<?php
namespace Packt\HelloWorld\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Packt_HelloWorld::index';

    protected $resultPageFactory;

    public function __construct(
        Context     $context,
        PageFactory $resultPageFactory
    ){
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }



//    protected function _isAllowed()
//    {
//        return $this->_authorization->isAllowed('Packt_HelloWorld::index');
//    }
}
