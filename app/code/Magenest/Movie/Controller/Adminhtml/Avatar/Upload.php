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
            $mediaDir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);

            $uploader =$this->uploaderFactory->create(['fileId' => 'customer[avatar]']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);

            $path = 'customers/avatar';
            $saved = $uploader->save($mediaDir->getAbsolutePath($path));

            // ✅ Sửa:  Dùng URL_TYPE_MEDIA thay vì ghép thủ công
            $baseMediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

            $fileName = ltrim($saved['file'], '/'); // vd abc.jpg

            // JSON format để uploader preview
            return $result->setData([
                'name' => $fileName,
                'file' => $fileName,
                'url'  => $baseMediaUrl . 'customers/avatar/' . $fileName, // ✅ Đúng format
            ]);
        } catch (\Throwable $e) {
            return $result->setData(['error' => $e->getMessage()]);
        }
    }
}
