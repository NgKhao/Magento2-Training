<?php
namespace Magenest\Movie\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Observer gửi email đơn giản khi order có khóa học
 */
class SendCourseEmailObserver implements ObserverInterface
{
    protected $transportBuilder;
    protected $inlineTranslation;
    protected $storeManager;
    protected $logger;

    public function __construct(
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Gửi email khi order được place - CÁCH ĐƠN GIẢN NHẤT
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $observer->getEvent()->getOrder();

            // Tìm product khóa học
            $courseProduct = null;
            foreach ($order->getAllVisibleItems() as $item) {
                $product = $item->getProduct();
                if ($product && $product->getAttributeSetId() == 25) {
                    $courseProduct = $product;
                    break;
                }
            }

            if (!$courseProduct) {
                return; // Không có khóa học
            }

            // Lấy customer name
            $customerName = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
            if (trim($customerName) == '') {
                $billing = $order->getBillingAddress();
                $customerName = $billing->getFirstname() . ' ' . $billing->getLastname();
            }

            // ⭐ Load documents và format thành HTML ĐƠN GIẢN
            $documentsHtml = $this->getDocumentsHtml($courseProduct->getId());

            // Tắt inline translation
            $this->inlineTranslation->suspend();

            // ⭐ Template vars - CHỈ DÙNG SCALAR VALUES, KHÔNG TRUYỀN OBJECT
            $templateVars = [
                'customer_name' => $customerName,
                'course_name' => $courseProduct->getName(),
                'order_id' => $order->getIncrementId(),
                'order_date' => $order->getCreatedAt(),
                'documents_html' => $documentsHtml, // ← HTML string
            ];

            $store = $this->storeManager->getStore($order->getStoreId());

            // Gửi email
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('course_purchase_email')
                ->setTemplateOptions([
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $store->getId()
                ])
                ->setTemplateVars($templateVars)
                ->setFromByScope('general')
                ->addTo($order->getCustomerEmail(), $customerName)
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();

            $this->logger->info('Course email sent to: ' . $order->getCustomerEmail());

        } catch (\Exception $e) {
            $this->logger->error('Send course email error: ' . $e->getMessage());
        }
    }

    /**
     * Format documents thành HTML string
     *
     * @param int $productId
     * @return string
     */
    private function getDocumentsHtml($productId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collectionFactory = $objectManager->get(\Magenest\Movie\Model\ResourceModel\CourseDocument\CollectionFactory::class);

        $collection = $collectionFactory->create();
        $collection->addFieldToFilter('product_id', $productId);

        if ($collection->count() == 0) {
            return '<p style="color: #666;">Không có tài liệu tham khảo cho khóa học này.</p>';
        }

        $html = '<ul style="list-style-type: disc; padding-left: 20px; margin-top: 10px;">';
        foreach ($collection as $doc) {
            $html .= '<li style="margin-bottom: 10px;">';
            $html .= '<strong>' . htmlspecialchars($doc->getTitle()) . '</strong>';

            if ($doc->getType() == 'link' && $doc->getLinkUrl()) {
                $html .= ' - <a href="' . htmlspecialchars($doc->getLinkUrl()) . '" style="color: #1979c3; text-decoration: none;">📄 Xem tài liệu</a>';
            } elseif ($doc->getType() == 'file' && $doc->getFileName()) {
                $html .= ' - <a href="' . htmlspecialchars($doc->getDocumentUrl()) . '" style="color: #1979c3; text-decoration: none;">💾 Tải file</a>';
            }

            $html .= '</li>';
        }
        $html .= '</ul>';

        return $html;
    }
}

