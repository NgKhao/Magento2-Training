<?php

namespace Magenest\UiKnockout\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magenest\UiKnockout\Model\BannerFactory;

// Model Factory của trò

class Delete extends Action
{
    protected $bannerFactory;

    public function __construct(Context $context, BannerFactory $bannerFactory)
    {
        $this->bannerFactory = $bannerFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('banner_id');

        if ($id) {
            try {
                $model = $this->bannerFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('The banner has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['banner_id' => $id]);
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a banner to delete.'));
        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_UiKnockout::banner');
    }
}
