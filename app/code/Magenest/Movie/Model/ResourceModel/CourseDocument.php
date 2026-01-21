<?php
namespace Magenest\Movie\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CourseDocument extends AbstractDb
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        // Table name, Primary key
        $this->_init('magenest_course_document', 'id');
    }
}
