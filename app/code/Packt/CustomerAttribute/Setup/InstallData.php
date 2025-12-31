<?php
namespace Packt\CustomerAttribute\Setup;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
class InstallData implements InstallDataInterface
{
    private $customerSetupFactory;
    public function __construct(\Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]); // khởi tạo
        $setup->startSetup();
        $customerSetup->addAttribute('customer', //entity type
            'loyaltynumber', [  //mã thuột tính
                'label' => 'Loyaltynumber',
                'type' => 'static', //static nghĩa là Magento sẽ tìm cột loyaltynumber trực tiếp
                // trong bảng chính (customer_entity)
                'frontend_input' => 'text',
                'required' => false,
                'visible' => true,
                'position' => 105,
            ]);

        // lấy ra đối tượng thuột tính vừa tạo
        $loyaltyAttribute = $customerSetup->getEavConfig()->getAttribute('customer', 'loyaltynumber');

        // 3. Đăng ký hiển thị thuộc tính này vào Form Admin
        $loyaltyAttribute->setData('used_in_forms', ['adminhtml_customer']);
        $loyaltyAttribute->save();
        $setup->endSetup();
    }
}
