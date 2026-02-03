<?php
namespace Magenest\Movie\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddCourseAttributes implements DataPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;
    private CategorySetupFactory $categorySetupFactory;
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    public function apply()
    {
        /**
         * @var CategorySetup $categorySetup
         */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $categorySetup->addAttribute(
            Product::ENTITY,
            'course_start_time',
            [
                'type' => 'datetime',
                'input' => 'date',
                'label' => 'Course Start Time',
                'required' => false,
                'user_defined' => true,
                'visible' => true,
                'system' => 0,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible_on_front' => true,
                'group' => 'General',
            ]
        );

        $categorySetup->addAttribute(
            Product::ENTITY,
            'course_end_time',
            [
                'type' => 'datetime',
                'input' => 'date',
                'label' => 'Course End Time',
                'required' => false,
                'user_defined' => true,
                'visible' => true,
                'system' => 0,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible_on_front' => true,
                'group' => 'General',
            ]
        );

        $categorySetup->addAttributeToSet(
            Product::ENTITY,
            25,
            'General', // Tên group trong set đó
            'course_start_time'
        );
        $categorySetup->addAttributeToSet(
            Product::ENTITY,
            25,
            'General', // Tên group trong set đó
            'course_end_time'
        );
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
