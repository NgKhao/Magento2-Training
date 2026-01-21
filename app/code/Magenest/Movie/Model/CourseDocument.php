<?php
namespace Magenest\Movie\Model;

use Magento\Framework\Model\AbstractModel;


class CourseDocument extends AbstractModel
{
    const TYPE_LINK = 'link';
    const TYPE_FILE = 'file';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magenest\Movie\Model\ResourceModel\CourseDocument::class);
    }


    /**
     * Check if document is link type
     */
    public function isLink()
    {
        return $this->getData('type') === self::TYPE_LINK;
    }

    /**
     * Check if document is file type
     */
    public function isFile()
    {
        return $this->getData('type') === self::TYPE_FILE;
    }

    public function getDocumentUrl()
    {
        if($this->isLink())
        {
            return $this->getData('link_url');
        }

        if($this->isFile() && $this->getData('file_path'))
        {
            $mediaUrl =\Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Store\Model\StoreManagerInterface::class)
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            return $mediaUrl . 'course/documents/' . $this->getData('file_path');
        }
        return '';
    }

}
