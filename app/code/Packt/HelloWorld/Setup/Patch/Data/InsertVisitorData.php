<?php
namespace Packt\HelloWorld\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InsertVisitorData implements DataPatchInterface
{
    private $moduleDataSetup;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $data = [
            ['visitor_name' => 'Guest1', 'visit_time' => '2023-01-01 00:00:00', 'product_id' => 5],
            ['visitor_name' => 'Guest2', 'visit_time' => '2023-01-02 00:00:00', 'product_id' => 6]
        ];

        $this->moduleDataSetup->getConnection()->insertArray(
            $this->moduleDataSetup->getTable('helloworld_visitor_log'),
            ['visitor_name', 'visit_time', 'product_id'],
            $data
        );

        $this->moduleDataSetup->endSetup();
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




