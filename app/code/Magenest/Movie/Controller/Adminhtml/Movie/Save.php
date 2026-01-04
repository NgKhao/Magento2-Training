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
        $data = $this->getRequest()->getPostValue(); //lấy toàn bộ data post
//        [
//            'name' => 'Avatar',
//            'description' => 'Sci-fi movie',
//        ]

        if(!$data)
        {
           return $this->_redirect('*/*/');
        }

        try{
            $movie = $this->movieFactory->create();
            $movie->setData($data);
            $movie->save();

            $this->messageManager->addSuccessMessage(__('You saved the movie.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->_redirect('*/*/');
    }
}
