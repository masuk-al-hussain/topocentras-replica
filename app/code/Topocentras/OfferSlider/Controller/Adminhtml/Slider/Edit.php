<?php
namespace Topocentras\OfferSlider\Controller\Adminhtml\Slider;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Topocentras\OfferSlider\Model\OfferSliderFactory;
use Magento\Framework\Registry;

class Edit extends Action
{
    protected $resultPageFactory;
    protected $sliderFactory;
    protected $coreRegistry;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        OfferSliderFactory $sliderFactory,
        Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->sliderFactory = $sliderFactory;
        $this->coreRegistry = $coreRegistry;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('slider_id');
        $model = $this->sliderFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This slider no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->coreRegistry->register('offerslider_slider', $model);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Topocentras_OfferSlider::offerslider');
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ? __('Edit Slider: %1', $model->getTitle()) : __('New Slider')
        );

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Topocentras_OfferSlider::slider');
    }
}
