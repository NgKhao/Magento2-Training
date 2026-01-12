<?php
namespace Magenest\Movie\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class OddEven extends Column
{
    /**
     * hàm được m2 gọi trước khi render
     * duyệt từng itme và gắn file odd_even
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        // Tên field của column (odd_even) lấy từ cấu hình UI component
        $fieldName = $this->getData('name');
        foreach ($dataSource['data']['items'] as & $item) {
            $orderID = (int) ($item['entity_id'] ?? 0);
            $idOdd = ($orderID % 2) === 1;
            $text = $idOdd ? 'odd' : 'even';

            $severityClass = $idOdd ? 'grid-severity-critical' : 'grid-severity-notice';

            $item[$fieldName] = sprintf('<span class="%s">%s</span>', $severityClass, $text);
        }
        return $dataSource;
    }
}
