<?php
/**
 * Copyright © Magenest. All rights reserved.
 *
 * Blog Search Results Implementation
 */
namespace Magenest\Movie\Model;

use Magento\Framework\Api\SearchResults;
use Magenest\Movie\Api\Data\BlogSearchResultsInterface;

/**
 * Class BlogSearchResults
 *
 * Thầy giải thích:
 * - Extend từ Magento\Framework\Api\SearchResults
 * - SearchResults đã có sẵn implementation cho getItems, setItems, getTotalCount, etc
 * - Chỉ cần implement interface để type hint đúng
 */
class BlogSearchResults extends SearchResults implements BlogSearchResultsInterface
{
    // Không cần code gì thêm
    // SearchResults class đã implement tất cả methods cần thiết
}

