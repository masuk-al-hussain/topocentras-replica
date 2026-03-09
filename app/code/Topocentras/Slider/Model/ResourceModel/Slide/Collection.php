<?php
namespace Topocentras\Slider\Model\ResourceModel\Slide;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'slide_id';

    protected function _construct()
    {
        $this->_init(
            \Topocentras\Slider\Model\Slide::class,
            \Topocentras\Slider\Model\ResourceModel\Slide::class
        );
    }
}
