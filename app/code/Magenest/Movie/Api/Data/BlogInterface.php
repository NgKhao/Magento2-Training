<?php
/**
 * Copyright © Magenest. All rights reserved.
 *
 * Blog Data Interface
 *
 * =============================================================================
 * THẦY GIẢI THÍCH: Data Interface trong Magento 2
 * =============================================================================
 *
 * Data Interface định nghĩa contract (hợp đồng) cho data object.
 *
 * Tại sao cần Interface?
 * - Loose coupling: Code phụ thuộc vào interface, không phụ thuộc vào implementation
 * - Easy to test: Có thể mock interface
 * - API documentation: Auto-generate API docs từ interface
 * - Type safety: IDE và PHP có thể check type
 *
 * Best practices:
 * - Constants cho field names (tránh typo)
 * - Getter/Setter cho mọi field
 * - PHPDoc đầy đủ cho API documentation
 *
 * =============================================================================
 */
namespace Magenest\Movie\Api\Data;

/**
 * Blog Data Interface
 *
 * @api
 */
interface BlogInterface
{
    /**
     * Constants for keys of data array
     *
     * Thầy giải thích:
     * - Sử dụng constants thay vì hardcode string
     * - Tránh typo khi set/get data
     * - IDE auto-complete
     */
    const ID = 'id';
    const AUTHOR_ID = 'author_id';
    const TITLE = 'title';
    const DESCRIPTION = 'description';
    const CONTENT = 'content';
    const URL_REWRITE = 'url_rewrite';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Status values
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * Get blog ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set blog ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get author ID
     *
     * @return int
     */
    public function getAuthorId();

    /**
     * Set author ID
     *
     * @param int $authorId
     * @return $this
     */
    public function setAuthorId($authorId);

    /**
     * Get blog title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set blog title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * Get blog description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Set blog description
     *
     * @param string|null $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Get blog content
     *
     * @return string
     */
    public function getContent();

    /**
     * Set blog content
     *
     * @param string $content
     * @return $this
     */
    public function setContent($content);

    /**
     * Get URL rewrite
     *
     * @return string
     */
    public function getUrlRewrite();

    /**
     * Set URL rewrite
     *
     * @param string $urlRewrite
     * @return $this
     */
    public function setUrlRewrite($urlRewrite);

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}

