<?php

namespace Magenest\Movie\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;

class CustomerSave
{
    private $request;
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    public function beforeSave(
        CustomerRepositoryInterface $subject,
        CustomerInterface $customer
    ) {
        $customerPostData = $this->request->getPostValue('customer');

        if (isset($customerPostData['avatar'])
            && is_array($customerPostData['avatar'])
            && !empty($customerPostData['avatar'][0]['file'])
        ) {
            $avatarPath = $customerPostData['avatar'][0]['file'];
            $customer->setCustomAttribute('avatar', $avatarPath);
        }

        return [$customer];
    }
}
