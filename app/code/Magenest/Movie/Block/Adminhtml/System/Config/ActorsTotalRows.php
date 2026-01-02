<?php
namespace Magenest\Movie\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;
use Magenest\Movie\Model\ResourceModel\Actor\CollectionFactory;

class ActorsTotalRows extends Field
{
    protected $actorCollectionFactory;
    public function __construct(Context $context, CollectionFactory $actorCollectionFactory, array $data = [])
    {
        $this->actorCollectionFactory = $actorCollectionFactory;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $actorCount = $this->getActorsCount(); //số lượng movie
        $element->setValue($actorCount); //set value vào input
        $element->setReadonly(true);
        return parent::_getElementHtml($element);
    }

    public function getActorsCount()
    {
        $collection = $this->actorCollectionFactory->create();
        return $collection->getSize();
    }
}
