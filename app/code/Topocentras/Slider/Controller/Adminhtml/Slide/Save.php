<?php
namespace Topocentras\Slider\Controller\Adminhtml\Slide;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Topocentras\Slider\Model\SlideFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends Action
{
    protected $slideFactory;
    protected $uploaderFactory;
    protected $filesystem;

    public function __construct(
        Context $context,
        SlideFactory $slideFactory,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->slideFactory = $slideFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$data) {
            $this->messageManager->addError(__('No data received'));
            return $resultRedirect->setPath('*/*/');
        }

        $model = $this->slideFactory->create();
        $id = $this->getRequest()->getParam('slide_id');
        
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('Slide does not exist'));
                return $resultRedirect->setPath('*/*/');
            }
        }

        // Handle image data from UI component
        if (isset($data['image']) && is_array($data['image'])) {
            if (isset($data['image'][0]['name'])) {
                $data['image'] = $data['image'][0]['name'];
            } else {
                unset($data['image']);
            }
        } elseif (!isset($data['image']) || empty($data['image'])) {
            // If editing and no new image, keep existing
            if ($id && $model->getId()) {
                unset($data['image']);
            } else {
                $data['image'] = null;
            }
        }

        // Remove form_key from data
        if (isset($data['form_key'])) {
            unset($data['form_key']);
        }

        // Set individual fields instead of bulk setData
        if (isset($data['title'])) {
            $model->setTitle($data['title']);
        }
        if (isset($data['image'])) {
            $model->setImage($data['image']);
        }
        if (isset($data['url'])) {
            $model->setUrl($data['url']);
        }
        if (isset($data['sort_order'])) {
            $model->setSortOrder($data['sort_order']);
        }
        if (isset($data['is_active'])) {
            $model->setIsActive($data['is_active']);
        }

        try {
            $model->save();
            $this->messageManager->addSuccess(__('Slide saved successfully with ID: %1. Title: %2', $model->getId(), $model->getTitle()));
            
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['slide_id' => $model->getId()]);
            }
            
            return $resultRedirect->setPath('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Error saving slide: %1', $e->getMessage()));
            $this->messageManager->addError(__('Stack trace: %1', $e->getTraceAsString()));
            return $resultRedirect->setPath('*/*/edit', ['slide_id' => $id]);
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Topocentras_Slider::slider');
    }
}
