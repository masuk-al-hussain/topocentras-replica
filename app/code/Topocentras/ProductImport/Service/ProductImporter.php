<?php

namespace Topocentras\ProductImport\Service;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\File\Uploader;

class ProductImporter
{
    private $productFactory;
    private $productRepository;
    private $storeManager;
    private $stockRegistry;
    private $categoryLinkManagement;
    private $filesystem;
    private $logger;
    private $categoryMapper;
    private $curl;

    public function __construct(
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        StockRegistryInterface $stockRegistry,
        CategoryLinkManagementInterface $categoryLinkManagement,
        Filesystem $filesystem,
        LoggerInterface $logger,
        CategoryMapper $categoryMapper,
        Curl $curl
    ) {
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->stockRegistry = $stockRegistry;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
        $this->categoryMapper = $categoryMapper;
        $this->curl = $curl;
    }

    public function importFromCsv(string $filePath, int $batchSize = 100, $output = null): array
    {
        $stats = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0
        ];

        if (!file_exists($filePath)) {
            throw new LocalizedException(__('CSV file not found: %1', $filePath));
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new LocalizedException(__('Cannot open CSV file: %1', $filePath));
        }

        $headers = fgetcsv($handle, 0, ',', '"', '\\');
        if (!$headers) {
            fclose($handle);
            throw new LocalizedException(__('CSV file is empty or invalid'));
        }

        $batch = [];
        $batchNumber = 0;
        $startTime = time();
        
        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if (count($row) !== count($headers)) {
                continue;
            }

            $data = array_combine($headers, $row);
            $batch[] = $data;

            if (count($batch) >= $batchSize) {
                $batchNumber++;
                $this->processBatch($batch, $stats);
                
                if ($output) {
                    $elapsed = time() - $startTime;
                    $rate = $stats['total'] > 0 ? round($stats['total'] / max($elapsed, 1), 2) : 0;
                    $output->writeln(sprintf(
                        '<comment>Batch %d: Processed %d products (Created: %d, Updated: %d, Errors: %d) - Rate: %s/sec</comment>',
                        $batchNumber,
                        $stats['total'],
                        $stats['created'],
                        $stats['updated'],
                        $stats['errors'],
                        $rate
                    ));
                }
                
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $batchNumber++;
            $this->processBatch($batch, $stats);
            
            if ($output) {
                $output->writeln(sprintf(
                    '<comment>Final batch %d: Processed %d products total</comment>',
                    $batchNumber,
                    $stats['total']
                ));
            }
        }

        fclose($handle);
        return $stats;
    }

    private function processBatch(array $batch, array &$stats): void
    {
        foreach ($batch as $data) {
            $stats['total']++;
            
            try {
                $result = $this->importProduct($data);
                if ($result === 'created') {
                    $stats['created']++;
                } elseif ($result === 'updated') {
                    $stats['updated']++;
                } else {
                    $stats['skipped']++;
                }
            } catch (\Exception $e) {
                $stats['errors']++;
                $this->logger->error('Product import error: ' . $e->getMessage(), [
                    'product_id' => $data['id'] ?? 'unknown',
                    'exception' => $e
                ]);
            }
        }
    }

    private function importProduct(array $data): string
    {
        if (empty($data['id']) || empty($data['title'])) {
            return 'skipped';
        }

        $sku = $this->generateSku($data['id']);
        
        try {
            $product = $this->productRepository->get($sku);
            $isNew = false;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $product = $this->productFactory->create();
            $isNew = true;
        }

        $product->setSku($sku);
        $product->setName($this->cleanText($data['title']));
        $product->setAttributeSetId(4);
        $product->setStatus(Status::STATUS_ENABLED);
        $product->setVisibility(Visibility::VISIBILITY_BOTH);
        $product->setTypeId(Type::TYPE_SIMPLE);
        $product->setWeight(1);

        if (!empty($data['description'])) {
            $product->setDescription($this->cleanText($data['description']));
            $product->setShortDescription($this->cleanText($data['description']));
        }

        if (!empty($data['price'])) {
            $price = $this->parsePrice($data['price']);
            if ($price > 0) {
                $product->setPrice($price);
            }
        }

        if (!empty($data['sale_price'])) {
            $salePrice = $this->parsePrice($data['sale_price']);
            if ($salePrice > 0) {
                $product->setSpecialPrice($salePrice);
            }
        }

        if (!empty($data['brand'])) {
            $product->setCustomAttribute('manufacturer', $data['brand']);
        }

        if (!empty($data['link'])) {
            $product->setUrlKey($this->generateUrlKey($data['title'], $sku));
        }

        $product = $this->productRepository->save($product);

        if (!empty($data['image_link'])) {
            $this->assignProductImage($product, $data['image_link']);
        }

        $stockItem = $this->stockRegistry->getStockItemBySku($sku);
        $isInStock = !empty($data['availability']) && 
                     strtolower($data['availability']) === 'in stock';
        $stockItem->setIsInStock($isInStock);
        $stockItem->setQty($isInStock ? 100 : 0);
        $this->stockRegistry->updateStockItemBySku($sku, $stockItem);

        if (!empty($data['custom_label_0'])) {
            $categoryIds = $this->categoryMapper->getCategoryIds($data['custom_label_0']);
            if (!empty($categoryIds)) {
                $this->categoryLinkManagement->assignProductToCategories($sku, $categoryIds);
            }
        }

        return $isNew ? 'created' : 'updated';
    }

    private function generateSku(string $id): string
    {
        return 'FB-' . $id;
    }

    private function parsePrice(string $priceString): float
    {
        $priceString = preg_replace('/[^0-9.,]/', '', $priceString);
        $priceString = str_replace(',', '.', $priceString);
        return (float) $priceString;
    }

    private function cleanText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = strip_tags($text);
        return trim($text);
    }

    private function generateUrlKey(string $title, string $sku): string
    {
        $urlKey = strtolower($this->cleanText($title));
        $urlKey = preg_replace('/[^a-z0-9]+/', '-', $urlKey);
        $urlKey = trim($urlKey, '-');
        return $urlKey . '-' . strtolower($sku);
    }

    private function assignProductImage(Product $product, string $imageUrl): bool
    {
        try {
            $existingImages = $product->getMediaGalleryImages();
            if ($existingImages && $existingImages->getSize() > 0) {
                return true;
            }

            $mediaDir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            
            $imageExtension = $this->getImageExtension($imageUrl);
            $tmpFileName = 'import_' . uniqid() . '.' . $imageExtension;
            $tmpPath = 'tmp/catalog/product/' . $tmpFileName;
            $tmpFilePath = $mediaDir->getAbsolutePath($tmpPath);
            
            $tmpDirectory = dirname($tmpFilePath);
            if (!is_dir($tmpDirectory)) {
                mkdir($tmpDirectory, 0777, true);
            }

            $this->curl->setOption(CURLOPT_TIMEOUT, 30);
            $this->curl->setOption(CURLOPT_FOLLOWLOCATION, true);
            $this->curl->get($imageUrl);
            
            $imageContent = $this->curl->getBody();
            
            if (empty($imageContent)) {
                $this->logger->warning('Empty image content from URL: ' . $imageUrl);
                return false;
            }

            file_put_contents($tmpFilePath, $imageContent);

            if (!file_exists($tmpFilePath) || filesize($tmpFilePath) === 0) {
                $this->logger->warning('Failed to save image to temp file: ' . $tmpFilePath);
                return false;
            }

            $product->addImageToMediaGallery(
                $tmpFilePath,
                ['image', 'small_image', 'thumbnail'],
                true,
                false
            );

            $this->productRepository->save($product);

            if (file_exists($tmpFilePath)) {
                unlink($tmpFilePath);
            }

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Image import error: ' . $e->getMessage(), [
                'image_url' => $imageUrl,
                'product_sku' => $product->getSku()
            ]);
            return false;
        }
    }

    private function getImageExtension(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        
        if (empty($extension)) {
            return 'jpg';
        }

        $extension = strtolower($extension);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        return in_array($extension, $allowedExtensions) ? $extension : 'jpg';
    }
}
