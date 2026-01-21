<?php
namespace Magenest\Movie\Controller\Adminhtml\Document;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;


class Upload extends Action
{
    protected $uploaderFactory;
    protected $filesystem;
    protected $storeManager;
    protected $jsonFactory;

    public function __construct(
        Action\Context $context,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        JsonFactory $jsonFactory

    ) {
        parent::__construct($context);
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->jsonFactory = $jsonFactory;

    }

    public function execute()
    {
        $result = $this->jsonFactory->create();

        try {
//            $fileId = $this->getRequest()->getParam('param_name');
            /** 1️⃣ Init uploader */
//            thêm param bên ui để lấy các trường thuận tiện hơn khi bị đánh dấu index của dinamyrow
            $uploader = $this->uploaderFactory->create(['fileId' => 'document_file']);
            $uploader->setAllowedExtensions([
                'pdf','doc','docx','xls','xlsx','ppt','pptx','txt','zip','rar','csv'
            ]);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $uploader->setAllowCreateFolders(true);

            /** Media path */
            $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $relativePath = 'course/documents';
            $absolutePath = $mediaDirectory->getAbsolutePath($relativePath);

            if (!$mediaDirectory->isDirectory($relativePath)) {
                $mediaDirectory->create($relativePath);
            }

            /** Save file */
            $saved = $uploader->save($absolutePath);

            /**Build response */
            $fileName = $saved['file']; // tên file đã rename
            $mediaUrl = $this->storeManager
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

            return $result->setData([
                'name' => $fileName,
                'file' => $fileName,
                'url'  => $mediaUrl . $relativePath . '/' . $fileName,
                'size' => $saved['size'] ?? 0,
                'type' => $saved['type'] ?? 'application/octet-stream',
            ]);

        } catch (\Exception $e) {
            return $result->setData([
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
