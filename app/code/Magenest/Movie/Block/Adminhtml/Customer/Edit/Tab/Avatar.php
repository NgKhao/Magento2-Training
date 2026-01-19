<?php
namespace Magenest\Movie\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

class Avatar extends Template implements TabInterface
{
    protected $coreRegistry;
    protected $customerRepository;
    protected $storeManager;
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->customerRepository = $customerRepository;
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
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    public function getCustomer()
    {
        $customerId = $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        return $customerId ? $this->customerRepository->getById($customerId) : null;
    }

    public function getAvatarUrl()
    {
        $customer = $this->getCustomer();

        if (!$customer) {
            return null;
        }

        $avatarAttribute = $customer->getCustomAttribute('avatar');
        if (!$avatarAttribute) {
            return null;
        }

        $avatarPath = $avatarAttribute->getValue();
        if (!is_string($avatarPath) || empty($avatarPath)) {
            return null;
        }

        $baseMediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        return rtrim($baseMediaUrl, '/') . '/' . ltrim($avatarPath, '/');

    }

}

