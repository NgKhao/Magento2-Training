<?php

declare(strict_types=1);

namespace Magenest\Movie\Model\Customer\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;

class Avatar extends AbstractBackend
{
    private $storeManager;
    private $filesystem;

    public function __construct(
        StoreManagerInterface $storeManager,
        Filesystem $filesystem
    ) {
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
    }

    /**
     * Get Request from ObjectManager
     */
    private function getRequest()
    {
        return ObjectManager::getInstance()->get(\Magento\Framework\App\RequestInterface::class);
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
     * Convert array → string path before save
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $value = null;
        $postData = $this->getRequest()->getPostValue();
        if (isset($postData['customer'][$attributeCode])) {
            $value = $postData['customer'][$attributeCode];
        }

        $finalValue = null;

        if (is_array($value) && !empty($value)) {
            // Handle delete
            if (!empty($value['delete']) || (isset($value[0]['file']) && $value[0]['file'] === '[deleted]')) {
                $this->deleteOldImage($object, $attributeCode);
                $finalValue = null;
            }
            // Upload mới - Format: [0]['file']
            elseif (isset($value[0]['file']) && is_string($value[0]['file']) && $value[0]['file'] !== '[deleted]') {
                $finalValue = $value[0]['file'];
            }
            // Giữ nguyên value cũ
            else {
                $finalValue = $object->getOrigData($attributeCode);
            }
        } elseif (is_string($value) && $value) {
            $finalValue = $value;
        } else {
            $finalValue = $object->getOrigData($attributeCode);
        }

        $object->setData($attributeCode, $finalValue);

        return parent::beforeSave($object);
    }

    /**
     * Delete old image file
     */
    private function deleteOldImage($object, $attributeCode)
    {
        $oldValue = $object->getOrigData($attributeCode);
        if ($oldValue && is_string($oldValue)) {
            $mediaDir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            try {
                if ($mediaDir->isExist($oldValue)) {
                    $mediaDir->delete($oldValue);
                }
            } catch (\Exception $e) {
                // Ignore delete errors
            }
        }
    }
}
