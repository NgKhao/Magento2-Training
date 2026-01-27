<?php
/**
 * Copyright © Magenest. All rights reserved.
 *
 * Blog Repository Interface
 *
 * Repository là layer trung gian giữa business logic và data persistence.
 *
 * Lợi ích:
 * - Tách biệt business logic khỏi database logic
 * - Dễ dàng thay đổi data source (MySQL, MongoDB, API, etc)
 * - Dễ test (mock repository)
 * - API standardization
 *
 * CRUD operations:
 * - save() - Create/Update
 * - getById() - Read by ID
 * - getList() - Read list with filter/sort/pagination
 * - delete() - Delete
 * - deleteById() - Delete by ID
 * */
namespace Magenest\Movie\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magenest\Movie\Api\Data\BlogInterface;
use Magenest\Movie\Api\Data\BlogSearchResultsInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Blog Repository Interface
 *
 * @api
 */
interface   BlogRepositoryInterface
{
    /**
     * Save blog
     *
     * @param \Magenest\Movie\Api\Data\BlogInterface $blog
     * @param int|null $blogId Optional blog ID for PUT request (from URL parameter)
     * @return \Magenest\Movie\Api\Data\BlogInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     **
     * POST /rest/V1/magenest/blogs
     *   → save($blog, null) → Create new blog
     *
     * PUT /rest/V1/magenest/blogs/4
     *   → save($blog, 4) → Update blog với ID = 4
     *   → Magento auto inject blogId từ URL vào parameter thứ 2
     *
     * Flow:
     * - Nếu $blogId != null → Force set ID vào $blog object
     * - Nếu $blog->getId() có giá trị → Update
     * - Nếu $blog->getId() null → Create new
     */
    public function save(BlogInterface $blog, $blogId = null);

    /**
     * Get blog by ID
     *
     * @param int $blogId
     * @return \Magenest\Movie\Api\Data\BlogInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * - Load blog theo ID
     * - Throw NoSuchEntityException nếu không tìm thấy
     * - HTTP Status: 404 Not Found
     */
    public function getById($blogId);

    /**
     * Get blog list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     * @return \Magenest\Movie\Api\Data\BlogSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     *
     * Usage:
     * 1. Get all: GET /rest/V1/magenest/blogs
     * 2. With filter: GET /rest/V1/magenest/blogs?searchCriteria[filter_groups][0][filters][0][field]=status&...
     *
     * Example with filter:
     * GET /rest/V1/magenest/blogs?
     *   searchCriteria[filter_groups][0][filters][0][field]=status
     *   &searchCriteria[filter_groups][0][filters][0][value]=1
     *   &searchCriteria[pageSize]=10
     *   &searchCriteria[currentPage]=1
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null);

    /**
     * Delete blog
     *
     * @param \Magenest\Movie\Api\Data\BlogInterface $blog
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * - Xóa blog object
     * - Return true nếu thành công
     * - Throw exception nếu có lỗi
     */
    public function delete(BlogInterface $blog);

    /**
     * Delete blog by ID
     *
     * @param int $blogId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * Thầy giải thích:
     * - Xóa blog theo ID
     * - Load blog trước → delete
     * - Throw NoSuchEntityException nếu không tìm thấy
     */
    public function deleteById($blogId);
}

