<?php

namespace Magenest\Movie\Controller\Adminhtml\Dashboard;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{

    protected $resultPageFactory;

    public const ADMIN_RESOURCE = 'Magenest_Movie::dashboard';

    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        // Highlight menu bên trái
        $resultPage->setActiveMenu('Magenest_Movie::dashboard');

        // Title trên tab + header
        $resultPage->getConfig()->getTitle()->prepend(__('Request Report'));
        return $resultPage;
    }
}
