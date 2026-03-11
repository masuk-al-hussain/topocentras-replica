<?php
namespace Topocentras\OfferSlider\Controller\Adminhtml\Slide;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Topocentras\OfferSlider\Model\SlideFactory;

class Save extends Action
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
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $id = $this->getRequest()->getParam('slide_id');
            $sliderId = $this->getRequest()->getParam('slider_id');
            $model = $this->slideFactory->create();

            if ($id) {
                $model->load($id);
            }

            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('Product saved successfully.'));
                return $resultRedirect->setPath('offerslider/slider/edit', ['slider_id' => $sliderId]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['slide_id' => $id, 'slider_id' => $sliderId]);
            }
        }

        return $resultRedirect->setPath('offerslider/slider/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Topocentras_OfferSlider::slider');
    }
}
