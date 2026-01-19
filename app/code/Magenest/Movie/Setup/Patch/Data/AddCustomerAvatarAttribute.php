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
                'input' => 'text', //UI component sẽ render uploader, DB vẫn lưu path (varchar)
                'required' => false,
                'visible' => true,
            ]
        );

        //lưu attrive vào form customer
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
