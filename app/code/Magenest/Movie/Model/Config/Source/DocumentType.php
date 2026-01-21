<?php
namespace Magenest\Movie\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source model cho Document Type
 */
class DocumentType implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'link', 'label' => __('External Link')],
            ['value' => 'file', 'label' => __('Upload File')]
        ];
    }
}
