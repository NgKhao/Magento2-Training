<?php

namespace Magenest\UiKnockout\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magenest\UiKnockout\Model\ResourceModel\Banner\CollectionFactory;
use Magento\Framework\UrlInterface;

class BannerDisplay implements ArgumentInterface
{
    protected $collectionFactory;
    protected $storeManager;

    public function __construct(
        CollectionFactory                          $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
    }

    public function getActiveBanners()
    {
        // Lấy tất cả banner đang bật (status = 1)
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('status', 1);
        return $collection->getItems();
    }

    public function getImageUrl($imagePath)
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl . 'banner/images/' . $imagePath;
    }
}
