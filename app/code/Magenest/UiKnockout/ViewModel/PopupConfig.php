<?php
/**
 * ViewModel cho Promo Popup
 * Nhiệm vụ: Lấy cấu hình từ System Config và chuyển thành JSON cho JavaScript
 */
namespace Magenest\UiKnockout\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class PopupConfig
 *
 * Note: Luôn implement ArgumentInterface để file này dùng được trong Layout XML
 */
class PopupConfig implements ArgumentInterface
{
    /**
     * System config path constants
     */
    const XML_PATH_ENABLE = 'promo_popup/general/enable';
    const XML_PATH_MESSAGE = 'promo_popup/general/promo_message';
    const XML_PATH_TARGET_GROUPS = 'promo_popup/general/target_groups';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig Đọc system configuration
     * @param SerializerInterface $serializer Chuyển array PHP sang JSON an toàn
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SerializerInterface $serializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
    }

    /**
     * Lấy cấu hình dưới dạng JSON
     *
     * @return string JSON string
     */
    public function getJsonConfig()
    {
        // Lấy dữ liệu từ System Config
        $isEnabled = $this->scopeConfig->getValue(
            self::XML_PATH_ENABLE,
            ScopeInterface::SCOPE_STORE
        );

        $message = $this->scopeConfig->getValue(
            self::XML_PATH_MESSAGE,
            ScopeInterface::SCOPE_STORE
        );

        $groups = $this->scopeConfig->getValue(
            self::XML_PATH_TARGET_GROUPS,
            ScopeInterface::SCOPE_STORE
        );

        // Chuẩn bị config array
        $config = [
            'is_enabled' => (bool)$isEnabled,
            'message' => $message ?: '',
            'target_groups' => $groups ? explode(',', $groups) : []
        ];

        // Chuyển thành JSON (dùng Serializer thay vì json_encode để tránh lỗi encoding)
        return $this->serializer->serialize($config);
    }
}
