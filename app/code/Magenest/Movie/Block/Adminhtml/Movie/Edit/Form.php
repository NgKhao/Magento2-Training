<?php
namespace Magenest\Movie\Block\Adminhtml\Movie\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magenest\Movie\Model\ResourceModel\Director\CollectionFactory;
class Form extends Generic
{
    protected $directorCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        CollectionFactory $directorCollectionFactory,
        array $data = []
    ) {
        $this->directorCollectionFactory = $directorCollectionFactory;
        parent::__construct($context, $registry , $formFactory ,$data);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'method' => 'post',
                'action' => $this->getUrl('*/*/save') // */*/save theo _controler thành adminhtml/movie/save
            ]
        ]);

//        name="movie_edit"
        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('Movie Information'),
        ]);

        $fieldset->addField('name', 'text', [
            'name' => 'name',
            'label' => __('Name'),
            'required' => true,
        ]);

        $fieldset->addField('description', 'textarea',
        [
            'name' => 'description',
            'label' => __('Description'),
        ]);

        $fieldset->addField('rating', 'text', [
            'name' => 'rating',
            'label' => __('Rating'),
        ]);

        $fieldset->addField('director', 'select',
        [
            'name' => 'director_id',
            'label' => __('Director'),
            'required' => true,
            'values' => $this->getDirectorOptions()
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    protected  function getDirectorOptions()
    {
        $options = [['value'=> null, 'label' => __('-- Select Director --')]];
        $collection = $this->directorCollectionFactory->create();
        foreach ($collection as $director) {
            $options[] = ['value' => $director->getDirectorId(), 'label' => $director->getName()];
        }
        return $options;
    }
}
