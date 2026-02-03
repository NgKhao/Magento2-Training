<?php
/**
 * Data Patch để thêm Customer Address Attribute: vn_region
 * Attribute này dùng để chọn miền Việt Nam: Bắc (1), Trung (2), Nam (3)
 */

namespace Magenest\Movie\Setup\Patch\Data;


use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class AddCustomerAddressVnRegionAttribute
 *
 * LOGIC CHÍNH:
 * 1. Sử dụng CustomerSetupFactory để làm việc với Customer EAV
 * 2. Tạo attribute cho entity 'customer_address' (KHÔNG phải 'customer')
 * 3. Set attribute type là 'int' vì value là số (1, 2, 3)
 * 4. Set input type là 'select' để hiển thị dropdown
 * 5. Tạo Source Model để cung cấp options cho dropdown
 * 6. Hiển thị attribute ở cả Admin và Frontend
 */
class AddCustomerAddressVnRegionAttribute implements DataPatchInterface
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
     * WORKFLOW:
     * 1. Start setup transaction
     * 2. Tạo CustomerSetup instance
     * 3. Add attribute với config đầy đủ
     * 4. Lấy attribute object vừa tạo
     * 5. Gán attribute vào form admin và frontend
     * 6. Save attribute
     * 7. End setup transaction
     *
     * @return void
     */
    public function apply()
    {

        /** @var \Magento\Customer\Setup\CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        /**
         * BƯỚC QUAN TRỌNG: Add Attribute
         *
         * Entity: AddressMetadataInterface::ENTITY_TYPE_ADDRESS = 'customer_address'
         * - Đây là entity type cho địa chỉ khách hàng
         * - Khác với 'customer' (thông tin khách hàng)
         *
         * Attribute Code: 'vn_region'
         * - Tên unique của attribute trong hệ thống
         *
         * Config Array - các tham số cấu hình:
         */
        $customerSetup->addAttribute(
            AddressMetadataInterface::ENTITY_TYPE_ADDRESS, // Entity type: customer_address
            'vn_region', // Attribute code
            [
                // === THÔNG TIN CƠ BẢN ===
                'label' => 'Vietnam Region', // Label hiển thị
                'type' => 'int', // Kiểu dữ liệu: int (vì value là 1, 2, 3)
                'input' => 'select', // Input type: dropdown select

                // === SOURCE MODEL ===
                // Cung cấp options cho dropdown (Bắc, Trung, Nam)
                'source' => \Magenest\Movie\Model\Config\Source\VnRegion::class,

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
//                'is_used_in_grid' => false, // Không dùng trong grid admin
//                'is_visible_in_grid' => false, // Không hiển thị column trong grid
//                'is_filterable_in_grid' => false, // Không filter được trong grid
//                'is_searchable_in_grid' => false, // Không search được trong grid
            ]
        );


        /**
         * BƯỚC QUAN TRỌNG: Gán vào attribute set và group trong table eav_entity_attribute
         */
        $attributeSetId = $customerSetup->getDefaultAttributeSetId(
            AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
        );

        $attributeGroupId = $customerSetup->getDefaultAttributeGroupId(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $attributeSetId);
        $customerSetup->addAttributeToSet(
            AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
            $attributeSetId,
            $attributeGroupId,
            'vn_region'
        );


        /**
         * BƯỚC QUAN TRỌNG: Lấy Attribute Object vừa tạo
         * - getAttribute() trả về EAV Attribute object
         * - Cần object này để set thêm config cho form hiển thị
         */
        $attribute = $customerSetup->getEavConfig()->getAttribute(
            AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
            'vn_region'
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
            'adminhtml_customer_address', // Admin: Customer > Addresses > Add/Edit
            'customer_address_edit', // Frontend: My Account > Address Book > Add/Edit
            'customer_register_address', // Frontend: Register form (nếu có address)
            'customer_account_create', // Frontend: Create account form
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

