<?php
namespace Magenest\Movie\Model;

use Magento\Framework\Model\AbstractModel;

class Category extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Magenest\Movie\Model\ResourceModel\Category::class);
    }
}

