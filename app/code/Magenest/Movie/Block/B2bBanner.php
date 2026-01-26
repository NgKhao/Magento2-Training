<?php
/**
 * Block hiển thị banner cho khách hàng B2B
 *
 * LOGIC CHÍNH:
 * 1. Inject CustomerSession để lấy thông tin customer hiện tại
 * 2. Check customer có đăng nhập không
 * 3. Lấy attribute is_b2b từ customer entity
 * 4. Return true/false để template quyết định hiển thị banner
 */

namespace Magenest\Movie\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Api\CustomerRepositoryInterface;


/**
 * Class B2bBanner
 *
 * Block chứa logic kiểm tra customer B2B và cung cấp data cho template
 */
class B2bBanner extends Template
{
    /**
     * @var CustomerSession
     * Customer Session chứa thông tin customer đang đăng nhập
     * Dùng để:
     * - Check customer có đăng nhập không: isLoggedIn()
     * - Lấy customer object: getCustomer()
     */
    protected $customerSession;
    protected $customerRepository;


    /**
     * Constructor
     *
     * @param Context $context
     * @param CustomerSession $customerSession - Inject customer session
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        parent::__construct($context, $data);
    }

    /**
     * Kiểm tra customer có phải là B2B không
     *
     * LOGIC:
     * 1. Check customer đã đăng nhập chưa
     * 2. Lấy customer object từ session
     * 3. Đọc custom attribute 'is_b2b'
     * 4. Return true nếu is_b2b = 1 (hoặc true)
     *
     * @return bool
     */
    public function isB2bCustomer()
    {
        // Debug log - Setup trước
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/customer_debug.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $logger->info('=== START isB2bCustomer() ===');

        // BƯỚC 1: Check session có được load không
        $logger->info('Session Class: ' . get_class($this->customerSession));
        $logger->info('isLoggedIn(): ' . ($this->customerSession->isLoggedIn() ? 'YES' : 'NO'));

        // BƯỚC 2: Check đăng nhập
        if (!$this->customerSession->isLoggedIn()) {
            $logger->info('Customer NOT logged in - Return false');
            return false;
        }

        // BƯỚC 3: Lấy Customer ID từ session
        $customerId = $this->customerSession->getCustomerId();
        $logger->info('Customer ID from session: ' . ($customerId ?: 'NULL'));

        if (!$customerId) {
            $logger->info('Customer ID is NULL - Return false');
            return false;
        }

        try {
            // BƯỚC 4: Load customer từ repository
            $logger->info('Loading customer from repository...');
            $customer = $this->customerRepository->getById($customerId);

            $logger->info('Customer loaded successfully');
            $logger->info('Customer ID: ' . $customer->getId());
            $logger->info('Email: ' . $customer->getEmail());
            $logger->info('Firstname: ' . $customer->getFirstname());
            $logger->info('Lastname: ' . $customer->getLastname());

            // BƯỚC 5: Lấy ALL custom attributes để debug
            $customAttributes = $customer->getCustomAttributes();
            $logger->info('Total custom attributes: ' . count($customAttributes));

            foreach ($customAttributes as $attribute) {
                $logger->info('Attribute: ' . $attribute->getAttributeCode() . ' = ' . $attribute->getValue());
            }

            // BƯỚC 6: Lấy custom attribute is_b2b
            $isB2bAttribute = $customer->getCustomAttribute('is_b2b');

            if ($isB2bAttribute) {
                $isB2bValue = $isB2bAttribute->getValue();
                $logger->info('is_b2b value: ' . $isB2bValue);
                $logger->info('is_b2b boolean: ' . ((bool)$isB2bValue ? 'TRUE' : 'FALSE'));
                return (bool)$isB2bValue;
            }

            $logger->info('is_b2b attribute NOT FOUND - Return false');
            return false;

        } catch (\Exception $e) {
            // Log lỗi nếu có
            $logger->err('Error loading customer: ' . $e->getMessage());
            $logger->err('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Lấy URL của banner image
     *
     * NOTE: Trong thực tế, có thể:
     * - Lấy từ System Configuration
     * - Lấy từ CMS Block
     * - Hardcode trong template
     *
     * Ở đây em có thể customize theo nhu cầu
     *
     * @return string
     */
    public function getBannerImageUrl()
    {
        // Trả về URL của banner image
        // getViewFileUrl() tự động tìm file trong:
        // app/code/Magenest/Movie/view/frontend/web/images/banner_b2b.svg
        // Magento sẽ publish vào pub/static khi deploy
        return $this->getViewFileUrl('Magenest_Movie::images/banner.webp');
    }

    /**
     * Lấy banner title/alt text
     *
     * @return string
     */
    public function getBannerTitle()
    {
        return __('Special Offer for B2B Customers');
    }

    /**
     * Quyết định có render block không
     *
     * Override method _toHtml() của Template
     * Nếu không phải B2B customer -> return empty string (không render gì)
     *
     * LƯU Ý: Đây là cách tối ưu performance
     * - Không cần render template nếu không cần thiết
     * - Template engine không phải parse file .phtml
     *
     * @return string
     */
    protected function _toHtml()
    {
        // Nếu không phải B2B customer -> return empty (KHÔNG render gì)
        if (!$this->isB2bCustomer()) {
            return ''; // ✅ Return empty string - không hiển thị gì
        }

        // Nếu là B2B -> render template bình thường
        return parent::_toHtml();
    }
}

