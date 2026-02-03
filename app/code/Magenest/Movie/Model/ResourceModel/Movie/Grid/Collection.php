<?php
declare(strict_types=1);

namespace Magenest\Movie\Model\ResourceModel\Movie\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult{
    protected $_idFieldName = 'movie_id';

    protected function _construct()
    {
        $this->_init(
            \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
            \Magenest\Movie\Model\ResourceModel\Movie::class
        );
        $this->setMainTable('magenest_movie');
    }
}
