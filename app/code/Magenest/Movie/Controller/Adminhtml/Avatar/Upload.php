<?php
namespace Magenest\Movie\Controller\Adminhtml\Avatar;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;


class Upload extends Action
{
    public function __construct(
        Action\Context $context,
        private JsonFactory $jsonFactory,
        private UploaderFactory $uploaderFactory,
        private Filesystem $filesystem,
        private StoreManagerInterface $storeManager,
    )
    {
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        try {
//            Lấy Media directory pub/media/
            $mediaDir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);

            $uploader =$this->uploaderFactory->create(['fileId' => 'customer[avatar]']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);

            $path = 'customers/avatar';
            $saved = $uploader->save($mediaDir->getAbsolutePath($path)); // save vào folder

            $fileName = $saved['file'];

            $relativePath = $path .  '/' . $fileName;

            //Build preview URL
            $baseMediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

            // JSON format để uploader preview
            return $result->setData([
                'name' => $saved['name'],
                'file' => $relativePath,
                'url'  => $baseMediaUrl . $relativePath, // Full URL - PREVIEW
            ]);
        } catch (\Throwable $e) {
            return $result->setData(['error' => $e->getMessage()]);
        }
    }
}
