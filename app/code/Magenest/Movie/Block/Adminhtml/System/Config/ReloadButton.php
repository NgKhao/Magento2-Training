<?php

namespace Magenest\Movie\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ReloadButton extends Field
{
    protected function _getElementHtml(AbstractElement $element)
    {
        return '<button type="button" onclick="location.reload()">Reload Page</button>';
    }

}
