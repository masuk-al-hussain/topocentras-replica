<?php
namespace Topocentras\OfferSlider\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Slide extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('topocentras_offer_slider_slide', 'slide_id');
    }
}
