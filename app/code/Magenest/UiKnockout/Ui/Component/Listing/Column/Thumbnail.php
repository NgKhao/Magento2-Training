<?php
//Lấy đường dẫn ảnh lưu trong DB, nối với đường dẫn gốc của thư mục media để tạo ra URL hiển thị
namespace Magenest\UiKnockout\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Thumbnail extends Column
{
    protected $urlBuilder; // Biến để tạo URL

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ){
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Chuẩn bị dataSource để hiển thị ảnh thumbnail trong grid
     *
     * @param array $dataSource Dữ liệu nguồn từ collection
     * @return array Dữ liệu đã được chuẩn bị với URL ảnh đầy đủ
     */
    public function prepareDataSource(array $dataSource)
    {
        if(isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['image'])) {
                    // Tạo URL đầy đủ cho ảnh thumbnail bằng cách nối với đường dẫn media
                    $url = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . 'banner/images/' . $item['image'];
                    $item['image_src'] = $url;
                    $item['image_link'] = $url;
                    $item['image_orig_src'] = $url;
                }
            }
        }
        return $dataSource;
    }
}
