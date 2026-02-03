<?php
namespace Magenest\Movie\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
class Rating implements OptionSourceInterface
{
    /**
     * Method này trả về mảng các options
     * Magento sẽ gọi method này khi render dropdown
     *
     * @return array
     */
    public function toOptionArray()
    {
        // Cấu trúc:  [['value' => giá trị lưu, 'label' => text hiển thị]]
        return [
            ['value' => '', 'label' => __('All Ratings')],
            ['value' => '2', 'label' => __('2 and above')],
            ['value' => '3', 'label' => __('3 and above')],
            ['value' => '4', 'label' => __('4 and above')],
        ];
    }
}
