<?php
namespace Magenest\Movie\Ui\DataProvider\Product\Form\Modifier;

use Magenest\Movie\Model\ResourceModel\CourseDocument\CollectionFactory;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;

class CourseDocuments extends AbstractModifier
{
    /**
     * @var LocatorInterface // locator: lấy thông tin về sản phẩm hiện tại
     */
    protected $locator;
    protected $documentCollectionFactory;

    public function __construct(
        LocatorInterface $locator,
        CollectionFactory $documentCollectionFactory
    ){
        $this->locator = $locator;
        $this->documentCollectionFactory = $documentCollectionFactory;
    }

    /**
     * Modify data - Load course documents từ DB
     * modifyData(): Load dữ liệu từ DB và format theo cấu trúc mà dynamicRows yêu cầu
     * Key course_documents phải trùng với name của <dynamicRows> trong XML
     * Mỗi document là 1 array với các key trùng với name của các <field> con
     */
    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();
        $productId = $product->getId();

        if (!$productId) {
            return $data;
        }

        // Load documents từ DB
        $collection = $this->documentCollectionFactory->create();
        $collection->addFieldToFilter('product_id', $productId);

        $documents = [];
        foreach ($collection as $doc) {
            $documents[] = [
                'id' => $doc->getId(),
                'title' => $doc->getTitle(),
                'type' => $doc->getType(),
                'link_url' => $doc->getLinkUrl() ?? '',
                'file' => $doc->getFileName()
                    ? [[
                        'name' => $doc->getFileName(),
                        'url'  => $doc->getDocumentUrl(),
                    ]]
                    : [],
            ];
        }

        // ⭐ QUAN TRỌNG: Data phải nằm trong path 'product'
        // vì Product Form có dataScope = 'data.product'
        // UI Component sẽ tìm data tại: $data[$productId]['product']['course_documents']
        $data[$productId][self::DATA_SOURCE_DEFAULT]['course_documents'] = $documents;
        return $data;
    }

    /**
     * Modify meta - Không cần modify UI structure
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
