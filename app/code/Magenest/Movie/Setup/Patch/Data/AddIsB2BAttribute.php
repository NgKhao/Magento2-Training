<?php
/**
 * Data Patch để thêm Customer Address Attribute: vn_region
 * Attribute này dùng để chọn miền Việt Nam: Bắc (1), Trung (2), Nam (3)
 */

namespace Magenest\Movie\Setup\Patch\Data;


use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class AddCustomerAddressVnRegionAttribute
 *
 * LOGIC CHÍNH:
 * 1. Sử dụng CustomerSetupFactory để làm việc với Customer EAV
 * 3. Set attribute type là 'int' vì value là số (1, 2, 3)
 * 6. Hiển thị attribute ở cả Admin và Frontend
 */
class AddIsB2BAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     * Dùng để thao tác với database khi install/upgrade module
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     * Factory để tạo CustomerSetup - công cụ chuyên xử lý Customer EAV attributes
     */
    private $customerSetupFactory;


    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * Apply patch - thêm attribute vn_region vào customer_address
     *
     * @return void
     */
    public function apply()
    {

        /** @var \Magento\Customer\Setup\CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $customerSetup->addAttribute(
            Customer::ENTITY, // Entity type: customer_address
            'is_b2b', // Attribute code
            [
                // === THÔNG TIN CƠ BẢN ===
                'label' => 'Is B2B Customer', // Label hiển thị
                'type' => 'int', // Kiểu dữ liệu
                'input' => 'boolean',

                // === CẤU HÌNH VALIDATION ===
                'required' => false, // Không bắt buộc (có thể để trống)
                'visible' => true, // Hiển thị attribute
                'user_defined' => true, // Attribute do user define (không phải system)
                'system' => false, // Không phải system attribute

                // === CẤU HÌNH ADMIN ===
                'position' => 100, // Vị trí hiển thị trong form (số càng lớn càng ở dưới)
                'sort_order' => 100, // Thứ tự sắp xếp
//
//                // === CẤU HÌNH FRONTEND ===
                'visible_on_front' => true, // Hiển thị ở frontend
            ]
        );


        /**
         * BƯỚC QUAN TRỌNG: Gán vào attribute set và group trong table eav_entity_attribute
         */
        $attributeSetId = $customerSetup->getDefaultAttributeSetId(
            Customer::ENTITY,
        );

        $attributeGroupId = $customerSetup->getDefaultAttributeGroupId(Customer::ENTITY, $attributeSetId);
        $customerSetup->addAttributeToSet(
            Customer::ENTITY,
            $attributeSetId,
            $attributeGroupId,
            'is_b2b'
        );


        /**
         * BƯỚC QUAN TRỌNG: Lấy Attribute Object vừa tạo
         * - getAttribute() trả về EAV Attribute object
         * - Cần object này để set thêm config cho form hiển thị
         */
        $attribute = $customerSetup->getEavConfig()->getAttribute(
            Customer::ENTITY,
            'is_b2b'
        );


        /**
         * BƯỚC QUAN TRỌNG: Gán Attribute vào Forms
         *
         * used_in_forms: Quy định attribute hiển thị ở form nào
         *
         * - adminhtml_customer_address: Form thêm/sửa địa chỉ trong Admin
         * - customer_address_edit: Form chỉnh sửa địa chỉ ở Frontend (My Account)
         * - customer_register_address: Form đăng ký địa chỉ khi register customer
         * - customer_account_create: Form tạo tài khoản mới (Frontend)
         *
         * Nếu không set, attribute sẽ KHÔNG hiển thị trong form!
         */
        $attribute->setData('used_in_forms', [
            'adminhtml_customer',
        ]);

        /**
         * BƯỚC CUỐI: Save Attribute
         * - Lưu config used_in_forms vào DB
         * - Sử dụng AttributeResource để save
         */
        $attribute->save();
    }

    /**
     * Get Dependencies
     *
     * Chỉ định patch nào phải chạy TRƯỚC patch này
     * Return: array of patch class names
     *
     * Hiện tại: không có dependency
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get Aliases
     *
     * Các tên gọi khác của patch này (dùng khi rename patch)
     * Return: array of alias names
     */
    public function getAliases()
    {
        return [];
    }
}

