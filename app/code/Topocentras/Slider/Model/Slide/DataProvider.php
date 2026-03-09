<?php
namespace Topocentras\Slider\Model\Slide;

use Topocentras\Slider\Model\ResourceModel\Slide\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $collection;
    protected $dataPersistor;
    protected $loadedData;
    protected $storeManager;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        
        $items = $this->collection->getItems();
        foreach ($items as $slide) {
            $slideData = $slide->getData();
            
            if (isset($slideData['image'])) {
                $imageUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'topocentras/slider/' . $slideData['image'];
                $slideData['image'] = [
                    [
                        'name' => $slideData['image'],
                        'url' => $imageUrl
                    ]
                ];
            }
            
            $this->loadedData[$slide->getId()] = $slideData;
        }
        
        $data = $this->dataPersistor->get('topocentras_slider_slide');
        if (!empty($data)) {
            $slide = $this->collection->getNewEmptyItem();
            $slide->setData($data);
            $this->loadedData[$slide->getId()] = $slide->getData();
            $this->dataPersistor->clear('topocentras_slider_slide');
        }
        
        return $this->loadedData;
    }
}
