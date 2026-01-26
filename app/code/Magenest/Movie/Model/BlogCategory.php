<?php
namespace Magenest\Movie\Model;

use Magento\Framework\Model\AbstractModel;

class BlogCategory extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Magenest\Movie\Model\ResourceModel\BlogCategory::class);
    }
}

