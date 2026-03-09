<?php
namespace Topocentras\Slider\Model;

use Magento\Framework\Model\AbstractModel;

class Slide extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Topocentras\Slider\Model\ResourceModel\Slide::class);
    }
}
