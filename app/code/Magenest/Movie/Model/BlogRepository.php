<?php
/**
 * Copyright © Magenest. All rights reserved.
 *
 * Blog Repository Implementation
 *
 * =============================================================================
 * THẦY GIẢI THÍCH: Repository Implementation
 * =============================================================================
 *
 * Class này implement BlogRepositoryInterface và thực hiện CRUD operations.
 *
 * Dependencies:
 * - BlogFactory: Tạo blog instance
 * - BlogResource: Save/Load/Delete blog
 * - CollectionFactory: Load blog list
 * - SearchResultsFactory: Tạo search result object
 * - CollectionProcessor: Xử lý filter/sort/pagination
 *
 * =============================================================================
 */
namespace Magenest\Movie\Model;

use Magenest\Movie\Api\BlogRepositoryInterface;
use Magenest\Movie\Api\Data\BlogInterface;
use Magenest\Movie\Api\Data\BlogSearchResultsInterface;
use Magenest\Movie\Api\Data\BlogSearchResultsInterfaceFactory;
use Magenest\Movie\Model\BlogFactory;
use Magenest\Movie\Model\ResourceModel\Blog as BlogResource;
use Magenest\Movie\Model\ResourceModel\Blog\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class BlogRepository
 */
class BlogRepository implements BlogRepositoryInterface
{
    /**
     * @var BlogFactory
     */
    protected $blogFactory;

    /**
     * @var BlogResource
     */
    protected $blogResource;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var BlogSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Constructor
     *
     * @param BlogFactory $blogFactory
     * @param BlogResource $blogResource
     * @param CollectionFactory $collectionFactory
     * @param BlogSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        BlogFactory $blogFactory,
        BlogResource $blogResource,
        CollectionFactory $collectionFactory,
        BlogSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->blogFactory = $blogFactory;
        $this->blogResource = $blogResource;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritDoc
     *
     * Examples:
     * POST /rest/V1/magenest/blogs
     *   Body: {"blog": {"title": "New", ...}}
     *   → save($blog, null)
     *   → $blog->getId() = null → CREATE
     *
     * PUT /rest/V1/magenest/blogs/4
     *   Body: {"blog": {"title": "Updated", ...}}
     *   → save($blog, 4)
     *   → Force $blog->setId(4) → UPDATE blog ID 4
     */
    public function save(BlogInterface $blog, $blogId = null)
    {
        try {
            // Bước 1: Nếu có $blogId từ URL (PUT request), force set vào blog
            // PUT /rest/V1/magenest/blogs/4 → $blogId = 4
            if ($blogId !== null) {
                // Load blog từ database
                $this->blogResource->load($blog, $blogId);
            }

            // Bước 2: Save blog to database
            // Plugin BlogPlugin::beforeSave() sẽ tự động chạy để validate URL
            // ResourceModel sẽ tự động:
            // - INSERT nếu ID null hoặc ID không tồn tại
            // - UPDATE nếu ID tồn tại
            $this->blogResource->save($blog);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the blog: %1', $exception->getMessage()),
                $exception
            );
        }

        return $blog;
    }

    /**
     * @inheritDoc*
     * Flow getById:
     * 1. Tạo empty blog object
     * 2. Load blog từ database theo ID
     * 3. Kiểm tra blog có tồn tại không (check ID)
     * 4. Nếu tồn tại → return blog
     * 5. Nếu không → throw NoSuchEntityException (404)
     */
    public function getById($blogId)
    {
        // Tạo blog instance
        $blog = $this->blogFactory->create();

        // Load blog từ database
        $this->blogResource->load($blog, $blogId);

        // Kiểm tra blog có tồn tại không
        if (!$blog->getId()) {
            throw new NoSuchEntityException(
                __('Blog with id "%1" does not exist.', $blogId)
            );
        }

        return $blog;
    }

    /**
     * @inheritDoc*
     * Flow getList:
     * 1. Check SearchCriteria có null không
     *    - Nếu null → Tạo empty SearchCriteria (get all)
     *    - Nếu có → Dùng SearchCriteria đã truyền vào
     * 2. Tạo collection (query builder)
     * 3. Apply SearchCriteria (filter, sort, pagination)
     * 4. Execute query → get items
     * 5. Tạo SearchResult object
     * 6. Set items + total_count vào SearchResult
     * 7. Return SearchResult
     *
     * SearchCriteria format:
     * - filter_groups: [[field, value, condition], ...]
     * - sort_orders: [[field, direction], ...]
     * - page_size: 10
     * - current_page: 1
     *
     * Examples:
     * 1. GET /rest/V1/magenest/blogs → $searchCriteria = null → Get ALL
     * 2. GET /rest/V1/magenest/blogs?searchCriteria[...] → Apply filter
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null)
    {
        // Bước 1: Tạo collection
        $collection = $this->collectionFactory->create();

        // Bước 2: Nếu searchCriteria null, tạo empty criteria
        if ($searchCriteria === null) {
            // Tạo empty SearchCriteria
            // Khi gọi GET /rest/V1/magenest/blogs không có params
            // → searchCriteria = null
            // → Tạo empty criteria để get ALL blogs
            $searchCriteria = $this->searchCriteriaBuilder->create();
        } else {
            // Bước 3: Apply search criteria (filter, sort, pagination)
            // CollectionProcessor tự động xử lý:
            // - addFieldToFilter() cho filters
            // - setOrder() cho sorting
            // - setPageSize() và setCurPage() cho pagination
            $this->collectionProcessor->process($searchCriteria, $collection);
        }

        // Bước 4: Tạo search result object
        /** @var BlogSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();

        // Bước 5: Set search criteria (có thể empty hoặc có value)
        $searchResults->setSearchCriteria($searchCriteria);

        // Bước 6: Set items (blog array)
        $searchResults->setItems($collection->getItems());

        // Bước 7: Set total count (tổng số record, dùng cho pagination)
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @inheritDoc
     **
     * Flow delete:
     * 1. Nhận blog object
     * 2. Validate blog có ID không
     * 3. Delete từ database
     * 4. Return true nếu thành công
     * 5. Throw exception nếu có lỗi
     */
    public function delete(BlogInterface $blog)
    {
        try {
            $this->blogResource->delete($blog);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the blog: %1', $exception->getMessage()),
                $exception
            );
        }

        return true;
    }

    /**
     * @inheritDoc
     *
     * Flow deleteById:
     * 1. Load blog theo ID (dùng getById)
     * 2. Nếu không tồn tại → getById throw NoSuchEntityException
     * 3. Nếu tồn tại → delete blog (dùng delete method)
     * 4. Return true
     */
    public function deleteById($blogId)
    {
        // Load blog (throw exception nếu không tồn tại)
        $blog = $this->getById($blogId);

        // Delete blog
        return $this->delete($blog);
    }
}

