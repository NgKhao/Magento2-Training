<?php
/**
 * Plugin để thêm group_id vào Customer Section Data
 * Mục đích: JavaScript cần biết customer thuộc nhóm nào để hiển thị popup đúng đối tượng
 */
namespace Magenest\UiKnockout\Plugin;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\CustomerData\Customer;

class CustomerDataGroup
{
    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * Constructor
     *
     * @param CurrentCustomer $currentCustomer
     */
    public function __construct(CurrentCustomer $currentCustomer)
    {
        $this->currentCustomer = $currentCustomer;
    }

    /**
     * Thêm group_id vào customer section data
     *
     * After Plugin: Chạy SAU khi method getSectionData() của class Customer chạy xong
     *
     * @param Customer $subject Class gốc (không dùng trong trường hợp này)
     * @param array $result Kết quả trả về từ method gốc
     * @return array Kết quả đã được bổ sung thêm group_id
     */
    public function afterGetSectionData(Customer $subject, $result)
    {
        // Mặc định: Guest user = group 0
        $groupId = 0;

        // Nếu user đã đăng nhập, lấy group_id thực
        if ($this->currentCustomer->getCustomerId() !== null) {
            $groupId = $this->currentCustomer->getCustomer()->getGroupId();
        }

        // Thêm vào mảng kết quả
        $result['group_id'] = $groupId;

        return $result;
    }
}
