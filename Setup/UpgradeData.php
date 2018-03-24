<?php
namespace Markant\Bring\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        $version = $productMetadata->getVersion(); //will return the magento version
        
        // If >2.2.0 we need to check that the config is stored using json
        if (version_compare($version, '2.2.0') >= 0) {
            $setup->startSetup();

            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('core_config_data'); //gives table name with prefix
            $result = $connection->fetchAll("SELECT * FROM $tableName WHERE path='carriers/bring/calculation/custom_method_prices'");

            foreach ($result as $r) {
                $this->serializeToJson($r);
            }
            die('123');

            $setup->endSetup();
        }
    }
    
    
    private function serializeToJson($line) {
        $value = @unserialize($line['value']);
        
        if ($value!==false && $line['config_id']) {
            $newValue = json_encode($value);
            
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('core_config_data'); //gives table name with prefix
            
            $stmt = $connection->prepare("UPDATE $tableName SET value=:value WHERE config_id=:config_id");
            $stmt->bindValue(":value", $newValue);
            $stmt->bindValue(":config_id", $line['config_id']);
            $stmt->execute();
        }
    }
    
}