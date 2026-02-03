<?php
namespace Magenest\Movie\Model\ResourceModel\BlogCategory;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Magenest\Movie\Model\BlogCategory::class, \Magenest\Movie\Model\ResourceModel\BlogCategory::class);
    }
}

