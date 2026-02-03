<?php
namespace Magenest\Movie\Ui\Component\Form;

use Magento\Framework\Data\OptionSourceInterface;
use Magenest\Movie\Model\ResourceModel\Director\CollectionFactory;

class DirectorOptions implements OptionSourceInterface
{
    protected $collectionFactory;

    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return void
     */
    public function toOptionArray()
    {
        $options = [];
        $collection = $this->collectionFactory->create();
        foreach ($collection as $director) {
            $options[] = [
                'value' => $director->getDirectorId(),
                'label' => $director->getName()
            ];
        }
        return $options;
    }
}
