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
     * Trong save blog.
     * còn trong save() check nếu có id thì là update và dùng addData để update những trường cần thiết,
     * cuối cùng cần reload lại để lấy các trường tự động như created_at, updated_at
 */
    public function save(BlogInterface $blog, $blogId = null)
    {
        try {
            // Validate ID conflict giữa URL và body
            if ($blogId !== null && $blog->getId() && $blogId != $blog->getId()) {
                throw new CouldNotSaveException(
                    __('Blog ID in URL (%1) does not match ID in request body (%2)',
                        $blogId,
                        $blog->getId()
                    )
                );
            }

            // Xác định ID để save
            $id = $blogId ?? $blog->getId();

            // Nếu có ID → UPDATE mode
            if ($id) {
                // Bước 1: Load existing blog từ database
                $existingBlog = $this->blogFactory->create();
                $this->blogResource->load($existingBlog, $id);

                // Bước 2: Validate blog tồn tại
                if (!$existingBlog->getId()) {
                    throw new NoSuchEntityException(
                        __('Blog with id "%1" does not exist.', $id)
                    );
                }

                // Bước 3: Merge data từ request vào existing blog
                // addData() sẽ chỉ update fields có trong request
                // Các fields không có trong request được giữ nguyên (created_at, etc)
                $existingBlog->addData($blog->getData());

                // Save existing blog (đã merge data)
                $this->blogResource->save($existingBlog);

                // Return existing blog (có đầy đủ data từ DB)
                return $existingBlog;
            }

            // Nếu KHÔNG có ID → CREATE mode
            // Save blog mới (INSERT)
            $this->blogResource->save($blog);
            // QUAN TRỌNG: Reload blog từ DB để lấy created_at và updated_at
            // Object $blog chưa có giá trị mới → phải reload
            $this->blogResource->load($blog, $blog->getId());

            return $blog;

        } catch (NoSuchEntityException $e) {
            throw $e;
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the blog: %1', $exception->getMessage()),
                $exception
            );
        }
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

