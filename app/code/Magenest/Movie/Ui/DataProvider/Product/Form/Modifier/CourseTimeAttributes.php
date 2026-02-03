<?php
declare(strict_types=1);

namespace Magenest\Movie\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager; // ArrayManager: helper để thao tác với mảng đa chiều

/**
 * Modifier để customize course_start_time và course_end_time attributes
 * Thêm custom JS component với beforeShowDay validation (chỉ cho chọn ngày 8-12)
 */
class CourseTimeAttributes extends AbstractModifier
{
    private ArrayManager $arrayManager;

    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    /**
     * @inheritdoc
     * thay đổi giá trị mặc định / dữ liệu hiển thị trong form
     */
    public function modifyData(array $data): array
    {
        return $data;
    }

    /**
     * @inheritdoc
     * thay đổi cấu trúc / config của form (thêm component, option, validation, label, v.v.)
     */
    public function modifyMeta(array $meta): array
    {
        $meta = $this->customizeCourseStartTime($meta);
        $meta = $this->customizeCourseEndTime($meta);

        return $meta;
    }

    /**
     * Customize course_start_time attribute
     */
    private function customizeCourseStartTime(array $meta): array
    {
//        tìm đường dẫn đến node có key course_start_time trong phần children
//        Trả về đường dẫn dạng string ví dụ: 'product-details/children/container_course_start_time/children/course_start_time'
        $attributePath = $this->arrayManager->findPath('course_start_time', $meta, null, 'children');

        if ($attributePath) {
            $meta = $this->arrayManager->merge(
                $attributePath . '/arguments/data/config', //đường dẫn chính xác đến phần config của field (nơi khai báo component, options, dataType, v.v.)
                $meta,
                $this->getCourseDateConfig()
            );
        }

        return $meta;
    }

    /**
     * Customize course_end_time attribute
     */
    private function customizeCourseEndTime(array $meta): array
    {
        $attributePath = $this->arrayManager->findPath('course_end_time', $meta, null, 'children');

        if ($attributePath) {
            $meta = $this->arrayManager->merge(
                $attributePath . self::META_CONFIG_PATH,
                $meta,
                $this->getCourseDateConfig()
            );
        }

        return $meta;
    }

    /**
     * Get common config for course date fields
     */
    private function getCourseDateConfig(): array
    {
        return [
            'component' => 'Magenest_Movie/js/form/element/course-date',
            'options' => [
                'dateFormat' => 'yyyy-MM-dd',
                'timeFormat' => 'HH:mm:ss',
                'showsTime' => true
            ]
        ];
    }
}

