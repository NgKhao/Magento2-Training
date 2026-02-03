<?php
/**
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║  COURSE DOCUMENTS PROCESSOR PLUGIN                                         ║
 * ║                                                                           ║
 * ║  Plugin xử lý data course_documents trước khi product được save           ║
 * ║                                                                           ║
 * ║  Vì sao dùng Plugin thay Observer?                                        ║
 * ║  - Plugin can thiệp trước khi save (beforeSave)                           ║
 * ║  - Có thể modify data trực tiếp                                           ║
 * ║  - Phù hợp hơn với EAV workflow                                           ║
 * ║                                                                           ║
 * ║  Data flow:                                                                ║
 * ║  Form Submit → Controller → Product->save() → [Plugin] → DB              ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */
namespace Magenest\Movie\Plugin\Catalog\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class ProductPlugin
{
    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param Json $jsonSerializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        Json $jsonSerializer,
        LoggerInterface $logger
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->logger = $logger;
    }

    /**
     * Before Save - Process course_documents data
     *
     * @param Product $subject
     * @return null
     */
    public function beforeSave(Product $subject)
    {
        // ⭐ Debug: Log tất cả data để tìm đúng path
        $allData = $subject->getData();
        $this->logger->debug('ProductPlugin::beforeSave - All data keys: ' . json_encode(array_keys($allData)));

        // Tìm course_documents_data trong data
        $courseDocumentsData = $subject->getData('course_documents_data');
        $this->logger->debug('ProductPlugin::beforeSave - course_documents_data: ' . json_encode($courseDocumentsData));

        $documents = null;

        // Path 1: course_documents_data.documents (từ dynamicRows)
        if (is_array($courseDocumentsData)) {
            if (isset($courseDocumentsData['documents'])) {
                $documents = $courseDocumentsData['documents'];
                $this->logger->debug('Found documents at course_documents_data.documents');
            } elseif (isset($courseDocumentsData['course_documents'])) {
                // Path có thể là course_documents_data.course_documents
                $documents = $courseDocumentsData['course_documents'];
                $this->logger->debug('Found documents at course_documents_data.course_documents');
            } else {
                // Có thể data nằm trực tiếp trong course_documents_data (array of documents)
                $firstKey = array_key_first($courseDocumentsData);
                if (is_numeric($firstKey)) {
                    $documents = $courseDocumentsData;
                    $this->logger->debug('Found documents directly in course_documents_data');
                }
            }
        }

        // Path 2: Fallback - check course_documents trực tiếp
        if (empty($documents)) {
            $documents = $subject->getData('course_documents');
            if (!empty($documents) && !is_string($documents)) {
                $this->logger->debug('Found documents at course_documents (fallback)');
            }
        }

        // Skip nếu không có data hoặc đã là JSON string
        if (empty($documents) || is_string($documents)) {
            $this->logger->debug('ProductPlugin::beforeSave - No documents to process or already JSON');
            return null;
        }

        $this->logger->debug('ProductPlugin::beforeSave - Processing documents: ' . json_encode($documents));

        // Process và clean data từ form
        $processedDocuments = $this->processDocumentsData($documents);

        // Serialize thành JSON để lưu vào EAV
        try {
            $jsonData = $this->jsonSerializer->serialize($processedDocuments);
            $subject->setData('course_documents', $jsonData);

            $this->logger->debug('ProductPlugin::beforeSave - Saved JSON: ' . $jsonData);
        } catch (\Exception $e) {
            $this->logger->error('Error serializing course documents: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Process documents data từ form
     *
     * Input từ form:
     * [
     *     0 => [
     *         'title' => 'Doc 1',
     *         'type' => 'link',
     *         'link_url' => 'https://...',
     *         'file' => [],
     *         'is_delete' => '0'
     *     ],
     *     1 => [
     *         'title' => 'Doc 2',
     *         'type' => 'file',
     *         'file' => [
     *             0 => ['name' => 'doc.pdf', 'file' => 'doc.pdf', 'url' => '...']
     *         ],
     *         'is_delete' => '1'  // <-- Đánh dấu xóa
     *     ]
     * ]
     *
     * @param array $documents
     * @return array
     */
    protected function processDocumentsData($documents)
    {
        if (!is_array($documents)) {
            return [];
        }

        $processed = [];
        $sortOrder = 0;

        foreach ($documents as $doc) {
            // Skip empty hoặc đánh dấu xóa
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

        // Nếu là string
        if (is_string($fileData)) {
            return $fileData;
        }

        // Nếu là array từ fileUploader
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

