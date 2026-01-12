<?php
namespace Magenest\Movie\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Rating extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory ,$components, $data);
    }

    /**
     * Chuẩn bị dataSource
     * convert số star sang html star
     * @param array $dataSource - data từ collection
     * @return array - Dữ liệu đã được transform
     */
    public function prepareDataSource(array $dataSource)
    {
        if(isset($dataSource['data']['items'])) {

            // rating
            $fieldName = $this->getData('rating');

            foreach ($dataSource['data']['items'] as &$item) {
                if(isset($item[$fieldName])) {
                    $rating = (int) $item[$fieldName];
                    $item[$fieldName] = $this->renderStars($rating);
                }
            }
        }
        return $dataSource;
    }

    /**
     * @param $rating
     * @return string
     */
    public function renderStars($rating)
    {
        $emptyStarts = 5 - $rating;
        $html = '<span class="rating-stars">';
        // Thêm sao đầy (màu vàng)
        $html .= '<span style="color: #ff9800;">' . str_repeat('★', $rating) . '</span>';
        // Thêm sao rỗng (màu xám)
        $html .= '<span style="color: #ccc;">' . str_repeat('☆', $emptyStarts) . '</span>';
        $html .= '</span>';
        return $html;
    }

}
