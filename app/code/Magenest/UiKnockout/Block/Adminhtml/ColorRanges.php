<?php
namespace Magenest\UiKnockout\Block\Adminhtml;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class ColorRanges extends AbstractFieldArray
{

    /**
     * Prepare to render
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('color_label', [
            'label' => __('Color Name'),
            'class' => 'required-entry'
        ]);

        $this->addColumn('color_code', [
            'label' => __('Color Code (Hex)'),
            'class' => 'required-entry' // validate bắt buộc nhập
        ]);

        $this->_addAfter = false; // Tắt nút Add After nếu không cần
        $this->_addButtonLabel = __('Add Color'); // Label nút thêm
    }
}
