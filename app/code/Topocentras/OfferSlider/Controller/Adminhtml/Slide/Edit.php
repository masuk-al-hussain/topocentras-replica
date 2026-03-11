<?php
namespace Topocentras\OfferSlider\Controller\Adminhtml\Slide;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Topocentras\OfferSlider\Model\SlideFactory;
use Magento\Framework\Registry;

class Edit extends Action
{
    protected $resultPageFactory;
    protected $slideFactory;
    protected $coreRegistry;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SlideFactory $slideFactory,
        Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->slideFactory = $slideFactory;
        $this->coreRegistry = $coreRegistry;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('slide_id');
        $sliderId = $this->getRequest()->getParam('slider_id');
        $model = $this->slideFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This product no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('offerslider/slider/edit', ['slider_id' => $sliderId]);
            }
        } else {
            $model->setSliderId($sliderId);
        }

        $this->coreRegistry->register('offerslider_slide', $model);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Topocentras_OfferSlider::offerslider');
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ? __('Edit Product') : __('Add Product')
        );

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Topocentras_OfferSlider::slider');
    }
}
