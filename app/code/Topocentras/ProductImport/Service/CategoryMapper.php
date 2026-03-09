<?php

namespace Topocentras\ProductImport\Service;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class CategoryMapper
{
    private $categoryFactory;
    private $categoryRepository;
    private $storeManager;
    private $categoryCache = [];

    public function __construct(
        CategoryFactory $categoryFactory,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
    }

    public function getCategoryIds(string $categoryName): array
    {
        if (empty($categoryName)) {
            return [];
        }

        if (isset($this->categoryCache[$categoryName])) {
            return $this->categoryCache[$categoryName];
        }

        $categoryId = $this->findOrCreateCategory($categoryName);
        $this->categoryCache[$categoryName] = [$categoryId];
        
        return $this->categoryCache[$categoryName];
    }

    private function findOrCreateCategory(string $name): int
    {
        $rootCategoryId = $this->storeManager->getStore()->getRootCategoryId();
        
        $category = $this->categoryFactory->create();
        $collection = $category->getCollection()
            ->addAttributeToFilter('name', $name)
            ->addAttributeToFilter('parent_id', $rootCategoryId)
            ->setPageSize(1);

        if ($collection->getSize() > 0) {
            return (int) $collection->getFirstItem()->getId();
        }

        return $this->createCategory($name, $rootCategoryId);
    }

    private function createCategory(string $name, int $parentId): int
    {
        $category = $this->categoryFactory->create();
        $category->setName($name);
        $category->setIsActive(true);
        $category->setParentId($parentId);
        $category->setPath($this->getParentPath($parentId));
        $category->setUrlKey($this->generateUrlKey($name));
        
        $category = $this->categoryRepository->save($category);
        return (int) $category->getId();
    }

    private function getParentPath(int $parentId): string
    {
        try {
            $parent = $this->categoryRepository->get($parentId);
            return $parent->getPath();
        } catch (NoSuchEntityException $e) {
            return '1/2';
        }
    }

    private function generateUrlKey(string $name): string
    {
        $urlKey = strtolower($name);
        $urlKey = preg_replace('/[^a-z0-9]+/', '-', $urlKey);
        return trim($urlKey, '-');
    }
}
