<?php
/**
 * Plugin để kiểm tra URL rewrite trùng lặp trước khi save Blog
 */
namespace Magenest\Movie\Plugin;

use Magenest\Movie\Model\Blog;
use Magenest\Movie\Model\ResourceModel\Blog\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;

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
     * Plugin beforeSave - chạy TRƯỚC khi Blog->save() được execute
     *
     * @param Blog $subject - Chính là Blog object đang được save
     * @return void
     * @throws LocalizedException
     * - $subject chính là instance của Blog đang được save
     */
    public function beforeSave(Blog $subject)
    {
        // Bước 1: Lấy url_rewrite từ Blog object đang được save
        $urlRewrite = $subject->getUrlRewrite();

        // Bước 2: Validate - URL rewrite không được rỗng
        if (!$urlRewrite) {
            throw new LocalizedException(__('URL rewrite is required.'));
        }

        // Bước 3: Tạo Collection để query database
        $collection = $this->blogCollectionFactory->create()
            ->addFieldToFilter('url_rewrite', $urlRewrite);

        // Bước 4: Nếu đang EDIT (blog đã có ID), loại trừ chính nó khỏi kết quả
        // Tại sao? Vì khi edit, URL của chính nó không tính là trùng!
        // WHERE url_rewrite = $urlRewrite AND id != $subject->getId()
        if ($subject->getId()) {
            $collection->addFieldToFilter('id', ['neq' => $subject->getId()]); // Loại trừ chính nó
        }

        // Bước 5: Kiểm tra kết quả
        // getSize() trả về số lượng record trong collection
        // Nếu > 0 nghĩa là đã có blog khác dùng URL này rồi
        if ($collection->getSize() > 0) {
            throw new LocalizedException(
                __('The URL rewrite "%1" already exists. Please use a different URL.', $urlRewrite)
            );
        }

        // Bước 6: Nếu không có lỗi, plugin kết thúc và cho phép save tiếp tục
        // beforeSave() không cần return gì nếu mọi thứ OK
    }
}
