<?php
/**
 * Copyright © Magenest. All rights reserved.
 *
 * Block class cho Blog View
 *
 * Block là nơi chứa logic để chuẩn bị data cho Template.
 *
 * Flow:
 * 1. Layout tạo Block instance
 * 2. Block lấy data từ Registry/Model
 * 3. Template gọi Block methods để hiển thị data
 *
 * Tại sao cần Block?
 * - Tách logic khỏi template (MVC pattern)
 * - Reusable code
 * - Easy to test
 *
 */
namespace Magenest\Movie\Block\Blog;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magenest\Movie\Model\Blog;
use Magenest\Movie\Model\DirectorFactory;

class View extends Template
{
    /**
     * @var Registry
     * Để lấy current blog từ registry
     */
    protected $registry;

    /**
     * @var DirectorFactory
     * Để load thông tin director (author)
     */
    protected $directorFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param DirectorFactory $directorFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DirectorFactory $directorFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->directorFactory = $directorFactory;
        parent::__construct($context, $data);
    }

    /**
     * Lấy current blog từ registry
     *
     * @return Blog|null
     *
     * Thầy giải thích:
     * - Registry::registry('key') lấy object đã được lưu trong Controller
     * - Controller đã lưu: $registry->register('current_blog', $blog)
     * - Block lấy ra: $registry->registry('current_blog')
     */
    public function getBlog(): ?Blog
    {
        return $this->registry->registry('current_blog');
    }

    /**
     * Lấy page title
     *
     * @return string
     */
    public function getPageTitle(): string
    {
        $blog = $this->getBlog();
        return $blog ? $blog->getTitle() : __('Blog Not Found');
    }

    /**
     * Lấy author name
     *
     * @return string
     *
     * - Blog có author_id (foreign key đến admin_user)
     * - Sử dụng data đã join từ Collection (joinAuthor())
     * - Nếu chưa join, lấy từ author_id
     */
    public function getAuthorName(): string
    {
        $blog = $this->getBlog();
        if (!$blog) {
            return '';
        }

        // Kiểm tra xem đã join author chưa (từ Collection::joinAuthor())
        // Nếu có author_firstname và author_lastname từ join
        $firstName = $blog->getData('author_firstname');
        $lastName = $blog->getData('author_lastname');

        if ($firstName || $lastName) {
            return trim($firstName . ' ' . $lastName);
        }

        return __('Unknown Author')->render();
    }

    /**
     * Format date theo locale
     *
     * @param \DateTimeInterface|string|null $date
     * @param int $format
     * @param bool $showTime
     * @param string|null $timezone
     * @return string
     *
     * - Override method từ AbstractBlock với correct signature
     * - Sử dụng parent method để format date
     */
    public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::MEDIUM,
        $showTime = false,
        $timezone = null
    ): string {
        if (!$date) {
            return '';
        }
        return $this->_localeDate->formatDate($date, $format, $showTime);
    }

    /**
     * Get formatted content (có thể add filter, remove script, etc)
     *
     * @return string
     */
    public function getFormattedContent(): string
    {
        $blog = $this->getBlog();
        if (!$blog) {
            return '';
        }

        // Có thể thêm content filter ở đây
        // Ví dụ: process shortcode, remove dangerous HTML, etc
        $content = $blog->getContent();

        // Simple sanitize (trong production cần robust hơn)
        $content = strip_tags($content, '<p><br><strong><em><ul><ol><li><a><img><h1><h2><h3>');

        return $content;
    }

    /**
     * Get blog URL (canonical URL)
     *
     * @return string
     */
    public function getBlogUrl(): string
    {
        $blog = $this->getBlog();
        if (!$blog) {
            return '';
        }

        // Return SEO-friendly URL
        return $this->getUrl($blog->getUrlRewrite());
    }

    /**
     * Get status label
     *
     * @return string
     */
    public function getStatusLabel(): string
    {
        $blog = $this->getBlog();
        if (!$blog) {
            return '';
        }

        return $blog->getStatus() == 1 ? __('Active') : __('Inactive');
    }
}


