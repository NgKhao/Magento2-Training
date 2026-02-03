<?php

namespace Magenest\Movie\Controller\Modal;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    protected $pageFactory;
    public function __construct(
        PageFactory $pageFactory,
        Context $context,
    ) {
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $page =  $this->pageFactory->create();

        return $page;
    }
}
