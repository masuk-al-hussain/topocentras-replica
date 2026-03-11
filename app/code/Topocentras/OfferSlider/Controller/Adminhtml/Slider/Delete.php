<?php
namespace Topocentras\OfferSlider\Controller\Adminhtml\Slider;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Topocentras\OfferSlider\Model\OfferSliderFactory;

class Delete extends Action
{
    protected $sliderFactory;

    public function __construct(
        Context $context,
        OfferSliderFactory $sliderFactory
    ) {
        parent::__construct($context);
        $this->sliderFactory = $sliderFactory;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('slider_id');

        if ($id) {
            try {
                $model = $this->sliderFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('Slider deleted successfully.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['slider_id' => $id]);
            }
        }

        $this->messageManager->addErrorMessage(__('Slider not found.'));
        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Topocentras_OfferSlider::slider');
    }
}
