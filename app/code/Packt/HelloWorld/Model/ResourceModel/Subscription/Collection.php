<?php
namespace Packt\HelloWorld\Model\ResourceModel\Subscription;
/**
 * Subscription Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct() {
        // Tham số 1: Đường dẫn đến Model (để biết 1 dòng dữ liệu là object gì)
        // Tham số 2: Đường dẫn đến Resource Model (để biết lấy dữ liệu ở bảng nào)
        $this->_init('Packt\HelloWorld\Model\Subscription',
            'Packt\HelloWorld\Model\ResourceModel\Subscription');
    }
}
