<?php
/**
 * Plugin để kiểm tra URL rewrite trùng lặp trước khi save Blog
 *
 * =============================================================================
 * THẦY GIẢI THÍCH: Plugin vào ResourceModel
 * =============================================================================
 *
 * Vì em plugin vào ResourceModel\Blog (trong di.xml), nên:
 * - $subject = ResourceModel\Blog object
 * - $object = Blog Model object (được truyền vào ResourceModel->save($object))
 *
 * Signature của ResourceModel::save():
 *   public function save(\Magento\Framework\Model\AbstractModel $object)
 *
 * Plugin beforeSave signature:
 *   public function beforeSave($subject, $object)
 *   - $subject = ResourceModel\Blog
 *   - $object = Blog Model (chính là Blog đang được save)
 *
 * =============================================================================
 */
namespace Magenest\Movie\Plugin;

use Magenest\Movie\Model\Blog;
use Magenest\Movie\Model\ResourceModel\Blog as BlogResource;
use Magenest\Movie\Model\ResourceModel\Blog\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;

class BlogPlugin
{
    /**
     * @var CollectionFactory
     */
    protected $blogCollectionFactory;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct(
        CollectionFactory $blogCollectionFactory
    ) {
        $this->blogCollectionFactory = $blogCollectionFactory;
    }

    /**
     * Plugin beforeSave - chạy TRƯỚC khi ResourceModel->save() được execute
     *
     * THẦY GIẢI THÍCH CHI TIẾT:
     *
     * 1. Plugin Target: Magenest\Movie\Model\ResourceModel\Blog
     *    → Khai báo trong di.xml
     *
     * 2. Original Method: ResourceModel\Blog::save(AbstractModel $object)
     *    → Nhận 1 parameter là Model object
     *
     * 3. Plugin Signature:
     *    beforeSave($subject, ...$originalMethodParams)
     *    - $subject = ResourceModel\Blog (class bị plugin)
     *    - $object = Blog Model (param của original method)
     *
     * 4. Type Hint:
     *    - $subject: BlogResource (hoặc không type hint cũng được)
     *    - $object: AbstractModel (hoặc Blog để chặt chẽ hơn)
     *
     * @param BlogResource $subject - ResourceModel\Blog object (bị plugin)
     * @param AbstractModel|Blog $object - Blog Model object (đang được save)
     * @return array - Return empty array để không modify params
     * @throws LocalizedException
     */
    public function beforeSave(BlogResource $subject, AbstractModel $object)
    {
        // Bước 1: Lấy url_rewrite từ Blog object đang được save
        // $object chính là Blog Model được truyền vào ResourceModel->save($object)
        $urlRewrite = $object->getUrlRewrite();

        // Bước 2: Validate - URL rewrite không được rỗng
        if (!$urlRewrite) {
            throw new LocalizedException(__('URL rewrite is required.'));
        }

        // Bước 3: Tạo Collection để query database
        // Tìm blog nào có url_rewrite trùng với URL đang save
        $collection = $this->blogCollectionFactory->create()
            ->addFieldToFilter('url_rewrite', $urlRewrite);

        // Bước 4: Nếu đang EDIT (blog đã có ID), loại trừ chính nó khỏi kết quả
        // Tại sao? Vì khi edit, URL của chính nó không tính là trùng!
        // WHERE url_rewrite = $urlRewrite AND id != $object->getId()
        if ($object->getId()) {
            $collection->addFieldToFilter('id', ['neq' => $object->getId()]); // neq = not equal
        }

        // Bước 5: Kiểm tra kết quả
        // getSize() trả về số lượng record trong collection
        // Nếu > 0 nghĩa là đã có blog khác dùng URL này rồi → Báo lỗi
        if ($collection->getSize() > 0) {
            throw new LocalizedException(
                __('The URL rewrite "%1" already exists. Please use a different URL.', $urlRewrite)
            );
        }

        // Bước 6: Return array chứa $object
        // QUAN TRỌNG: beforeSave() phải return array chứa parameters cho original method
        //
        // Original method: ResourceModel->save($object)
        // Plugin MUST return: [$object]
        //
        // Giải thích:
        // - return [];              → Magento gọi save() với 0 params → LỖI!
        // - return [$object];       → Magento gọi save($object) → ĐÚNG!
        // - return [$modifiedObj];  → Magento gọi save($modifiedObj) → Modify params
        //
        // Vì em không modify object, chỉ validate, nên return [$object] để giữ nguyên
        return [$object];
    }
}
