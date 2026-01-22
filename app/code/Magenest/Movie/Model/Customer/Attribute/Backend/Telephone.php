<?php
/**
 * Backend Model để validate và convert số điện thoại
 * - Validate: Tối đa 10 số, bắt đầu bằng 0 hoặc +84
 * - Convert: Tự động chuyển +84 thành 0
 */
namespace Magenest\Movie\Model\Customer\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\Exception\LocalizedException;

class Telephone extends AbstractBackend
{
    /**
     * Phương thức beforeSave sẽ được gọi TRƯỚC KHI lưu customer vào database
     * Đây là nơi ta xử lý:
     * 1. Validate dữ liệu
     * 2. Convert +84 thành 0
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave($object)
    {
        // Lấy giá trị của attribute telephone
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attributeCode);

        // Nếu không có giá trị hoặc giá trị rỗng thì không validate
        if (empty($value)) {
            return $this;
        }

        // Trim để loại bỏ khoảng trắng thừa
        $value = trim($value);

        // BƯỚC 1: Chuyển đổi +84 thành 0
        // Nếu số bắt đầu bằng +84, ta thay thế +84 bằng 0
        if (str_starts_with($value, '+84')) { //check chuỗi $value có bắt đầu bằng +84 không
            // substr($value, 3) lấy phần còn lại sau +84
            // Ví dụ: +84912345678 -> 0912345678
            $value = '0' . substr($value, 3);
        }

        // BƯỚC 2: Loại bỏ tất cả ký tự không phải số để validate
        // Dùng preg_replace để chỉ giữ lại số
        $numbersOnly = preg_replace('/[^0-9]/', '', $value);

        // BƯỚC 3: Validate độ dài không quá 10 số
        if (strlen($numbersOnly) > 10) {
            throw new LocalizedException(
                __('Telephone number must not exceed 10 digits.')
            );
        }

        // BƯỚC 4: Validate phải bắt đầu bằng 0
        // Sau khi convert +84 thành 0, số phải bắt đầu bằng 0
        if (substr($numbersOnly, 0, 1) !== '0') {
            throw new LocalizedException(
                __('Telephone number must start with 0 or +84.')
            );
        }

        // BƯỚC 5: Validate phải là số hợp lệ (10 chữ số)
        if (strlen($numbersOnly) != 10) {
            throw new LocalizedException(
                __('Telephone number must be exactly 10 digits.')
            );
        }

        // BƯỚC 6: Lưu giá trị đã được convert vào object
        // Giá trị này sẽ được lưu vào database
        $object->setData($attributeCode, $numbersOnly);

        return $this;
    }

    /**
     * Phương thức validate được gọi khi validate toàn bộ entity
     * Ta có thể dùng method này để validate thêm nếu cần
     *
     * @param \Magento\Framework\DataObject $object
     * @return bool
     */
    public function validate($object)
    {
        $value = $object->getData($this->getAttribute()->getAttributeCode());

        // Nếu không có giá trị thì valid
        if (empty($value)) {
            return true;
        }

        // Validate format: phải là 10 chữ số và bắt đầu bằng 0
        $pattern = '/^0\d{9}$/';
        if (!preg_match($pattern, $value)) {
            throw new LocalizedException(
                __('Invalid telephone number format.')
            );
        }

        return true;
    }
}

