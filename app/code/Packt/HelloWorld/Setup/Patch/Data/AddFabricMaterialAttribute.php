<?php
declare(strict_types=1); // ép kiểu nghiêm ngặt
namespace Packt\HelloWorld\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddFabricMaterialAttribute implements DataPatchInterface
{
    private $moduleDataSetup;
    private $eavSetupFactory;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup, EavSetupFactory  $EavSetupFactory)
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $EavSetupFactory;
    }

    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'fabric_material',
            [ //
                'type' => 'varchar',
                'label' => 'Fabric Material',
                 'input' => 'select',
                'source' => \Packt\HelloWorld\Model\Config\Source\FabricMaterial::class,
                'required' => false,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'user_defined' => true, // thuộc tính do người dùng định nghĩa
                'searchable' => true, // có thể tìm kiếm
                'filterable' => true, // có thể lọc
                'comparable' => false, // không thể so sánh
                'visible_on_front' => true, // hiển thị trên frontend
                'used_in_product_listing' => true, // sử dụng trong danh sách sản phẩm
                'unique' => false, // không phải là thuộc tính duy nhất
                'group' => 'Product Details', // Nhóm thuộc tính trong tab chỉnh sửa sản phẩm,
                'is_used_in_grid' => true, // Sử dụng trong grid quản trị
                'is_visible_in_grid' => true, // Hiển thị trong grid quản trị
                'is_filterable_in_grid' => true // Có thể lọc trong grid quản trị
            ]
        );
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'helloworld_attribute');
    }

    public static function getDependencies() // Phụ thuộc của patch này
    {
        return []; // Không có phụ thuộc nào
    }

    public function getAliases() // Bí danh cho patch này
    {
        return [];
    }
}
