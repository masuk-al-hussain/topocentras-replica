<?php
namespace Topocentras\OfferSlider\Controller\Adminhtml\Slider;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Topocentras\OfferSlider\Model\OfferSliderFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class Save extends Action
{
    protected $sliderFactory;
    protected $dataPersistor;

    public function __construct(
        Context $context,
        OfferSliderFactory $sliderFactory,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
        $this->sliderFactory = $sliderFactory;
        $this->dataPersistor = $dataPersistor;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $id = $this->getRequest()->getParam('slider_id');
            $model = $this->sliderFactory->create();

            if ($id) {
                $model->load($id);
            }

            // Handle product_skus field - convert array to string if needed
            if (isset($data['product_skus']) && is_array($data['product_skus'])) {
                $data['product_skus'] = implode(',', $data['product_skus']);
            }

            // Handle banner_image field - extract filename if it's an array
            if (isset($data['banner_image']) && is_array($data['banner_image'])) {
                $data['banner_image'] = isset($data['banner_image'][0]['name']) ? $data['banner_image'][0]['name'] : '';
            }

            // Remove form_key from data as it's not a database field
            unset($data['form_key']);
            
            // Remove slider_id if it's empty to allow AUTO_INCREMENT
            if (isset($data['slider_id']) && empty($data['slider_id'])) {
                unset($data['slider_id']);
            }
            
            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('Slider saved successfully.'));
                $this->dataPersistor->clear('offerslider_slider');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['slider_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->dataPersistor->set('offerslider_slider', $data);
                return $resultRedirect->setPath('*/*/edit', ['slider_id' => $id]);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Topocentras_OfferSlider::slider');
    }
}
