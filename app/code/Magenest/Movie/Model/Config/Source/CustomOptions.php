<?php
namespace Magenest\Movie\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class CustomOptions implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 1,
                'label' => __("show")
            ],
            [
                'value' => 2,
                'label' => __("not-show")
            ]
        ];
    }
}

