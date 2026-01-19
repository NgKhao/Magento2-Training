<?php

declare(strict_types=1);

namespace Magenest\Movie\Model\Customer\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;

class Avatar extends AbstractBackend
{
    private $storeManager;
    private $filesystem;

    public function __construct(
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
    )
    {
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
    }

    /**
     * Giống core: Convert string path → array cho preview khi load customer
     */
    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attributeCode);

        if (is_string($value) && $value) {
            $baseMediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            $url = rtrim($baseMediaUrl, '/') . '/' . ltrim($value, '/');

            $mediaDir = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $size = $mediaDir->isExist($value) ? ($mediaDir->stat($value)['size'] ?? 0) : 0;
            $type = $mediaDir->isExist($value) ? 'image' : 'unknown';

            $previewData = [
                [
                    'name' => basename($value),
                    'url' => $url,
                    'file' => $value,
                    'size' => $size,
                    'type' => $type
                ]
            ];

            $object->setData($attributeCode, $previewData);
        }

        return $this;
    }

    /**
     * Giống core _filterCategoryPostData + beforeSave: Convert array → string, handle delete
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attributeCode);

        if (is_array($value)) {
            // Handle delete (core gửi delete flag hoặc [deleted])
            if (!empty($value['delete']) || (isset($value[0]['file']) && $value[0]['file'] === '[deleted]')) {
                // Optional: xóa file cũ
                $oldValue = $object->getOrigData($attributeCode);
                if ($oldValue && is_string($oldValue)) {
                    $mediaDir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
                    try {
                        $mediaDir->delete($oldValue);
                    } catch (\Exception $e) {
                        // ignore
                    }
                }
                $object->setData($attributeCode, null);
            } // Upload mới: lấy file path từ uploader (giống core lấy [0]['name'])
            elseif (isset($value[0]['file']) && is_string($value[0]['file'])) {
                $object->setData($attributeCode, $value[0]['file']);
            } else {
                // Giữ nguyên hoặc unset nếu invalid
                unset($object[$attributeCode]);
            }
        }

        return parent::beforeSave($object);
    }
}
