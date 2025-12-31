<?php
namespace Packt\SEO\Setup;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
class UpgradeData implements UpgradeDataInterface {
    protected $resourceConfig;


    public function __construct(\Magento\Config\Model\ResourceModel\Config $resourceConfig ) {
//        \Magento\Config\Model\ResourceModel\Config $resourceConfig: lưu, cập nhật hoặc xóa các giá trị cấu hình trong bảng core_config_data
        $this->resourceConfig = $resourceConfig;
    }
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.1') < 0) {
            $this->resourceConfig->saveConfig(
                'web/cookie/cookie_lifetime',
                '8400',
                \Magento\Config\Block\System\Config\Form::SCOPE_DEFAULT,
                0
            );
        }
        if(version_compare($context->getVersion(), '2.0.2') < 0){
            $connection = $setup->getConnection();
            $tableName = $setup->getTable('packt_seo_log');

            $connection->insert($tableName, ['message' => 'Upgrade to version 2.0.2 completed successfully.']);


        }
        $setup->endSetup();
    }
}
