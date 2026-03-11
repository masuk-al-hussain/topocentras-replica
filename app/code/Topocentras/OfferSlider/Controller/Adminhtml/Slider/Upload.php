<?php
namespace Topocentras\OfferSlider\Controller\Adminhtml\Slider;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class Upload extends Action
{
    protected $uploaderFactory;
    protected $filesystem;

    public function __construct(
        Context $context,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
    }

    public function execute()
    {
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => 'banner_image']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);

            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $destinationPath = $mediaDirectory->getAbsolutePath('topocentras/offerslider');

            $result = $uploader->save($destinationPath);

            if (!$result) {
                throw new \Exception(__('File cannot be saved to path: %1', $destinationPath));
            }

            $result['url'] = $this->_url->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA])
                . 'topocentras/offerslider/' . $result['file'];

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
        return $this->_authorization->isAllowed('Topocentras_OfferSlider::slider');
    }
}
