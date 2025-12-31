<?php
namespace Packt\HelloWorld\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpdateVisitorData implements DataPatchInterface
{
    private $moduleDataSetup;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('helloworld_visitor_log'),
            ['visitor_name' => "update visitor_name"],
            ['visitor_name = ?' => 'Guest1']
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
