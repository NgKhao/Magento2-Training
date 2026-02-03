<?php
/**
 * Copyright © Magenest. All rights reserved.
 *
 * Blog Search Results Interface
 *
 * =============================================================================
 * THẦY GIẢI THÍCH: Search Result Interface
 * =============================================================================
 *
 * SearchResultInterface dùng để trả về kết quả tìm kiếm từ API.
 *
 * Structure:
 * {
 *   "items": [blog1, blog2, blog3],
 *   "search_criteria": {...},
 *   "total_count": 100
 * }
 *
 * Tại sao cần?
 * - Chuẩn hóa format trả về từ API
 * - Hỗ trợ pagination (page, size)
 * - Hỗ trợ filtering, sorting
 * - Magento auto-generate REST API từ interface này
 *
 * =============================================================================
 */
namespace Magenest\Movie\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface BlogSearchResultsInterface
 *
 * @api
 */
interface BlogSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get blog list
     *
     * @return \Magenest\Movie\Api\Data\BlogInterface[]
     */
    public function getItems();

    /**
     * Set blog list
     *
     * @param \Magenest\Movie\Api\Data\BlogInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

