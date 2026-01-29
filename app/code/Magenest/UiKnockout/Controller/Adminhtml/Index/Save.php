<?php
namespace Magenest\UiKnockout\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Catalog\Model\ImageUploader;
use Magenest\UiKnockout\Model\BannerFactory;

class Save extends Action
{
    protected $bannerFactory;
    /**
     * @var \Magento\Catalog\Model\ImageUploader
     */
    protected $imageUploader;

    public function __construct(
        Action\Context $context,
        BannerFactory $bannerFactory,
        ImageUploader $imageUploader,
    ){
        parent::__construct($context);
        $this->bannerFactory = $bannerFactory;
        $this->imageUploader = $imageUploader;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        // Tạo đối tượng result redirect để chuyển hướng sau khi lưu
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            // Xử lý hình ảnh
            // [image] =>Array ( [0] => Array ( [name] => abc.jpg, [tmp_name] => ... ) )
            if (isset($data['image']) && is_array($data['image'])) {
                // Kiểm tra xem có tên file không (tránh trường hợp mảng rỗng)
                if (!empty($data['image'][0]['name'])) {
                    // Lấy tên file ảnh từ mảng
                    $imageName = $data['image'][0]['name'];
                    // Nếu có 'tmp_name', nghĩa là ảnh mới upload -> Cần di chuyển từ TMP sang chính thức
                    if (isset($data['image'][0]['tmp_name'])) {
                        try {
                            $this->imageUploader->moveFileFromTmp($imageName);
                        } catch (\Exception $e) {
//                            $this->messageManager->addErrorMessage(__('Không thể lưu ảnh: %1', $e->getMessage()));
//                            return $resultRedirect->setPath('*/*/edit', ['banner_id' => $this->getRequest()->getParam('banner_id')]);
                        }

                    }
                    // Cuối cùng: Gán lại chuỗi tên file vào data để lưu xuống DB
                    $data['image'] = $imageName;
                }
                else
                {
                    // Trường hợp mảng rỗng (xóa ảnh)
                    $data['image'] = null;
                }
            } else {
                // Trường hợp mảng rỗng (xóa ảnh)
                $data['image'] = null;
            }

            // Khởi tạo Model
            $model = $this->bannerFactory->create();
            $id = $this->getRequest()->getParam('banner_id');
            if ($id) {
                $model->load($id);
            }


            if (isset($data['banner_id']) && $data['banner_id'] === '') {
                unset($data['banner_id']);
            }

            $model->setData($data);
            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('Đã lưu Banner thành công.'));

                // Logic nút "Save and Continue Edit"
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['banner_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['banner_id' => $id]);
            }
        }
        return $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_UiKnockou t::ui_knockout');
    }
}
