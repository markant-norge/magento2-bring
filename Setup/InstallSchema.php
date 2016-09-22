<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Markant\Bring\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    const TABLE_NAME = 'sales_shipment_edi';


    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if  (!$installer->getConnection()->isTableExists(self::TABLE_NAME)) {


            /**
             * Create table 'sales_shipment_edi'
             */
            $table = $installer->getConnection()->newTable(
                $installer->getTable(self::TABLE_NAME)
            )->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )->addColumn(
                'parent_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Parent Id'
            )->addColumn(
                'weight',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Weight'
            )->addColumn(
                'length',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Length'
            )->addColumn(
                'height',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Height'
            )->addColumn(
                'width',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Width'
            )->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Order Id'
            )->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Title'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addIndex(
                $installer->getIdxName('sales_shipment_edi', ['parent_id']),
                ['parent_id']
            )->addIndex(
                $installer->getIdxName('sales_shipment_edi', ['order_id']),
                ['order_id']
            )->addIndex(
                $installer->getIdxName('sales_shipment_edi', ['created_at']),
                ['created_at']
            )->addForeignKey(
                $installer->getFkName('sales_shipment_edi', 'parent_id', 'sales_shipment', 'entity_id'),
                'parent_id',
                $installer->getTable('sales_shipment'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment(
                'Sales Flat Shipment EDI'
            );
            $installer->getConnection()->createTable($table);

            $connection = $installer->getConnection();

            $connection
                ->addColumn(
                    $installer->getTable(self::TABLE_NAME),
                    'label_url',
                    ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'nullable' => true, 'comment' => 'From bring: Label URL']
                );


            $connection->addColumn(
                $installer->getTable(self::TABLE_NAME),
                'waybill',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'nullable' => true, 'comment' => 'From bring: Waybill']
            );

            $connection->addColumn(
                $installer->getTable(self::TABLE_NAME),
                'tracking',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'nullable' => true, 'comment' => 'From bring: Tracking URL']
            );

            $connection->addColumn(
                $installer->getTable(self::TABLE_NAME),
                'consignment_number',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'nullable' => true, 'comment' => 'From bring: Consignment number']
            );

            $connection->addColumn(
                $installer->getTable(self::TABLE_NAME),
                'package_numbers',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'nullable' => true, 'comment' => 'From bring: Package numbers (serialized)']
            );

            $connection->addColumn(
                $installer->getTable(self::TABLE_NAME),
                'earliest_pickup',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, 'nullable' => true, 'comment' => 'From bring: Earliest pickup']
            );

            $connection->addColumn(
                $installer->getTable(self::TABLE_NAME),
                'expected_delivery',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, 'nullable' => true, 'comment' => 'From bring: Expected delivery']
            );



            $connection
                ->addColumn(
                    $installer->getTable(self::TABLE_NAME),
                    'return_label_url',
                    ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'nullable' => true, 'comment' => 'From bring: Return Label URL']
                );
                

        }
        $installer->endSetup();
    }
}
