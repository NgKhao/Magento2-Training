<?php
/**
 * Backend Model để validate và convert số điện thoại
 *
 * GIẢI THÍCH:
 * Backend Model trong EAV được gọi tự động bởi Magento khi:
 * 1. Save entity (customer) - gọi beforeSave()
 * 2. Load entity - gọi afterLoad() (nếu có)
 * 3. Validate entity - gọi validate()
 *
 * Luồng hoạt động:
 * User nhập dữ liệu -> Form submit -> Magento nhận data ->
 * Gọi beforeSave() -> Validate & Convert -> Lưu vào DB
 *
 * Yêu cầu bài toán:
 * - Độ dài không quá 10 chữ số
 * - Bắt đầu là 0 hoặc +84
 * - Nếu là +84 tự động chuyển thành 0
 */
namespace Magenest\Movie\Model\Customer\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\Exception\LocalizedException;

class Telephone extends AbstractBackend
{
    /**
     * beforeSave() - Được gọi TRƯỚC KHI lưu entity vào database
     *
     * Đây là nơi quan trọng nhất để:
     * 1. Validate dữ liệu đầu vào
     * 2. Convert/Transform dữ liệu (VD: +84 -> 0)
     * 3. Throw exception nếu dữ liệu không hợp lệ
     *
     * @param \Magento\Framework\DataObject $object - Customer entity object
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave($object)
    {
        // BƯỚC 1: Lấy attribute code (telephone) và giá trị của nó
        $attributeCode = $this->getAttribute()->getAttributeCode();

        // Lấy giá trị telephone từ customer object
        // VD: '0912345678' hoặc '+84912345678'
        $value = $object->getData($attributeCode);

        // BƯỚC 2: Nếu không có giá trị (null, empty string) thì skip validation
        // Vì attribute này không bắt buộc (required => false)
        if (empty($value)) {
            return $this;
        }

        // BƯỚC 3: Trim để loại bỏ khoảng trắng đầu/cuối
        // VD: " 0912345678 " -> "0912345678"
        $value = trim($value);

        // BƯỚC 4: Convert +84 thành 0
        // str_starts_with() - PHP 8 function kiểm tra chuỗi bắt đầu bằng gì
        // VD: '+84912345678' -> true
        if (str_starts_with($value, '+84')) {
            // substr($value, 3) - Lấy chuỗi từ vị trí 3 đến hết
            // VD: '+84912345678' -> '912345678'
            // Thêm '0' vào đầu -> '0912345678'
            $value = '0' . substr($value, 3);
        }

        // BƯỚC 5: Loại bỏ tất cả ký tự không phải số để validate chính xác
        // preg_replace('/[^0-9]/', '', $value)
        // - Pattern: [^0-9] = không phải số 0-9
        // - Replace với empty string
        // VD: '091-234-5678' -> '0912345678'
        $numbersOnly = preg_replace('/[^0-9]/', '', $value);

        // BƯỚC 6: Set giá trị đã được convert vào object
        // Giá trị này sẽ được lưu vào database
        // VD: User nhập '+84912345678' -> Lưu '0912345678'
        $object->setData($attributeCode, $numbersOnly);

        return $this;
    }

    /**
     * validate() - Được gọi khi validate toàn bộ entity
     *
     * Method này là lớp validate thứ 2, được gọi sau beforeSave()
     * Có thể dùng để validate logic phức tạp hơn
     *
     * @param \Magento\Framework\DataObject $object
     * @return bool
     * @throws LocalizedException
     */
    public function validate($object)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attributeCode);

        if (empty($value)) {
            return true;
        }

        $value = trim($value);

        $pattern = '/^(0\d{9}|\+84\d{9})$/';

        if (!preg_match($pattern, $value)) {
            throw new LocalizedException(
                __('Telephone number must be in format 0XXXXXXXXX or +84XXXXXXXXX.')
            );
        }

        return true;
    }
}

