<?php
namespace Topocentras\Slider\Controller\Adminhtml\Slide;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;

class Upload extends Action
{
    protected $uploaderFactory;
    protected $filesystem;
    protected $storeManager;

    public function __construct(
        Context $context,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => 'image']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            
            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $result = $uploader->save($mediaDirectory->getAbsolutePath('topocentras/slider'));
            
            $result['url'] = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'topocentras/slider/' . $result['file'];
            $result['name'] = $result['file'];
            
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
        } catch (\Exception $e) {
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ]);
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Topocentras_Slider::slider');
    }
}
