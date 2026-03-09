<?php
namespace Topocentras\Slider\Controller\Adminhtml\Slide;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Topocentras\Slider\Model\ResourceModel\Slide\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends Action
{
    protected $filter;
    protected $collectionFactory;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        foreach ($collection as $slide) {
            $slide->delete();
        }

        $this->messageManager->addSuccess(__('A total of %1 slide(s) have been deleted.', $collectionSize));

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Topocentras_Slider::slider');
    }
}
