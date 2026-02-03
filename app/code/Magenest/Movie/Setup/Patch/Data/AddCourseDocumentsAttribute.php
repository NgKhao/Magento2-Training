<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║  ADD COURSE DOCUMENTS ATTRIBUTE                                            ║
 * ║                                                                           ║
 * ║  Cách tiếp cận: Sử dụng EAV Attribute với backend serialized              ║
 * ║                                                                           ║
 * ║  Ưu điểm so với tạo bảng riêng:                                           ║
 * ║  - Không cần tạo Model/ResourceModel/Collection riêng                     ║
 * ║  - Tự động được lưu khi save product                                      ║
 * ║  - Không cần Observer để save data                                        ║
 * ║  - Đơn giản hơn nhiều!                                                    ║
 * ║                                                                           ║
 * ║  Data được lưu dạng JSON trong bảng catalog_product_entity_text           ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */
namespace Magenest\Movie\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddCourseDocumentsAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * Apply patch
     *
     * @return void
     */
    public function apply()
    {
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);

        /**
         * Tạo attribute course_documents
         *
         * - type = 'text': Lưu trong bảng catalog_product_entity_text (cho phép data lớn)
         * - input = 'text': Không hiện trong form mặc định (ta sẽ dùng Modifier)
         * - backend = 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend':
         *   Tự động serialize/unserialize array data
         * - is_used_in_grid = false: Không hiện trong product grid
         */
        $categorySetup->addAttribute(
            Product::ENTITY,
            'course_documents',
            [
                'type' => 'text',                    // Lưu trong _text table
                'label' => 'Course Documents',
                'input' => 'text',                   // Input type (sẽ override bằng UI)
                'required' => false,
                'user_defined' => true,
                'visible' => false,                  // Ẩn trong form mặc định
                'system' => 0,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible_on_front' => false,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                // Backend model để handle serialize/deserialize
                'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
            ]
        );

        // Add to attribute set 25 (Course attribute set)
        // Bạn có thể bỏ qua nếu muốn attribute này available cho tất cả products
        $categorySetup->addAttributeToSet(
            Product::ENTITY,
            25,                     // Attribute Set ID cho Course
            'General',              // Group name
            'course_documents'
        );
    }

    /**
     * Get dependencies
     *
     * @return array
     */
    public static function getDependencies()
    {
        // Chạy sau AddCourseAttributes nếu cần
        return [AddCourseAttributes::class];
    }

    /**
     * Get aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}

