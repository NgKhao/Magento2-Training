<?php
/**
 * Plugin để validate và save custom customer attribute ở Frontend
 *
 * GIẢI THÍCH:
 * Magento Frontend không tự động handle custom EAV attributes như Admin.
 * Ta cần dùng Plugin để intercept quá trình save và xử lý custom attribute.
 *
 * Vị trí intercept: CustomerRepositoryInterface::save()
 * Đây là method được gọi khi save customer ở cả Admin và Frontend.
 *
 * LƯU Ý:
 * - Plugin này chạy cho CẢ Admin và Frontend
 * - Nó đảm bảo phone_number được validate và normalize TRƯỚC KHI
 *   Backend Model được gọi
 */
namespace Magenest\Movie\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;

class CustomerTelephonePlugin
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Before plugin cho CustomerRepository::save()
     *
     * LOGIC:
     * 1. Intercept TRƯỚC KHI customer được save
     * 2. Lấy phone_number từ request (nếu có)
     * 3. Validate theo rule (0xxxxxxxxx hoặc +84xxxxxxxxx)
     * 4. Convert +84 -> 0 và normalize
     * 5. Set vào customer object -> Sau đó backend model sẽ được trigger
     *
     *
     * @param CustomerRepositoryInterface $subject
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return array
     * @throws LocalizedException
     */
    public function beforeSave(
        CustomerRepositoryInterface $subject,
        \Magento\Customer\Api\Data\CustomerInterface $customer
    ) {
        // BƯỚC 1: Lấy phone_number từ request (khác nhau giữa Admin và Frontend)
        $phoneNumber = $this->getPhoneNumberFromRequest();

        // Nếu phone_number có giá trị từ form, ta xử lý
        if ($phoneNumber !== null && $phoneNumber !== '') {
            // BƯỚC 2: Validate format TRƯỚC KHI set vào customer
            $this->validatePhoneNumber($phoneNumber);

            // BƯỚC 3: Convert +84 -> 0 và loại bỏ ký tự không phải số
            $phoneNumber = $this->normalizePhoneNumber($phoneNumber);

            // BƯỚC 4: Set phone_number vào customer custom attribute
            // Sau đó, backend model Telephone::beforeSave() sẽ được gọi tự động
            $customer->setCustomAttribute('phone_number', $phoneNumber);
        }

        // Return array với customer object để tiếp tục quá trình save
        return [$customer];
    }

    /**
     * Lấy phone_number từ request - handle cả Admin và Frontend
     *
     * GIẢI THÍCH SỰ KHÁC BIỆT:
     *
     * ADMIN (UI Component):
     * - Request structure: customer[phone_number] = "0912345678"
     * - Phải lấy từ: $request->getParam('customer')['phone_number']
     *
     * FRONTEND (Template Form):
     * - Request structure: phone_number = "0912345678"
     * - Lấy từ: $request->getParam('phone_number')
     *
     * @return string|null
     */
    private function getPhoneNumberFromRequest()
    {
        // Thử lấy từ Frontend format trước (flat param)
        $phoneNumber = $this->request->getParam('phone_number');

        // Nếu không có, thử lấy từ Admin format (nested trong customer array)
        if ($phoneNumber === null || $phoneNumber === '') {
            $customerData = $this->request->getParam('customer');
            if (is_array($customerData) && isset($customerData['phone_number'])) {
                $phoneNumber = $customerData['phone_number'];
            }
        }

        return $phoneNumber;
    }

    /**
     * Validate phone number format
     *
     * RULE:
     * - Phải bắt đầu bằng 0 hoặc +84
     * - Tổng cộng 10 chữ số (sau khi loại bỏ +84)
     * - Pattern: 0xxxxxxxxx hoặc +84xxxxxxxxx
     *
     * @param string $phoneNumber
     * @return void
     * @throws LocalizedException
     */
    private function validatePhoneNumber($phoneNumber)
    {
        $phoneNumber = trim($phoneNumber);

        // Pattern validation: phải là 0 + 9 số HOẶC +84 + 9 số
        $pattern = '/^(0\d{9}|\+84\d{9})$/';

        if (!preg_match($pattern, $phoneNumber)) {
            throw new LocalizedException(
                __('Phone number must be in format 0XXXXXXXXX (10 digits) or +84XXXXXXXXX.')
            );
        }
    }

    /**
     * Normalize phone number
     *
     * LOGIC:
     * 1. Nếu bắt đầu bằng +84 -> Chuyển thành 0
     * 2. Loại bỏ tất cả ký tự không phải số (trừ số 0 đầu tiên)
     * 3. Return chuỗi số sạch
     *
     * VD: +84912345678 -> 0912345678
     *
     * @param string $phoneNumber
     * @return string
     */
    private function normalizePhoneNumber($phoneNumber)
    {
        $phoneNumber = trim($phoneNumber);

        // Convert +84 -> 0
        if (str_starts_with($phoneNumber, '+84')) {
            $phoneNumber = '0' . substr($phoneNumber, 3);
        }

        // Loại bỏ tất cả ký tự không phải số
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        return $phoneNumber;
    }
}

