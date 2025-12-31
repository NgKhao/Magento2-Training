<?php
namespace Packt\HelloWorld\Model\ResourceModel;
class Subscription extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
    public function _construct() {
        // Tham số 1: Tên bảng trong database
        // Tham số 2: Tên cột khóa chính (Primary Key)
        $this->_init('packt_helloworld_subscription', 'subscription_id');
    }
}
