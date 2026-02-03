<?php
/**
 * Upload Controller cho Course Documents
 *
 * Xử lý upload file từ fileUploader trong dynamicRows
 * URL: admin/movie/document/upload
 */
namespace Magenest\Movie\Controller\Adminhtml\Document;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

class Upload extends Action implements HttpPostActionInterface
{
    // ACL resource - quyền cần có để upload
    const ADMIN_RESOURCE = 'Magento_Catalog::products';

    // Thư mục lưu file trong pub/media/
    const UPLOAD_DIR = 'course/documents';

    // Các định dạng file được phép upload
    const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar', 'csv'];

    private $uploaderFactory;
    private $filesystem;
    private $storeManager;

    public function __construct(
        Context $context,
        UploaderFactory $uploaderFactory,  // Factory tạo Uploader instance
        Filesystem $filesystem,             // Quản lý file system
        StoreManagerInterface $storeManager // Lấy base URL
    ) {
        parent::__construct($context);
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
    }

    /**
     * Xử lý upload file
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            // 1. Lấy file data từ $_FILES
            $fileData = $this->getFileData();

            if (!$fileData) {
                throw new \Exception(__('No file uploaded.'));
            }

            // 2. Tạo uploader và cấu hình
            $uploader = $this->uploaderFactory->create(['fileId' => $fileData]);
            $uploader->setAllowedExtensions(self::ALLOWED_EXTENSIONS);
            $uploader->setAllowRenameFiles(true);   // Tự đổi tên nếu trùng
            $uploader->setFilesDispersion(false);   // Không tạo subfolder

            // 3. Tạo thư mục nếu chưa có
            $mediaDir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            if (!$mediaDir->isDirectory(self::UPLOAD_DIR)) {
                $mediaDir->create(self::UPLOAD_DIR);
            }

            // 4. Lưu file
            $result = $uploader->save($mediaDir->getAbsolutePath(self::UPLOAD_DIR));

            // 5. Thêm URL vào response để preview
            $baseUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            $result['url'] = $baseUrl . self::UPLOAD_DIR . '/' . $result['file'];

            // 6. Xóa thông tin nhạy cảm
            unset($result['tmp_name'], $result['path']);

        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        // Trả về JSON response
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }

    /**
     * Lấy file data từ $_FILES
     *
     * DynamicRows gửi file với key dạng: course_documents[0][file]
     * Nên $_FILES có cấu trúc nested:
     * $_FILES['course_documents']['tmp_name'][0]['file'] = '/tmp/phpXXX'
     *
     * @return array|null File data dạng ['name'=>..., 'tmp_name'=>..., ...]
     */
    private function getFileData()
    {
        if (empty($_FILES)) {
            return null;
        }

        // Duyệt qua từng file input
        foreach ($_FILES as $fileInput) {
            // Trường hợp 1: File đơn giản (tmp_name là string)
            if (isset($fileInput['tmp_name']) && is_string($fileInput['tmp_name'])) {
                if (file_exists($fileInput['tmp_name'])) {
                    return $fileInput;
                }
            }

            // Trường hợp 2: File từ dynamicRows (tmp_name là nested array)
            if (isset($fileInput['tmp_name']) && is_array($fileInput['tmp_name'])) {
                $result = $this->extractNestedFile($fileInput);
                if ($result) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * Trích xuất file từ nested array
     *
     * Input:  ['tmp_name' => [0 => ['file' => '/tmp/xxx']], 'name' => [0 => ['file' => 'doc.pdf']], ...]
     * Output: ['tmp_name' => '/tmp/xxx', 'name' => 'doc.pdf', ...]
     *
     * @param array $fileInput Dữ liệu file từ $_FILES
     * @return array|null
     */
    private function extractNestedFile($fileInput)
    {
        // Flatten tmp_name để tìm file thực (là string, không phải array)
        $allTmpNames = $this->flatten($fileInput['tmp_name']);

        foreach ($allTmpNames as $path => $tmpName) {
            // Bỏ qua nếu không phải file hợp lệ
            if (empty($tmpName) || !file_exists($tmpName)) {
                continue;
            }

            // Tìm thấy file! Lấy các thuộc tính khác theo cùng path
            $keys = explode('.', $path);  // "0.file" => ["0", "file"]

            return [
                'name'     => $this->getValue($fileInput['name'], $keys),
                'type'     => $this->getValue($fileInput['type'], $keys),
                'tmp_name' => $tmpName,
                'error'    => $this->getValue($fileInput['error'], $keys),
                'size'     => $this->getValue($fileInput['size'], $keys),
            ];
        }

        return null;
    }

    /**
     * Flatten nested array thành dạng phẳng
     *
     * Input:  [0 => ['file' => 'value']]
     * Output: ['0.file' => 'value']
     */
    private function flatten($array, $prefix = '')
    {
        $result = [];

        foreach ((array)$array as $key => $value) {
            $newKey = $prefix === '' ? $key : $prefix . '.' . $key;

            if (is_array($value)) {
                // Đệ quy nếu còn là array
                $result += $this->flatten($value, $newKey);
            } else {
                // Đã đến giá trị cuối
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Lấy giá trị từ nested array theo path
     *
     * Input:  $array = [0 => ['file' => 'doc.pdf']], $keys = ['0', 'file']
     * Output: 'doc.pdf'
     */
    private function getValue($array, $keys)
    {
        foreach ($keys as $key) {
            if (!isset($array[$key])) {
                return null;
            }
            $array = $array[$key];
        }
        return $array;
    }
}
