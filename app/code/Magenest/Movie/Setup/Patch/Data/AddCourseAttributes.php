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
                'input' => 'datetime',
                'label' => 'Course Start Time',
                'required' => false,
            ]
        );

        $categorySetup->addAttribute(
            Product::ENTITY,
            'course_end_time',
            [
                'type' => 'datetime',
                'input' => 'datetime',
                'label' => 'Course End Time',
            ]
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
