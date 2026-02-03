<?php
namespace Magenest\UiKnockout\Model;

use Magento\Framework\Model\AbstractModel;

class Banner extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Magenest\UiKnockout\Model\ResourceModel\Banner::class);
    }
}
