<?php
namespace Magenest\Movie\Block\Adminhtml\Movie;

//use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magenest\Movie\Model\ResourceModel\Movie\CollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;

class Grid extends Extended
{

    protected $movieCollectionFactory;
    public function __construct(Context $context, Data $backendHelper, CollectionFactory $collectionFactory, array $data = [] )
    {
        $this->movieCollectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _prepareCollection()
    {
        $collection = $this->movieCollectionFactory->create();

//        join main_table với direactor
        $select = $collection->getSelect(); //bọc câu query select
        $select->joinLeft(
            ['director' => 'magenest_director'],
            'main_table.director_id = director.director_id',
            ['director_name' => 'director.name']
        );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    public function _prepareColumns()
    {
        $this->addColumn(
            'movie_id',
            [
                'header' => __('ID'),
                'index' => 'movie_id',
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
            ]
        );

        $this->addColumn(
            'description',
            [
                'header' => __('Description'),
                'index' => 'description',
            ]
        );

        $this->addColumn(
            'rating',
            [
                'header' => __('Rating'),
                'index' => 'rating',
            ]
        );

        $this->addColumn(
            'director_name',
            [
                'header' => __('Director'),
                'index' => 'director_name',
            ]
        );

        return parent::_prepareColumns();
    }
}


