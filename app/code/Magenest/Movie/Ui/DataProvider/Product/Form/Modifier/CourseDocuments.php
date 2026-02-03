<?php
namespace Magenest\Movie\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form;
use Magenest\Movie\Model\Config\Source\DocumentType;

class CourseDocuments extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var DocumentType
     */
    protected $documentType;

    /**
     * Constructor
     */
    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        Json $jsonSerializer,
        ArrayManager $arrayManager,
        UrlInterface $urlBuilder,
        DocumentType $documentType
    ) {
        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->jsonSerializer = $jsonSerializer;
        $this->arrayManager = $arrayManager;
        $this->urlBuilder = $urlBuilder;
        $this->documentType = $documentType;
    }

    /**
     * ═══════════════════════════════════════════════════════════════════════
     * MODIFY DATA - Load course_documents từ EAV attribute
     * ═══════════════════════════════════════════════════════════════════════
     */
    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();
        $productId = $product->getId();

        if (!$productId) {
            return $data;
        }

        // Lấy data từ EAV attribute
        $documentsData = $product->getData('course_documents');

        // Parse JSON nếu là string
        if (is_string($documentsData) && !empty($documentsData)) {
            try {
                $documentsData = $this->jsonSerializer->unserialize($documentsData);
            } catch (\Exception $e) {
                $documentsData = [];
            }
        }

        // Format data cho dynamicRows
        $formattedDocuments = $this->formatDocumentsForForm($documentsData ?: []);

        // ⭐ QUAN TRỌNG: Set data vào path riêng cho course_documents
        // Tương tự cách Downloadable dùng: $data[$id]['downloadable']['sample']
        $data[$productId]['course_documents_data']['documents'] = $formattedDocuments;

        return $data;
    }

    /**
     * Format documents data cho form display
     */
    protected function formatDocumentsForForm(array $documents)
    {
        $mediaUrl = $this->storeManager
            ->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        $formatted = [];
        foreach ($documents as $doc) {
            $item = [
                'title' => $doc['title'] ?? '',
                'type' => $doc['type'] ?? 'link',
                'link_url' => $doc['link_url'] ?? '',
                'sort_order' => $doc['sort_order'] ?? 0,
            ];

            // Format file cho fileUploader component
            if (!empty($doc['file'])) {
                if (is_array($doc['file']) && isset($doc['file'][0]['name'])) {
                    $item['file'] = $doc['file'];
                } elseif (is_string($doc['file'])) {
                    $item['file'] = [
                        [
                            'name' => $doc['file'],
                            'file' => $doc['file'],
                            'url' => $mediaUrl . 'course/documents/' . $doc['file'],
                            'status' => 'old'
                        ]
                    ];
                }
            } else {
                $item['file'] = [];
            }

            $formatted[] = $item;
        }

        return $formatted;
    }

    /**
     * ═══════════════════════════════════════════════════════════════════════
     * MODIFY META - Tạo cấu trúc UI động trong PHP
     * ═══════════════════════════════════════════════════════════════════════
     *
     * Đây là cách CHUẨN của Magento Core
     */
    public function modifyMeta(array $meta)
    {
        // Tạo fieldset Course Documents
        $meta = $this->arrayManager->set(
            'course_documents_fieldset',
            $meta,
            $this->getCourseDocumentsFieldset()
        );

        return $meta;
    }

    /**
     * Tạo fieldset chứa DynamicRows
     */
    protected function getCourseDocumentsFieldset()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Fieldset::NAME,
                        'label' => __('Course Documents'),
                        'collapsible' => true,
                        'opened' => false,
                        'sortOrder' => 900,
                        // ⭐ Bỏ dataScope hoặc để rỗng để không lồng thêm path
                        'dataScope' => '',
                    ],
                ],
            ],
            'children' => [
                'course_documents' => $this->getDynamicRows(),
            ],
        ];
    }

    /**
     * Tạo cấu hình DynamicRows
     * Tham khảo: Samples::getDynamicRows()
     */
    protected function getDynamicRows()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => DynamicRows::NAME,
                        'label' => __('Documents'),
                        'renderDefaultRecord' => false,
                        'addButtonLabel' => __('Add Document'),
                        'itemTemplate' => 'record',
                        // ⭐ dataScope trỏ đến nơi data được set trong modifyData
                        // modifyData set: $data[$id]['course_documents_data']['documents']
                        // Nên dynamicRows cần dataScope = 'course_documents_data'
                        // và record sẽ lấy từ key 'documents'
                        'dataScope' => 'course_documents_data',
                        'deleteProperty' => 'is_delete',
                        'deleteValue' => '1',
                        'dndConfig' => [
                            'enabled' => true,
                        ],
                    ],
                ],
            ],
            'children' => [
                'record' => $this->getRecord(),
            ],
        ];
    }

    /**
     * Tạo cấu hình cho mỗi record (row)
     */
    protected function getRecord()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Container::NAME,
                        'component' => 'Magento_Ui/js/dynamic-rows/record',
                        'isTemplate' => true,
                        'is_collection' => true,
                        'dataScope' => '',
                    ],
                ],
            ],
            'children' => [
                'title' => $this->getTitleField(),
                'type' => $this->getTypeField(),
                'link_url' => $this->getLinkUrlField(),
                'file' => $this->getFileField(),
                'sort_order' => $this->getSortOrderField(),
                'action_delete' => $this->getActionDelete(),
            ],
        ];
    }

    /**
     * Title field
     */
    protected function getTitleField()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'formElement' => Form\Element\Input::NAME,
                        'dataType' => Form\Element\DataType\Text::NAME,
                        'label' => __('Title'),
                        'dataScope' => 'title',
                        'sortOrder' => 10,
                        'validation' => [
                            'required-entry' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Type field với custom JS component để toggle link_url/file
     */
    protected function getTypeField()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'formElement' => Form\Element\Select::NAME,
                        'dataType' => Form\Element\DataType\Text::NAME,
                        'label' => __('Type'),
                        'dataScope' => 'type',
                        'sortOrder' => 20,
                        // ⭐ Custom JS component để toggle visibility
                        'component' => 'Magenest_Movie/js/components/document-type-handler',
                        'options' => $this->documentType->toOptionArray(),
                        'typeUrl' => 'link_url',
                        'typeFile' => 'file',
                        'validation' => [
                            'required-entry' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Link URL field
     */
    protected function getLinkUrlField()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'formElement' => Form\Element\Input::NAME,
                        'dataType' => Form\Element\DataType\Text::NAME,
                        'label' => __('URL'),
                        'dataScope' => 'link_url',
                        'sortOrder' => 30,
                        'validation' => [
                            'validate-url' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * File uploader field
     * Tham khảo: Samples::getSampleColumn() - $sampleUploader
     */
    protected function getFileField()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'fileUploader',
                        'formElement' => 'fileUploader',
                        'label' => __('File'),
                        'dataScope' => 'file',
                        'sortOrder' => 40,
                        // ⭐ QUAN TRỌNG: fileInputName phải match với Controller
                        'fileInputName' => 'document_file',
                        'uploaderConfig' => [
                            'url' => $this->urlBuilder->getUrl(
                                'movie/document/upload',
                                ['_secure' => true]
                            ),
                        ],
                        'allowedExtensions' => 'pdf doc docx xls xlsx ppt pptx txt zip rar csv',
                    ],
                ],
            ],
        ];
    }

    /**
     * Sort order field (hidden)
     */
    protected function getSortOrderField()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'formElement' => Form\Element\Input::NAME,
                        'dataType' => Form\Element\DataType\Number::NAME,
                        'dataScope' => 'sort_order',
                        'sortOrder' => 50,
                        'visible' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Action delete button
     */
    protected function getActionDelete()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'actionDelete',
                        'label' => null,
                        'sortOrder' => 100,
                        'fit' => true,
                    ],
                ],
            ],
        ];
    }
}
