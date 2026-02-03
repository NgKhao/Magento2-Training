<?php
namespace Magenest\Movie\Controller\Adminhtml\Movie;

use Magenest\Movie\Model\MovieFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Save extends Action
{
    protected $movieFactory;

    public function __construct(Context $context, MovieFactory $movieFactory)
    {
        $this->movieFactory = $movieFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue(); //lấy toàn bộ data post

        if(!$data)
        {
           return $this->_redirect('*/*/');
        }

        try{
            $movieId = $data['movie_id'] ?? null;
            $movie = $this->movieFactory->create();
            if ($movieId) {
                $movie->load($movieId);
                if (!$movie->getId()) {
                    throw new \Exception(__('This movie no longer exists.'));
                }
            }

            unset($data['form_key']);

            if (isset($data['movie_id']) && $data['movie_id'] === '') {
                unset($data['movie_id']);
            }
            $movie->setData($data);
            $movie->save();

            $this->messageManager->addSuccessMessage(__('You saved the movie.'));
//            return $resultRedirect->setPath('*/*/edit', [
//                'movie_id' => $movie->getId()
//            ]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->_redirect('*/*/');
    }

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_Movie::movie');
    }
}
