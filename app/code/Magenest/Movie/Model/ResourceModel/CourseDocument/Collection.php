<?php
namespace Magenest\Movie\Model\ResourceModel\CourseDocument;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magenest\Movie\Model\CourseDocument',
            'Magenest\Movie\Model\ResourceModel\CourseDocument');
    }
}
