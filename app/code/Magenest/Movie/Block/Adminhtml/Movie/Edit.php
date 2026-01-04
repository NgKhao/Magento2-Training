<?php
namespace Magenest\Movie\Block\Adminhtml\Movie;

use Magento\Backend\Block\Widget\Form\Container;

class Edit extends Container
{
    protected function _construct()
    {
        $this->_blockGroup = 'Magenest_Movie';
        $this->_controller = 'adminhtml_movie';
        $this->_mode = 'edit';
        $this->buttonList->update('save', 'label', __('Save Movie'));
        $this->buttonList->update('delete', 'label', __('Delete Movie'));
        parent::_construct();
    }
}
