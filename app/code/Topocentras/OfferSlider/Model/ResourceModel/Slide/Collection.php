<?php
namespace Topocentras\OfferSlider\Model\ResourceModel\Slide;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'slide_id';

    protected function _construct()
    {
        $this->_init(
            \Topocentras\OfferSlider\Model\Slide::class,
            \Topocentras\OfferSlider\Model\ResourceModel\Slide::class
        );
    }
}
