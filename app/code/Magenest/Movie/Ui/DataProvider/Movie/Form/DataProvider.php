<?php
namespace Magenest\Movie\Ui\DataProvider\Movie\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magenest\Movie\Model\ResourceModel\Movie\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    private array $loadedData = [];

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if($this->loadedData) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        foreach ($items as $movie) {
            $this->loadedData[$movie->getId()] = $movie->getData();
        }

        return $this->loadedData;
    }
}
