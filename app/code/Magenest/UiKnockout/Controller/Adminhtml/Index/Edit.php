<?php

namespace Magenest\UiKnockout\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{
    protected $resultPageFactory;

    public function __construct(Action\Context $context, PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        // 1. Tạo trang
        $resultPage = $this->resultPageFactory->create();

        // 2. Lấy ID từ URL (nếu có -> đang sửa, nếu không -> đang tạo mới)
        $id = $this->getRequest()->getParam('banner_id');

        // 3. Set tiêu đề trang
        $title = $id ? __('Edit Banner') : __('New Banner');
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_UiKnockout::banner');
    }
}
