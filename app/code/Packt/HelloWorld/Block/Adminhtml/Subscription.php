<?php
namespace Packt\HelloWorld\Block\Adminhtml;
class Subscription extends \Magento\Backend\Block\Widget\Grid\Container
{
//    tạo 1 block contain grid
    protected function _construct()
    {
        $this->_blockGroup = 'Packt_HelloWorld'; // Tên Vendor_Module
        $this->_controller = 'adminhtml_subscription';// Định danh controller, tự tìm file Grid.php cùng thư mục
        parent::_construct();
    }
}
