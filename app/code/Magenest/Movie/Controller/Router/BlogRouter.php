<?php
/**
 *
 * Router là component xử lý URL request và match với Controller phù hợp.
 * Flow hoạt động:
 * 1. User truy cập: http://domain.com/blog1.html
 * 2. Magento check các Router theo thứ tự priority
 * 3. Custom Router của ta check xem "blog1.html" có trong DB không
 * 4. Nếu có → Forward request đến Blog/View controller với ID tương ứng
 * 5. Nếu không → Trả về false, để Router khác xử lý tiếp
 */
namespace Magenest\Movie\Controller\Router;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RouterInterface;
use Magenest\Movie\Model\ResourceModel\Blog\CollectionFactory as BlogCollectionFactory;

/**
 * Class BlogRouter
 *
 */
class BlogRouter implements RouterInterface
{
    /**
     * @var ActionFactory
     * Dùng để tạo Action object (Forward action)
     */
    protected $actionFactory;

    /**
     * @var ResponseInterface
     * Response object để set redirect/forward
     */
    protected $response;

    /**
     * @var BlogCollectionFactory
     * Để query blog từ database theo url_rewrite
     */
    protected $blogCollectionFactory;

    /**
     * Constructor - Dependency Injection
     *
     * @param ActionFactory $actionFactory
     * @param ResponseInterface $response
     * @param BlogCollectionFactory $blogCollectionFactory
     */
    public function __construct(
        ActionFactory $actionFactory,
        ResponseInterface $response,
        BlogCollectionFactory $blogCollectionFactory
    ) {
        $this->actionFactory = $actionFactory;
        $this->response = $response;
        $this->blogCollectionFactory = $blogCollectionFactory;
    }

    /**
     * Match request và return Action nếu URL match với blog url_rewrite
     *
     * @param RequestInterface $request
     * @return ActionInterface|null
     *
     * Bước 1: Lấy path từ URL
     *   - URL: http://domain.com/blog1.html
     *   - Path: /blog1.html
     *
     * Bước 2: Loại bỏ dấu / đầu tiên
     *   - Identifier: blog1.html
     *
     * Bước 3: Query database tìm blog có url_rewrite = "blog1.html"
     *   - SELECT * FROM magenest_blog WHERE url_rewrite = 'blog1.html' AND status = 1
     *
     * Bước 4: Nếu không tìm thấy → return null (Router khác xử lý)
     *
     * Bước 5: Nếu tìm thấy → Forward request đến blog/view/id/{blog_id}
     *   - Set module: Magenest_Movie
     *   - Set controller: blog
     *   - Set action: view
     *   - Set param: id = {blog_id}
     *
     * Bước 6: Return Forward action để Magento execute
     *
     */
    public function match(RequestInterface $request): ?ActionInterface
    {
        // Bước 1: Lấy path từ request
        // Ví dụ: URL = http://domain.com/blog1.html → pathInfo = /blog1.html
        $identifier = trim($request->getPathInfo(), '/');

        // Validate - Nếu path rỗng, không xử lý
        if (empty($identifier)) {
            return null;
        }

        // Query database để tìm blog có url_rewrite match
        /** @var \Magenest\Movie\Model\ResourceModel\Blog\Collection $collection */
        $collection = $this->blogCollectionFactory->create();
        $collection->addFieldToFilter('url_rewrite', $identifier)
                   ->addFieldToFilter('status', 1) // Chỉ lấy blog active
                   ->setPageSize(1); // Chỉ cần 1 kết quả

        // Kiểm tra kết quả
        if ($collection->getSize() == 0) {
            // Không tìm thấy blog → return null để Router khác xử lý
            return null;
        }

        // Lấy blog đầu tiên từ collection

        /** @var \Magenest\Movie\Model\Blog $blog */
        $blog = $collection->getFirstItem();

        // Bước 6: Set parameters cho request
        // Magento sẽ forward đến: movie/blog/view/id/{blog_id}
        $request->setModuleName('movie')        // Module name (từ routes.xml: frontName="movie")
                ->setControllerName('blog')      // Controller folder
                ->setActionName('view')          // Action class
                ->setParam('id', $blog->getId()); // Blog ID parameter

        // Bước 7: Set alias để tracking
        // PathInfo sẽ được set thành route thực tế
        $request->setAlias(
            \Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS,
            $identifier
        );

        // Bước 8: Return Forward action
        // ActionFactory sẽ tạo Forward action để execute request
        return $this->actionFactory->create(Forward::class);
    }
}

