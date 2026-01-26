<?php
namespace Magenest\Movie\Model\ResourceModel\Category;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Magenest\Movie\Model\Category::class, \Magenest\Movie\Model\ResourceModel\Category::class);
    }
}

