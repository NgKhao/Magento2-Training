<?php
/**
 * Plugin: Thêm Vietnam Region field vào Checkout Form
 *
 * GIẢI THÍCH CHỨC NĂNG:
 * ---------------------
 * Plugin này can thiệp vào quá trình xây dựng jsLayout của checkout form
 * để thêm field "vn_region" (Miền Việt Nam) vào các form:
 * - Shipping Address Form (Địa chỉ giao hàng)
 * - Billing Address Form (Địa chỉ thanh toán)
 *
 * CÁCH HOẠT ĐỘNG:
 * ---------------
 * 1. LayoutProcessor build jsLayout từ XML + EAV attributes
 * 2. Plugin intercept AFTER quá trình đó (afterProcess)
 * 3. Inject config cho field vn_region vào đúng vị trí trong jsLayout tree
 *
 * TARGET CLASS:
 * ------------
 * Magento\Checkout\Block\Checkout\LayoutProcessor
 * Method: process() - Xây dựng jsLayout cho checkout page
 */

namespace Magenest\Movie\Plugin\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Magenest\Movie\Model\Config\Source\VnRegion;

class LayoutProcessorPlugin
{
    /**
     * Source Model cho Vietnam Region options
     *
     * @var VnRegion
     */
    protected $vnRegionSource;

    /**
     * Constructor - Inject dependencies
     *
     * GIẢI THÍCH:
     * ----------
     * Magento DI sẽ tự động inject VnRegion source model vào plugin
     * Plugin sử dụng source model để lấy options thay vì hardcode
     *
     * @param VnRegion $vnRegionSource - Source model cung cấp options
     */
    public function __construct(
        VnRegion $vnRegionSource
    ) {
        $this->vnRegionSource = $vnRegionSource;
    }

    /**
     * Plugin: After process - Thêm vn_region field config
     *
     * LOGIC:
     * ------
     * 1. Nhận jsLayout đã được build bởi core
     * 2. Navigate đến đúng node chứa address fields:
     *    - shippingAddress.shippingAddress.children (cho shipping form)
     *    - billing forms (nếu có)
     * 3. Inject config cho vn_region field (component, template, sortOrder, validation...)
     * 4. Return jsLayout đã được modify
     *
     * @param LayoutProcessor $subject - Instance của LayoutProcessor
     * @param array $jsLayout - jsLayout đã được build bởi core
     * @return array - jsLayout sau khi thêm vn_region config
     */
    public function afterProcess(LayoutProcessor $subject, array $jsLayout)
    {
        /**
         * BƯỚC 1: Thêm vn_region vào Shipping Address Form
         * -------------------------------------------------
         * Path: checkout > steps > shipping-step > shippingAddress > shippingAddress > children
         */
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'])) {

            $shippingFields = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];

            /**
             * Config cho vn_region field trong shipping form
             *
             * CÁC THUỘC TÍNH QUAN TRỌNG:
             * - component: UI component type (select dropdown)
             * - config: Template và scope cho field
             * - dataScope: Path trong data model (shippingAddress.custom_attributes.vn_region)
             * - label: Nhãn hiển thị
             * - provider: Data provider (checkoutProvider)
             * - sortOrder: Thứ tự hiển thị (sau postcode)
             * - validation: Rules validate
             * - options: Lấy từ Source Model (ĐÚNG CHUẨN MAGENTO 2)
             * - filterBy: null (không filter theo field khác như region_id filter theo country_id)
             * - customEntry: null (không cho nhập custom value)
             * - visible: true (luôn hiển thị)
             *
             * LƯU Ý: Gọi getAllOptions() từ source model để lấy options động
             */
            $shippingFields['vn_region'] = [
                'component' => 'Magento_Ui/js/form/element/select',
                'config' => [
                    'customScope' => 'shippingAddress.custom_attributes',
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/select'
                ],
                'dataScope' => 'shippingAddress.custom_attributes.vn_region',
                'label' => __('Vietnam Region'),
                'provider' => 'checkoutProvider',
                'sortOrder' => 115, // Sau postcode (114)
                'options' => $this->vnRegionSource->getAllOptions(), // LẤY TỪ SOURCE MODEL
                'filterBy' => null,
                'customEntry' => null,
                'visible' => true
            ];
        }

        /**
         * BƯỚC 2: Thêm vn_region vào Billing Address Forms
         * -------------------------------------------------
         * Billing forms có nhiều nơi:
         * 1. billing-step > payment > afterMethods > billing-address-form (billing form chính)
         * 2. Mỗi payment method có thể có billing form riêng
         *
         * LOGIC: Duyệt qua tất cả payment methods và thêm vn_region vào billing form của từng method
         */
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['payments-list']['children'])) {

            $paymentList = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['payments-list']['children'];

            /**
             * Duyệt qua từng payment method
             */
            foreach ($paymentList as $paymentCode => &$paymentComponent) {
                /**
                 * Kiểm tra xem payment method có billing form không
                 * Path: payment-method > children > form-fields (billing address fields)
                 */
                if (isset($paymentComponent['children']['form-fields']['children'])) {
                    $billingFields = &$paymentComponent['children']['form-fields']['children'];

                    /**
                     * Thêm vn_region vào billing form của payment method này
                     *
                     * LƯU Ý:
                     * - dataScope khác với shipping: billingAddress{$paymentCode}.custom_attributes.vn_region
                     * - Mỗi payment method có data scope riêng
                     * - options: Lấy từ Source Model (ĐÚNG CHUẨN)
                     */
                    $billingFields['vn_region'] = [
                        'component' => 'Magento_Ui/js/form/element/select',
                        'config' => [
                            'customScope' => 'billingAddress' . $paymentCode . '.custom_attributes',
                            'template' => 'ui/form/field',
                            'elementTmpl' => 'ui/form/element/select'
                        ],
                        'dataScope' => 'billingAddress' . $paymentCode . '.custom_attributes.vn_region',
                        'label' => __('Vietnam Region'),
                        'provider' => 'checkoutProvider',
                        'sortOrder' => 115,
                        'options' => $this->vnRegionSource->getAllOptions(), // LẤY TỪ SOURCE MODEL
                        'filterBy' => null,
                        'customEntry' => null,
                        'visible' => true
                    ];
                }
            }
        }

        /**
         * BƯỚC 3: Return jsLayout đã modify
         */
        return $jsLayout;
    }
}

