<?php

namespace Magenest\UiKnockout\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class Actions extends Column
{
    /** URL path tới Controller */
    const URL_PATH_EDIT = 'banner/index/edit';
    const URL_PATH_DELETE = 'banner/index/delete';

    /** @var UrlInterface */
    protected $urlBuilder;

    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface       $urlBuilder,
        array              $components = [],
        array              $data = []
    )
    {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Note logic: Hàm này quét qua từng dòng (row) trong Grid
     * và chèn thêm mảng 'actions' vào dữ liệu trả về.
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['banner_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'banner_id' => $item['banner_id']
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'banner_id' => $item['banner_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            // Note logic: Thêm xác nhận trước khi xóa để tránh bấm nhầm
                            'confirm' => [
                                'title' => __('Delete %1', $item['name']),
                                'message' => __('Are you sure you wan\'t to delete a %1 record?', $item['name'])
                            ]
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
