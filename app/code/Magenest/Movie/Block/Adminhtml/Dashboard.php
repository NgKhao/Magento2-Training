<?php
namespace Magenest\Movie\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Module\ModuleListInterface;

class Dashboard extends Template
{
    private  ResourceConnection $resourceConnection;
    private  ModuleListInterface $moduleList;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = [],
        ModuleListInterface $moduleList,
        ResourceConnection $resourceConnection,
    ){
        $this->resourceConnection = $resourceConnection;
        $this->moduleList = $moduleList;
        parent::__construct($context, $data);
    }

    /**
     * @return int|null
     */
    public function getTotalModules()
    {
        return count($this->moduleList->getAll());
    }

    public function getThirdPartyModules()
    {
        // lấy các module thuộc m2
        $all = array_keys($this->moduleList->getAll());
        $cnt = 0;
        foreach ($all as $module) {
            if(str_starts_with($module, 'Magento_')) {
                $cnt++;
            }
        }
        return $this->getTotalModules() - $cnt;
    }

    /**
     * @param string $table
     * @return int
     */
    private function countTable(string $table)
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $connection->getTableName($table);
        $sql = "SELECT COUNT(*) FROM $table";
        return (int) $connection->fetchOne($sql);
    }

    public function getCustomersCount(): int
    {
        return $this->countTable('customer_entity');
    }

    public function getProductsCount(): int
    {
        return $this->countTable('catalog_product_entity');
    }

    public function getOrdersCount(): int
    {
        return $this->countTable('sales_order');
    }

    public function getInvoicesCount(): int
    {
        return $this->countTable('sales_invoice');
    }

    public function getCreditmemosCount(): int
    {
        return $this->countTable('sales_creditmemo');
    }

}
