<?php
namespace Magenest\UiKnockout\Model\ResourceModel\Banner;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Magenest\UiKnockout\Model\Banner::class,
            \Magenest\UiKnockout\Model\ResourceModel\Banner::class
        );
    }
}
