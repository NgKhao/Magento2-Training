<?php
namespace Magenest\Movie\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddCustomerAvatarAttribute implements DataPatchInterface {
    private $moduleDataSetup;
    private $customerSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ){
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->addAttribute(
            Customer::ENTITY,
            'avatar',
            [
                'type' => 'varchar',
                'label' => 'Avatar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'system' => false,
                'user_defined' => true,
                'position' => 100,
                'sort_order' => 100,
                'backend' => \Magenest\Movie\Model\Customer\Attribute\Backend\Avatar::class,
            ]
        );

        // Lưu attribute vào form customer
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'avatar');
        $attribute->setData('used_in_forms', ['adminhtml_customer']);
        $attribute->save();

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies(){
        return [];
    }

    public function getAliases(){
        return [];
    }
}
