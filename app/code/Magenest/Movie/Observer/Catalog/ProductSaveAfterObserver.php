<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║  COURSE DOCUMENTS SAVE OBSERVER                                            ║
 * ║                                                                           ║
 * ║  Observer xử lý save course_documents khi product được save               ║
 * ║                                                                           ║
 * ║  Vấn đề với Plugin beforeSave:                                            ║
 * ║  - Product Initialization Helper chỉ lấy data từ $_POST['product']        ║
 * ║  - Data của dynamicRows với dataScope khác không được add vào Product    ║
 * ║                                                                           ║
 * ║  Giải pháp:                                                                ║
 * ║  - Dùng Observer với event 'catalog_product_save_after'                   ║
 * ║  - Lấy data trực tiếp từ Request                                          ║
 * ║  - Save vào EAV attribute                                                  ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */
namespace Magenest\Movie\Observer\Catalog;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Psr\Log\LoggerInterface;

class ProductSaveAfterObserver implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     */
    public function __construct(
        RequestInterface $request,
        Json $jsonSerializer,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->jsonSerializer = $jsonSerializer;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        // ⭐ Debug với error_log (luôn ghi ra file)
        error_log('=== ProductSaveAfterObserver TRIGGERED ===');

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getEvent()->getProduct();

        if (!$product || !$product->getId()) {
            error_log('ProductSaveAfterObserver: No product or product ID');
            return;
        }

        error_log('ProductSaveAfterObserver: Product ID = ' . $product->getId());

        try {
            // ⭐ Lấy data trực tiếp từ Request (không qua Product model)
            $courseDocumentsData = $this->request->getParam('course_documents_data');

            error_log('ProductSaveAfterObserver: course_documents_data = ' . json_encode($courseDocumentsData));
            $this->logger->debug('Observer - course_documents_data from request: ' . json_encode($courseDocumentsData));

            if (empty($courseDocumentsData)) {
                error_log('ProductSaveAfterObserver: No course_documents_data');
                return;
            }

            // Tìm documents trong data
            $documents = null;
            if (is_array($courseDocumentsData)) {
                // Path 1: course_documents_data.documents
                if (isset($courseDocumentsData['documents'])) {
                    $documents = $courseDocumentsData['documents'];
                    error_log('ProductSaveAfterObserver: Found at .documents');
                }
                // Path 2: course_documents_data (array trực tiếp)
                elseif (isset($courseDocumentsData[0]) || is_numeric(array_key_first($courseDocumentsData))) {
                    $documents = $courseDocumentsData;
                    error_log('ProductSaveAfterObserver: Found direct array');
                }
            }

            if (empty($documents) || !is_array($documents)) {
                error_log('ProductSaveAfterObserver: No documents found in data');
                $this->logger->debug('Observer - No documents found');
                return;
            }

            error_log('ProductSaveAfterObserver: Processing ' . count($documents) . ' documents');
            $this->logger->debug('Observer - Processing documents: ' . json_encode($documents));

            // Process documents data
            $processedDocuments = $this->processDocumentsData($documents);

            error_log('ProductSaveAfterObserver: Processed = ' . json_encode($processedDocuments));
            $this->logger->debug('Observer - Processed documents: ' . json_encode($processedDocuments));

            // Serialize thành JSON
            $jsonData = $this->jsonSerializer->serialize($processedDocuments);

            // ⭐ Save trực tiếp vào product resource
            // Dùng setData + save để bypass các logic khác
            $product->setData('course_documents', $jsonData);
            $product->getResource()->saveAttribute($product, 'course_documents');

            error_log('ProductSaveAfterObserver: SAVED course_documents = ' . $jsonData);
            $this->logger->debug('Observer - Saved course_documents: ' . $jsonData);

        } catch (\Exception $e) {
            error_log('ProductSaveAfterObserver ERROR: ' . $e->getMessage());
            $this->logger->error('Observer - Error saving course documents: ' . $e->getMessage());
        }
    }

    /**
     * Process documents data từ form
     *
     * @param array $documents
     * @return array
     */
    protected function processDocumentsData(array $documents)
    {
        $processed = [];
        $sortOrder = 0;

        foreach ($documents as $doc) {
            // Skip empty
            if (empty($doc) || !is_array($doc)) {
                continue;
            }

            // Skip nếu is_delete = 1
            if (!empty($doc['is_delete']) && $doc['is_delete'] == '1') {
                continue;
            }

            // Skip nếu không có title
            if (empty($doc['title'])) {
                continue;
            }

            // Extract file name từ fileUploader data
            $fileName = $this->extractFileName($doc['file'] ?? null);

            $processed[] = [
                'title' => $doc['title'],
                'type' => $doc['type'] ?? 'link',
                'link_url' => $doc['link_url'] ?? '',
                'file' => $fileName,
                'sort_order' => $sortOrder++,
            ];
        }

        return $processed;
    }

    /**
     * Extract file name từ fileUploader data
     *
     * @param mixed $fileData
     * @return string
     */
    protected function extractFileName($fileData)
    {
        if (empty($fileData)) {
            return '';
        }

        if (is_string($fileData)) {
            return $fileData;
        }

        if (is_array($fileData)) {
            $firstFile = reset($fileData);
            if (is_array($firstFile)) {
                return $firstFile['file'] ?? $firstFile['name'] ?? '';
            }
            return (string) $firstFile;
        }

        return '';
    }
}

