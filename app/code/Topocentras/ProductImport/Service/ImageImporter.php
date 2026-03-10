<?php

namespace Topocentras\ProductImport\Service;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

class ImageImporter
{
    private $productRepository;
    private $filesystem;
    private $curl;
    private $logger;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        Filesystem $filesystem,
        Curl $curl,
        LoggerInterface $logger
    ) {
        $this->productRepository = $productRepository;
        $this->filesystem = $filesystem;
        $this->curl = $curl;
        $this->logger = $logger;
    }

    public function importImagesFromCsv(string $filePath, int $batchSize = 50, $output = null, bool $force = false, int $offset = 0, int $limit = 0): array
    {
        $stats = [
            'total' => 0,
            'downloaded' => 0,
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
        $rowNumber = 0;
        $processedCount = 0;

        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if (count($row) !== count($headers)) {
                continue;
            }

            $data = array_combine($headers, $row);
            
            if (!empty($data['id']) && !empty($data['image_link'])) {
                $rowNumber++;
                
                if ($rowNumber <= $offset) {
                    continue;
                }
                
                if ($limit > 0 && $processedCount >= $limit) {
                    break;
                }
                
                $batch[] = $data;
                $processedCount++;
            }

            if (count($batch) >= $batchSize) {
                $batchNumber++;
                $this->processBatch($batch, $stats, $force);
                
                if ($output) {
                    $elapsed = time() - $startTime;
                    $rate = $stats['total'] > 0 ? round($stats['total'] / max($elapsed, 1), 2) : 0;
                    $output->writeln(sprintf(
                        '<comment>Batch %d: Processed %d products (Downloaded: %d, Skipped: %d, Errors: %d) - Rate: %s/sec</comment>',
                        $batchNumber,
                        $stats['total'],
                        $stats['downloaded'],
                        $stats['skipped'],
                        $stats['errors'],
                        $rate
                    ));
                }
                
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $batchNumber++;
            $this->processBatch($batch, $stats, $force);
            
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

    private function processBatch(array $batch, array &$stats, bool $force): void
    {
        foreach ($batch as $data) {
            $stats['total']++;
            
            try {
                $sku = 'FB-' . $data['id'];
                $product = $this->productRepository->get($sku);
                
                $result = $this->downloadAndAssignImage($product, $data['image_link'], $force);
                
                if ($result) {
                    $stats['downloaded']++;
                } else {
                    $stats['skipped']++;
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $stats['skipped']++;
                $this->logger->warning('Product not found for image import: ' . $data['id']);
            } catch (\Exception $e) {
                $stats['errors']++;
                $this->logger->error('Image import error: ' . $e->getMessage(), [
                    'product_id' => $data['id'] ?? 'unknown',
                    'image_url' => $data['image_link'] ?? 'unknown'
                ]);
            }
        }
    }

    private function downloadAndAssignImage(Product $product, string $imageUrl, bool $force): bool
    {
        try {
            if (!$force) {
                $existingImages = $product->getMediaGalleryImages();
                if ($existingImages && $existingImages->getSize() > 0) {
                    return false;
                }
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
                return false;
            }

            file_put_contents($tmpFilePath, $imageContent);

            if (!file_exists($tmpFilePath) || filesize($tmpFilePath) === 0) {
                return false;
            }

            if ($force) {
                $existingImages = $product->getMediaGalleryImages();
                if ($existingImages) {
                    foreach ($existingImages as $image) {
                        $product->removeImage($image->getFile());
                    }
                }
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
            $this->logger->error('Image download error: ' . $e->getMessage(), [
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
