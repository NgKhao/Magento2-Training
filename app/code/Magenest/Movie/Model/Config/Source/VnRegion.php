<?php
/**
 *
 * Source Model cung cấp options cho attribute vn_region
 * Miền Bắc, Miền Trung, Miền Nam
 */
namespace Magenest\Movie\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class VnRegion
 *
 * LOGIC CHÍNH:
 * - Cung cấp danh sách options (value => label) cho dropdown vn_region
 * - Value: 1, 2, 3 (lưu vào database)
 * - Label: Miền Bắc, Miền Trung, Miền Nam (hiển thị cho user)
 *
 * Extends AbstractSource để kế thừa:
 * - getOptionText(): lấy label từ value
 * - toOptionArray(): format options cho UI Component
 */
class VnRegion extends AbstractSource
{
    const REGION_NORTH = 1; // Miền Bắc
    const REGION_CENTRAL = 2; // Miền Trung
    const REGION_SOUTH = 3; // Miền Nam

    /**
     * Get all options
     *
     * FUNCTION NÀY BẮT BUỘC (required by AbstractSource)
     *
     * Return format:
     * [
     *     ['value' => 1, 'label' => 'Miền Bắc'],
     *     ['value' => 2, 'label' => 'Miền Trung'],
     *     ['value' => 3, 'label' => 'Miền Nam'],
     * ]
     *
     * WORKFLOW:
     * 1. Check cache: nếu đã load rồi thì return luôn
     * 2. Chưa load: tạo array options
     * 3. Cache lại vào $this->_options
     * 4. Return options
     *
     * @return array
     */
    public function getAllOptions()
    {
        // Check cache: tránh tạo lại options nhiều lần (tối ưu performance)
        if ($this->_options === null) {
            /**
             * Tạo array options
             *
             * Mỗi element có 2 key:
             * - value: giá trị lưu vào DB (integer)
             * - label: text hiển thị cho user (string)
             *
             * __('text') là function translate (đa ngôn ngữ)
             */
            $this->_options = [
                [
                    'value' => self::REGION_NORTH,
                    'label' => __('Miền Bắc') // Miền Bắc
                ],
                [
                    'value' => self::REGION_CENTRAL,
                    'label' => __('Miền Trung') // Miền Trung
                ],
                [
                    'value' => self::REGION_SOUTH,
                    'label' => __('Miền Nam') // Miền Nam
                ]
            ];
        }

        return $this->_options;
    }

    /**
     * Get options for toOptionArray format
     *
     * Hàm này kế thừa từ AbstractSource, tự động gọi getAllOptions()
     * Format: giống getAllOptions() nhưng có thể add thêm 'default' option
     *
     * Usage: dùng trong UI Component (form_element select)
     *
     * @return array
     */
    // public function toOptionArray() - đã có sẵn trong AbstractSource

    /**
     * Get option text by value
     *
     * Hàm này kế thừa từ AbstractSource
     *
     * Usage: Khi có value (1,2,3) cần lấy label tương ứng
     * Example:
     *   $source->getOptionText(1) => 'North Region'
     *   $source->getOptionText(2) => 'Central Region'
     *
     * @param int|string $value
     * @return string|false
     */
    // public function getOptionText($value) - đã có sẵn trong AbstractSource
}

