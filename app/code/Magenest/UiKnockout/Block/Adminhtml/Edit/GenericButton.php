<?php

namespace Magenest\UiKnockout\Block\Adminhtml\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;

// file này dùng để tạo các nút (button) trong form Edit Banner
class GenericButton
{
    protected $context;
    protected $registry;

    public function __construct(
        Context                     $context,
        \Magento\Framework\Registry $registry
    )
    {
        $this->context = $context;
        $this->registry = $registry;
    }

    /**
     * Lấy ID của Banner hiện tại (nếu đang Edit)
     */
    public function getBannerId()
    {
        return $this->context->getRequest()->getParam('banner_id');
    }

    /**
     * Hàm tạo URL
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
