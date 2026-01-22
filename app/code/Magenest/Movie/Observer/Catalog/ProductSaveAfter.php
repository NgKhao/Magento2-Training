<?php
namespace Magenest\Movie\Observer\Catalog;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magenest\Movie\Model\CourseDocumentFactory;
use Magenest\Movie\Model\ResourceModel\CourseDocument as CourseDocumentResource;
use Magenest\Movie\Model\ResourceModel\CourseDocument\CollectionFactory;
use Psr\Log\LoggerInterface;

class ProductSaveAfter implements ObserverInterface
{
    protected $documentFactory;
    protected $documentResource;
    protected $documentCollectionFactory;
    protected $logger;


    public function __construct(
        CourseDocumentFactory $documentFactory,
        CourseDocumentResource $documentResource,
        CollectionFactory $documentCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->documentFactory = $documentFactory;
        $this->documentResource = $documentResource;
        $this->documentCollectionFactory = $documentCollectionFactory;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if (!$product || !$product->getId()) {
            return;
        }

        $documents = $product->getData('course_documents')['course_documents'] ?? null;

        if (!is_array($documents) || empty($documents)) {
            return;
        }

        try {
//            lấy những id doc đã trong db
            $existingIds = $this->getExistingDocumentIds($product->getId());
            $processedIds = [];

//            $documents = [
//                0 => [
//                    'title' => 'PDF 1',
//                    'file' => 'a.pdf',
//                    'is_delete' => false
//                ],
//                1 => [
//                    'title' => 'PDF 2',
//                    'file' => 'b.pdf',
//                    'is_delete' => true
//                ],
//            ];
            foreach ($documents as $position => $documentData) {
                if (empty($documentData) || isset($documentData['is_delete']) && $documentData['is_delete']) {
                    continue;
                }

                $fileValue = '';
                if (isset($documentData['file'])) {
                    if (is_array($documentData['file']) && !empty($documentData['file'])) {
                        $fileData = reset($documentData['file']); // lấy phần tử đầu tiên của mảng và reset con trỏ về đầu mảng

                        $documentData['file'] = $fileData['name'] ?? $fileData['file'] ?? '';
                    } else {
                        $fileValue = (string)$documentData['file'];
                    }
                }

                $documentId = $documentData['id'] ?? null;

                /** @var \Magenest\Movie\Model\CourseDocument $document */
                if ($documentId && in_array($documentId, $existingIds)) {
                    $document = $this->documentFactory->create();
                    $document->load($documentId);
                } else {
                    $document = $this->documentFactory->create();
                }

//                set vào model và save
                $document->setData([
                    'product_id' => $product->getId(),
                    'title' => $documentData['title'] ?? '',
                    'type' => $documentData['type'] ?? 'link',
                    'link_url' => $documentData['link_url'] ?? '',
                    'file_name' => $documentData['file'] ?? '',
                    'file_path' => $documentData['file'] ?? '',
                ]);
               $document->save();
               $processedIds[] = $document->getId();

            }

//            array_diff: return array có ptu ở $existingIds nhưng không có trongg $processedIds
            $deletedIds = array_diff($existingIds, $processedIds);
            if (!empty($deletedIds)) {
                $this->deleteDocuments($deletedIds);
            }

        }catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * @param $productId
     * @return array
     * lấy id doc theo id product
     */
    protected function getExistingDocumentIds($productId)
    {
        $collection = $this->documentCollectionFactory->create();
        $collection->addFieldToFilter('product_id', $productId);

        return $collection->getAllIds();
    }

    /**
     * @param array $ids
     * @return void
     */
    protected function deleteDocuments(array $ids)
    {
        $collection = $this->documentCollectionFactory->create();
        $collection->addFieldToFilter('id', ['in' => $ids]);

        foreach ($collection as $document) {
            try {
//                cũng có thể khởi tạo documentFactory rồi delete
                $this->documentResource->delete($document);
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
    }
}
