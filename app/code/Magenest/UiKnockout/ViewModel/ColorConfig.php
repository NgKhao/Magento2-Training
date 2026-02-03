<?php
namespace Magenest\UiKnockout\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Serialize\SerializerInterface;

class ColorConfig implements ArgumentInterface
{
    protected $scopeConfig; // là biến để đọc cấu hình hệ thống
    protected $serializer; // là biến để chuyển đổi dữ liệu sang định dạng JSON

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SerializerInterface $serializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
    }

    public function getColors()
    {
        $configValue = $this->scopeConfig->getValue(
            'color_ranges/general/color_options',
            ScopeInterface::SCOPE_STORE
        );

        if (!$configValue) {
            return [];
        }

        // Giải nén chuỗi đã được serialize thành mảng PHP
        try{
            $data = $this->serializer->unserialize($configValue);
        }catch (\Exception $exception){
            return [];
        }

        $options = []; // Mảng để chứa các tùy chọn màu sắc [ {label: 'Red', value: '#F00'}, ... ]

        $options[] = [
            'label' => __('Default Color'),
            'value' => 'default'
        ];

        foreach ($data as $item){
            $options[] = [
                'label' => $item['color_label'],
                'value' => $item['color_code']
            ];
        }

        return $this->serializer->serialize($options); // Trả về chuỗi JSON
    }
}
