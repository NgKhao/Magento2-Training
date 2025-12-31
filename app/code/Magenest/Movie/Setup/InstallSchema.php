<?php

namespace Magenest\Movie\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements InstallSchemaInterface {
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

//        magenest_director
        $tableName = $installer->getTable('magenest_director');
        if(!$installer->getConnection()->isTableExists($tableName)){
            $table = $installer->getConnection()->newTable($tableName);
            $table->addColumn(
                'director_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ]
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT
            );
            $installer->getConnection()->createTable($table);
        }

//        magenest_actor
        $tableName = $installer->getTable('magenest_actor');
        if(!$installer->getConnection()->isTableExists($tableName)){
            $table = $installer->getConnection()->newTable($tableName);
            $table->addColumn(
                'actor_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ]
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT
            );
            $installer->getConnection()->createTable($table);
        }

//        magenest_movie
        $tableName = $installer->getTable('magenest_movie');
        if(!$installer->getConnection()->isTableExists($tableName)){
            $table = $installer->getConnection()->newTable($tableName);
            $table->addColumn(
                'movie_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ],
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            )->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            )->addColumn(
                'rating',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            )->addColumn(
                'director_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'unsigned' => true,
                ]
            )->addForeignKey(
                $installer->getFkName('magenest_movie', 'director_id', 'magenest_director', 'director_id'),
                'director_id',
                $installer->getTable('magenest_director'),
                'director_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('magenest_movie_actor');
        if(!$installer->getConnection()->isTableExists($tableName)){
            $table = $installer->getConnection()->newTable($tableName);
            $table->addColumn(
                'movie_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'unsigned' => true,
                ]
            )->addColumn(
                'actor_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'unsigned' => true,
                ]
            )->addForeignKey(
                $installer->getFkName('magenest_movie_actor', 'movie_id', 'magenest_movie', 'movie_id'),
                'movie_id',
                $installer->getTable('magenest_movie'),
                'movie_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE // xóa phim thì  xóa liết kế actor liên quan
            )->addForeignKey(
                $installer->getFkName('magenest_movie_actor', 'actor_id', 'magenest_actor', 'actor_id'),
                'actor_id',
                $installer->getTable('magenest_actor'),
                'actor_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_RESTRICT //không cho xóa actor nếu còn movie liên quan
            );

            $installer->getConnection()->createTable($table);
        }
    }
}
