<?php
namespace Magenest\UiKnockout\Model\Banner;

use Magenest\UiKnockout\Model\ResourceModel\Banner\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Store\Model\StoreManagerInterface;

// lấy dữ liệu của 1 banner dựa trên id cho form edit banner trong adminhtml
class DataProvider extends AbstractDataProvider
{
    protected $collection;
    protected $dataPersistor; // Lưu dữ liệu tạm trong session
    protected $loadedData;
    protected $storeManager;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    )
    {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        // Kiểm tra nếu đã load rồi thì trả về luôn (tránh load 2 lần)
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        // Lấy đường dẫn Media base URL để ghép với tên ảnh
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        foreach ($items as $banner) {
            $data = $banner->getData();

            // Form UI Component yêu cầu dữ liệu ảnh phải là một Mảng gồm name, url, size...
            if (isset($data['image'])) {
                $imageName = $data['image'];
                unset($data['image']); // Xóa chuỗi cũ đi

                $data['image'][0]['name'] = $imageName;
                $data['image'][0]['url'] = $mediaUrl . 'banner/images/' . $imageName; // Đường dẫn hiển thị preview
            }

            $this->loadedData[$banner->getId()] = $data; // Lưu dữ liệu đã xử lý vào mảng
        }

        // Logic lấy dữ liệu từ session (trường hợp Save lỗi, form tự điền lại dữ liệu vừa nhập)
        $data = $this->dataPersistor->get('vendor_banner_banner');
        if (!empty($data)) {
            $banner = $this->collection->getNewEmptyItem();
            $banner->setData($data);
            $this->loadedData[$banner->getId()] = $banner->getData();
            $this->dataPersistor->clear('vendor_banner_banner');
        }

        return $this->loadedData;
    }
}
