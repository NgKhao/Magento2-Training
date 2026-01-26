<?php
/**
 *
 * Controller là nơi xử lý request và trả về response.
 *
 * Flow:
 * 1. Router forward request đến: movie/blog/view/id/1
 * 2. Magento tìm file: Controller/Blog/View.php
 * 3. Execute method execute() trong View class
 * 4. Load blog theo ID từ parameter
 * 5. Render view với blog data
 *
 * URL patterns:
 * - Gốc: http://domain.com/movie/blog/view/id/1
 * - Rewrite: http://domain.com/blog1.html (qua Router)
 */
namespace Magenest\Movie\Controller\Blog;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magenest\Movie\Model\ResourceModel\Blog\CollectionFactory;
use Magento\Framework\Registry;

/**
 * Class View
 *
 * Hiển thị trang chi tiết blog
 */
class View extends Action implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     * Để tạo Result Page
     */
    protected $resultPageFactory;

    /**
     * @var CollectionFactory
     * Để load blog collection với author info (join)
     */
    protected $blogCollectionFactory;

    /**
     * @var Registry
     * Để lưu current blog vào registry (dùng trong block/template)
     */
    protected $registry;

    /**
     * @var ForwardFactory
     * Để forward đến 404 page nếu blog không tồn tại
     */
    protected $resultForwardFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param CollectionFactory $blogCollectionFactory
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        CollectionFactory $blogCollectionFactory,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->blogCollectionFactory = $blogCollectionFactory;
        $this->registry = $registry;
    }

    /**
     * Execute action - Load và hiển thị blog
     *
     * @return ResultInterface
     * Bước 1: Lấy blog ID từ request parameter
     *   - URL: /movie/blog/view/id/1 → ID = 1
     *   - URL: /blog1.html → Router set param ID = 1
     *
     * Bước 2: Validate ID
     *   - Nếu không có ID → 404
     *
     * Bước 3: Load blog từ database
     *   - Tạo Blog collection
     *   - Join với admin_user để có author info
     *   - Load theo ID
     *
     * Bước 4: Kiểm tra blog tồn tại và active
     *   - Nếu không tồn tại → 404
     *   - Nếu status = 0 (disabled) → 404
     *
     * Bước 5: Lưu blog vào Registry
     *   - Registry giống như "global variable" trong request
     *   - Block/Template có thể lấy blog từ registry
     *
     * Bước 6: Tạo Result Page
     *   - Set page title
     *   - Set meta description
     *   - Set breadcrumbs
     *
     * Bước 7: Return page
     **/
    public function execute(): ResultInterface
    {
        // Bước 1: Lấy blog ID từ parameter
        $blogId = (int) $this->getRequest()->getParam('id');

        // Bước 2: Validate ID
        if (!$blogId) {
            // Không có ID → Forward đến 404
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }

        // Bước 3: Load blog từ database với author info
        // Sử dụng Collection để có thể join author
        /** @var \Magenest\Movie\Model\ResourceModel\Blog\Collection $collection */
        $collection = $this->blogCollectionFactory->create();
        $collection->addFieldToFilter('id', $blogId)
                   ->joinAuthor(); // Join với admin_user để có author info

        $blog = $collection->getFirstItem();

        // Bước 4: Kiểm tra blog tồn tại và active
        if (!$blog->getId() || $blog->getStatus() != 1) {
            // Blog không tồn tại hoặc bị disable → 404
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }

        // Bước 5: Lưu blog vào Registry để Block/Template sử dụng
        // Key: 'current_blog'
        $this->registry->register('current_blog', $blog);

        // Bước 6: Tạo Result Page
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        // Set page title (hiển thị trên browser tab)
        $resultPage->getConfig()->getTitle()->set($blog->getTitle());

        // Set meta description (SEO)
        if ($blog->getDescription()) {
            $resultPage->getConfig()->setDescription($blog->getDescription());
        }

        // Set breadcrumbs
        /** @var \Magento\Theme\Block\Html\Breadcrumbs $breadcrumbs */
        if ($breadcrumbs = $resultPage->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Home'),
                    'link' => $this->_url->getUrl('')
                ]
            );
            $breadcrumbs->addCrumb(
                'blog',
                [
                    'label' => __('Blog'),
                    'title' => __('Blog')
                ]
            );
            $breadcrumbs->addCrumb(
                'blog_view',
                [
                    'label' => $blog->getTitle(),
                    'title' => $blog->getTitle()
                ]
            );
        }

        // Bước 7: Return page
        return $resultPage;
    }
}

