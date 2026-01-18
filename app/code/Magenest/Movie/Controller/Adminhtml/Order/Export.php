<?php
namespace Magenest\Movie\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Export extends Action
{
    protected $fileFactory;
    protected $orderRepository;

    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->fileFactory = $fileFactory;
        $this->orderRepository = $orderRepository;
        parent::__construct($context);
    }

    public function execute()
    {
        $orderID = $this->getRequest()->getParam('order_id');

        if(!$orderID) {
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            return $this->_redirect('sales/order/index');
        }

        try {
            $order = $this->orderRepository->get($orderID);
            $csvContent = $this->generateCsvContent($order);
            $fileName = 'order_' . $order->getIncrementId() . '_items.csv';
            return $this->fileFactory->create(
                $fileName,
                $csvContent,
                DirectoryList::VAR_DIR,
                'text/csv'
            );
        }catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Error exporting order:  %1', $e->getMessage())
            // %1 = placeholder, replaced by $e->getMessage()
            );
            return $this->_redirect('sales/order/view', ['order_id' => $orderID]);
        }
    }

    protected  function generateCsvContent($order)
    {
        $csvData = [
            [
                'ID Order',              // Mã order
                'Purchase Point',        // Store name
                'Purchase Date',         // Ngày đặt hàng
                'Ref Code',              // SKU
                'Product Name',          // Tên sản phẩm
                'Product Qty',           // Số lượng
                'Unit Price',            // Giá đơn vị
                'Grand Total (Base)',    // Tổng tiền order
                'Subtotal'               // Tổng tiền item
            ]
        ];

        $orderId = $order->getIncrementId(); // 000000444
        $purchasePoint = $order->getStoreName();
//        strtotime():
//         Convert datetime string → Unix timestamp
//        VD: "2020-08-25 10:30:00" → 1598347800
//         date('M d, Y', $timestamp):
        $purchaseDate = date('d/M/Y', strtotime($order->getCreatedAt()));
        $grandTotal = $order->getBaseGrandTotal(); // tổng tiền
        foreach ($order->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            $refCode = $item->getSku();
            $productName = $item->getName();
            $qty = $item->getQtyOrdered();
            $unitPrice = number_format($item->getBasePrice(), 0);
            $subtotal = number_format($item->getBaseRowTotal(), 0);
            $csvData[] = [
                $orderId,           // ID Order
                $purchasePoint,     // Purchase Point
                $purchaseDate,      // Purchase Date
                $refCode,           // Ref Code (SKU)
                $productName,       // Product Name
                $qty,               // Quantity
                $unitPrice,         // Unit Price
                $grandTotal,        // Grand Total (same for all items)
                $subtotal           // Subtotal
            ];
        }
        $file = fopen('php://temp', 'w+'); //'w+': Write + Read mode
        foreach ($csvData as $row) {
            fputcsv($file, $row); // write vào csv
        }
        rewind($file); //đưa cusor lên đầu file

//        Đọc toàn bộ content từ cursor đến cuối
//        Return string
        $csvContent = stream_get_contents($file);
        fclose($file);
        return $csvContent;
    }
}
