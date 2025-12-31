<?php
namespace Packt\HelloWorld\Controller\Index;
class Collection extends \Magento\Framework\App\Action\Action {
    public function execute() {
        $productCollection = $this->_objectManager->create
        ('Magento\Catalog\Model\ResourceModel\Product\Collection')
            ->addAttributeToSelect(['name', 'price','image',])// lấy thêm thuộc tính
//            ->addAttributeToFilter('name', array( // lọc tìm kiếm
//                'like' => '%iphone V%'
//            ));
            ->addAttributeToFilter('entity_id', array( // lọc theo theo danh sách
                'in' => array(5, 12)
            ));
//            ->addAttributeToFilter('name', 'iphone ix'); // lọc chính xác
//            ->setPageSize(5,1);
        $productCollection->setDataToAll('price', 20);
        $productCollection->save();

        $output = '';
        foreach ($productCollection as $product) {
            $output .= '<pre>' . print_r($product->debug(), true) . '</pre>';
        }
//        $output = $productCollection->getSelect()->__toString(); //echo ra câu lệnh sql
        $this->getResponse()->setBody($output);
    }
}
