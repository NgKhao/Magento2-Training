<?php
namespace Magenest\Movie\Model;

use Magento\Framework\Model\AbstractModel;

class Movie extends AbstractModel
{
    protected function _construct(){
        $this->_init('Magenest\Movie\Model\ResourceModel\Movie');
    }

//    dispatch observer
    public function beforeSave()
    {
        $this->_eventManager->dispatch(
            'movie_before_save',
            ['movie' => $this] //Data:  pass movie object với key 'movie'
        );

        return parent::beforeSave();
    }
}
