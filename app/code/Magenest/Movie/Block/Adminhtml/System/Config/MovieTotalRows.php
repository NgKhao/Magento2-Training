<?php
namespace Magenest\Movie\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magenest\Movie\Model\ResourceModel\Movie\CollectionFactory;
use Magento\Backend\Block\Template\Context;

class MovieTotalRows extends Field
{
    protected $movieCollectionFactory;
    public function __construct(Context $context, CollectionFactory $movieCollectionFactory, array $data = [])
    {
        $this->movieCollectionFactory = $movieCollectionFactory;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $movieCount = $this->getMovieCount(); //số lượng movie
        $element->setValue($movieCount); //set value vào input
        $element->setReadonly(true);
        return parent::_getElementHtml($element);
    }

    public function getMovieCount()
    {
        $collection = $this->movieCollectionFactory->create();
        return $collection->getSize();
    }
}
