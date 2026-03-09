<?php
namespace Topocentras\Slider\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Slide extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('topocentras_slider', 'slide_id');
    }
}
