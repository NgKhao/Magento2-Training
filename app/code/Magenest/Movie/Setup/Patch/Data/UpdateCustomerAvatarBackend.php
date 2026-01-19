<?php

declare(strict_types=1);

namespace Magenest\Movie\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Model\Customer;

class UpdateCustomerAvatarBackend implements DataPatchInterface
{
    private $moduleDataSetup;
    private $customerSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory     $customerSetupFactory
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $customerSetup->updateAttribute(
            Customer::ENTITY,
            'avatar',
            'backend_model',
            \Magenest\Movie\Model\Customer\Attribute\Backend\Avatar::class
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
