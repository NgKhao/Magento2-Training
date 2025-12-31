<?php

namespace Packt\SEO\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;

//đối tượng cung cấp thông tin về phiên bản module hiện tại
use Magento\Framework\Setup\SchemaSetupInterface;

//đối tượng cung cấp các phương thức để thao tác với DB

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup(); //bắt đầu quá trình nâng  cấp

        if (version_compare($context->getVersion(), '2.0.2') < 0) {
            $connection = $setup->getConnection(); //lấy kết nối DB hiện tại

            //Tạo bảng mới packt_seo_log
            $table = $connection->newTable(
                $setup->getTable('packt_seo_log')
            )->addColumn(
                'log_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, //kiểu dữ liệu cột
                null, //độ dài cột
                [
                    'identity' => true, //tự động tăng
                    'unsigned' => true, //không âm
                    'nullable' => false, //không cho phép null
                    'primary' => true //khóa chính
                ],
            )->addColumn(
                'message',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
            );

            $connection->createTable($table);


            $tableName = $setup->getTable('catalog_product_entity_varchar');
            if ($connection->isTableExists($tableName)) {
                $connection->addColumn(
                    $tableName,
                    'is_canonical_enabled',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    [
                        'nullable' => false,
                        'default' => 0,
                        'comment' => 'Is Canonical Enabled'
                    ],
//                    mặc dù nullable => false và default => '0', nhưng khi thêm cột mới vào bảng đã tồn tại,
//                    đều sẽ có giá trị null cho đến khi thực hiện update
                );
                $connection->update($tableName, ['is_canonical_enabled' => 0]);
            }
        }

        $setup->endSetup(); //kết thúc quá trình nâng cấp
    }
}
