<?php
namespace Magenest\UiKnockout\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;

class NewAction extends Action
{
    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    public function __construct(Context $context, ForwardFactory $resultForwardFactory)
    {
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();

        // LOGIC QUAN TRỌNG:
        // Chuyển tiếp request này sang action 'edit'.
        // Nghĩa là: Khi user vào 'banner/index/new', hệ thống sẽ tự chạy code của 'banner/index/edit'
        return $resultForward->forward('edit');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_UiKnockout::banner');
    }
}
