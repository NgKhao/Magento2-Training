<?php
namespace Packt\HelloWorld\Controller\Index;
use Magento\Framework\View\Result\PageFactory;
use Magento\Catalog\Model\Product;

class Index extends \Magento\Framework\App\Action\Action
{
    /** @var \Magento\Framework\View\Result\PageFactory */
    protected $resultPageFactory;
    protected $product;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Product $product
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->product = $product;
        parent::__construct($context);
    }
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $p1 = $this->product->load(5);
        echo $p1->getName() . "<br>";
        $p2 = $this->product->load(6);
        echo $p2->getName() . "<br>";

        echo $p1->getName() . "<br>";
        exit;
        return $resultPage;
    }
}
