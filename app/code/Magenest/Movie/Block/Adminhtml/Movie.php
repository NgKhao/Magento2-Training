<?php

namespace Magenest\Movie\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Movie extends Container
{
    protected function _construct()
    {
        $this->_blockGroup = 'Magenest_Movie';
        $this->_controller = 'adminhtml_movie';
        $this->_addButtonLabel = __('New Movie');
        parent::_construct();
    }
}
