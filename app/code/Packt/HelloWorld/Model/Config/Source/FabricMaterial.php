<?php
namespace Packt\HelloWorld\Model\Config\Source;
use \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class FabricMaterial extends AbstractSource
{
    public function getAllOptions()
    {
        if(null === $this->_options)
        {
            $this->_options = [
                ['label' => __('Cotton'), 'value' => 'cotton'],
                ['label' => __('Wool'), 'value' => 'wool'],
                ['label' => __('Silk'), 'value' => 'silk'],
                ['label' => __('Linen'), 'value' => 'linen']
            ];
        }
        return $this->_options;
    }
}
