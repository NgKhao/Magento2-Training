<?php

namespace Magenest\Movie\Plugin;

use Magento\Customer\Model\Customer;
use Psr\Log\LoggerInterface;

class CustomerSave
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function beforeSave(Customer $subject)
    {
        // Lấy data trực tiếp từ attribute 'avatar'
        $value = $subject->getData('avatar');

        // Log để debug
        $this->logger->info('Avatar data before save:', ['data' => $value]);

        // Nếu không có data hoặc đã là string (đã xử lý rồi) thì skip
        if (!$value || is_string($value)) {
            return null;
        }

        // imageUploader gửi dạng:
        // [ ['name' => 'ao.jpeg', 'url' => '...', 'file' => 'ao.jpeg'] ]
        if (is_array($value) && isset($value[0]['file'])) {
            $fileName = $value[0]['file'];
            $path = 'customers/avatar/' . $fileName;

            // Set lại đúng chuẩn EAV - chỉ cần setData thôi
            $subject->setData('avatar', $path);
        }

        return null;
    }
}
