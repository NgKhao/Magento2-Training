<?php
/**
 * Source Model: Vietnam Region Options
 *
 * Cung cấp options cho dropdown Vietnam Region:
 * - 1: Miền Bắc
 * - 2: Miền Trung
 * - 3: Miền Nam
 */
namespace Magenest\Movie\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class VnRegion extends AbstractSource
{
    /**
     * Get all options
     *
     * @return array Options [{value: '', label: '-- Please Select --'}, ...]
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['value' => '', 'label' => __('-- Please Select --')],
                ['value' => '1', 'label' => __('Miền Bắc')],
                ['value' => '2', 'label' => __('Miền Trung')],
                ['value' => '3', 'label' => __('Miền Nam')]
            ];
        }
        return $this->_options;
    }
}

