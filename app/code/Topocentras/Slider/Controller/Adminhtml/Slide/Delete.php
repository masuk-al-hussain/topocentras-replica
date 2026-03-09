<?php
namespace Topocentras\Slider\Controller\Adminhtml\Slide;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Topocentras\Slider\Model\SlideFactory;

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
        $id = $this->getRequest()->getParam('slide_id');
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($id) {
            try {
                $model = $this->slideFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('Slide deleted successfully.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Topocentras_Slider::slider');
    }
}
