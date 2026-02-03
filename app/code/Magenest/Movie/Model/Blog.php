<?php
/**
 * Copyright © Magenest. All rights reserved.
 *
 * Blog Model - Implement BlogInterface
 */
namespace Magenest\Movie\Model;

use Magento\Framework\Model\AbstractModel;
use Magenest\Movie\Api\Data\BlogInterface;

/**
 * Class Blog
 *
 * - Model implement Interface để đảm bảo tuân thủ contract
 * - Các method getter/setter đã có sẵn từ AbstractModel
 * - Chỉ cần khai báo implement interface
 */
class Blog extends AbstractModel implements BlogInterface
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init(\Magenest\Movie\Model\ResourceModel\Blog::class);
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritDoc
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getAuthorId()
    {
        return $this->getData(self::AUTHOR_ID);
    }

    /**
     * @inheritDoc
     */
    public function setAuthorId($authorId)
    {
        return $this->setData(self::AUTHOR_ID, $authorId);
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @inheritDoc
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @inheritDoc
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @inheritDoc
     */
    public function getContent()
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * @inheritDoc
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * @inheritDoc
     */
    public function getUrlRewrite()
    {
        return $this->getData(self::URL_REWRITE);
    }

    /**
     * @inheritDoc
     */
    public function setUrlRewrite($urlRewrite)
    {
        return $this->setData(self::URL_REWRITE, $urlRewrite);
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
