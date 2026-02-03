<?php
namespace Magenest\Movie\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Model\CustomerFactory;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;

class Avatar extends Template implements TabInterface
{
    protected $request;
    protected $customerFactory;
    protected $storeManager;

    public function __construct(
        Context $context,
        RequestInterface $request,
        CustomerFactory $customerFactory,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->request = $request;
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    public function getTabLabel()
    {
        return __('Avatar Information');
    }

    public function getTabTitle()
    {
        return __('Avatar Information');
    }

    public function canShowTab()
    {
        return (bool)$this->getCustomerId();
    }

    public function isHidden()
    {
        return false;
    }

    public function getCustomerId()
    {
        return $this->request->getParam('id');
    }

    public function getCustomer()
    {
        $customerId = $this->getCustomerId();
        if (!$customerId) {
            return null;
        }

        // Load customer model để có thể lấy EAV attributes
        $customer = $this->customerFactory->create()->load($customerId);
        return $customer;
    }

    public function getAvatarUrl()
    {
        $customer = $this->getCustomer();

        if (!$customer || !$customer->getId()) {
            return null;
        }

        // Lấy avatar từ customer model (EAV attribute)
        $avatarPath = $customer->getData('avatar');
        $avatarPath = $avatarPath[0]['file'] ?? null;

        if (!$avatarPath || !is_string($avatarPath)) {
            return null;
        }

        $baseMediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        return rtrim($baseMediaUrl, '/') . '/' . ltrim($avatarPath, '/');
    }

    public function getAvatarPath()
    {
        $customer = $this->getCustomer();
        if (!$customer || !$customer->getId()) {
            return null;
        }
        return $customer->getData('avatar');
    }
}

