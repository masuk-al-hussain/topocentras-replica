<?php
namespace Topocentras\OfferSlider\Controller\Adminhtml\Slide;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Topocentras\OfferSlider\Model\SlideFactory;

class Delete extends Action
{
    protected $slideFactory;

    public function __construct(
        Context $context,
        SlideFactory $slideFactory
    ) {
        parent::__construct($context);
        $this->slideFactory = $slideFactory;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('slide_id');
        $sliderId = $this->getRequest()->getParam('slider_id');

        if ($id) {
            try {
                $model = $this->slideFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('Product deleted successfully.'));
                return $resultRedirect->setPath('offerslider/slider/edit', ['slider_id' => $sliderId]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('offerslider/slider/edit', ['slider_id' => $sliderId]);
            }
        }

        $this->messageManager->addErrorMessage(__('Product not found.'));
        return $resultRedirect->setPath('offerslider/slider/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Topocentras_OfferSlider::slider');
    }
}
