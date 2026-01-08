<?php

namespace Magenest\Movie\Plugin;

use Magento\Customer\Model\Customer;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;

class CustomerSave
{
    private $logger;
    private $request;

    public function __construct(
        LoggerInterface $logger,
        RequestInterface $request
    ) {
        $this->logger = $logger;
        $this->request = $request;
    }

    public function beforeSave(Customer $subject)
    {
        // Lấy data từ POST request
        $data = $this->request->getPostValue();

        $this->logger->info('Full POST data:', ['data' => $data]);

        // imageUploader gửi trong customer['avatar']
        if (isset($data['customer']['avatar']) && is_array($data['customer']['avatar'])) {
            $avatarData = $data['customer']['avatar'];

            $this->logger->info('Avatar POST data:', ['avatar' => $avatarData]);

            // Nếu có file uploaded: [0 => ['file' => 'abc.jpg', ...]]
            if (isset($avatarData[0]['file'])) {
                $fileName = $avatarData[0]['file'];
                $path = 'customers/avatar/' . $fileName;

                $subject->setData('avatar', $path);
                $this->logger->info('Avatar saved:', ['path' => $path]);
            }
        }

        return null;
    }
}
