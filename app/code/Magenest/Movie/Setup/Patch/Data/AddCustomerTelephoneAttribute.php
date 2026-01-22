<?php
/**
 * Data Patch để tạo customer attribute "telephone"
 *
 * Magento 2.3+ sử dụng Data Patch thay vì InstallData/UpgradeData
 * Data Patch có những ưu điểm:
 * - Tự động track patch đã chạy hay chưa qua bảng patch_list
 * - Có thể khai báo dependencies giữa các patch
 * - Dễ maintain và mở rộng hơn
 */
namespace Magenest\Movie\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddCustomerTelephoneAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * Constructor - Inject dependencies
     *
     * @param ModuleDataSetupInterface $moduleDataSetup - Để thao tác với database
     * @param CustomerSetupFactory $customerSetupFactory - Để tạo customer attributes
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * Phương thức apply() sẽ được gọi khi chạy setup:upgrade
     * Đây là nơi ta viết logic tạo attribute
     *
     * @return void
     */
    public function apply()
    {
        // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
        $this->moduleDataSetup->getConnection()->startSetup();

        // Tạo CustomerSetup instance để thao tác với customer attributes
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        /**
         * Customer::ENTITY = 'customer' - Entity type code
         * 'telephone' - Attribute code (tên attribute)
         *
         * Các tham số quan trọng:
         */
        $customerSetup->addAttribute(
            Customer::ENTITY,
            'telephone',
            [
                // Kiểu dữ liệu trong database: varchar, int, text, datetime...
                'type' => 'varchar',

                // Label hiển thị trên form
                'label' => 'Telephone',

                // Loại input: text, textarea, select, multiselect, date...
                'input' => 'text',

                // Có bắt buộc nhập không
                'required' => false,

                // Có hiển thị trên form không
                'visible' => true,

                // Có phải system attribute không (false = user defined)
                'system' => false,

                // User có thể tự định nghĩa attribute này
                'user_defined' => true,

                // Vị trí hiển thị trên form
                'position' => 100,
                'sort_order' => 100,

                // Backend Model để validate và xử lý dữ liệu
                // Đây là class ta vừa tạo ở BƯỚC 1
                'backend' => \Magenest\Movie\Model\Customer\Attribute\Backend\Telephone::class,

//                // Validate classes - Có thể thêm validate ở frontend
//                'validate_rules' => json_encode([
//                    'max_text_length' => 15,  // Cho phép +84 ban đầu
//                    'min_text_length' => 10
//                ]),
            ]
        );

        /**
         * Khai báo attribute được dùng ở đâu
         *
         * used_in_forms: Các form mà attribute sẽ hiển thị
         *
         * Danh sách các form có sẵn:
         * - 'customer_account_create' : Form đăng ký customer ở frontend
         * - 'customer_account_edit' : Form edit customer ở frontend
         * - 'adminhtml_customer' : Form create/edit customer trong admin
         * - 'checkout_register' : Form checkout registration
         * - 'adminhtml_checkout' : Form admin create order
         */
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'telephone');
        $attribute->setData('used_in_forms', [
            'customer_account_create',    // Registration form
            'customer_account_edit',      // Customer edit form
            'adminhtml_customer',         // Admin customer create/edit
            'adminhtml_checkout'          // Admin create order
        ]);

        $attribute->save();

        // Kết thúc transaction
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Khai báo dependencies - Patch nào cần chạy trước patch này
     *
     * Ví dụ: Nếu patch này phụ thuộc vào UpdateCustomerAvatarBackend
     * thì return [\Magenest\Movie\Setup\Patch\Data\UpdateCustomerAvatarBackend::class];
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Aliases của patch - Tên cũ của patch (nếu rename)
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}

