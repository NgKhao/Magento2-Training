<?php
namespace Magenest\Movie\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ColoredLabel extends Field
{
    public function render(AbstractElement $element)
    {
        $element->setLabel(
            'Yes/No_Field_<span style="color:red;">abcd</span>'
        );

        return parent::render($element);
    }
}
