<?php
declare(strict_types=1);

namespace Magenest\Movie\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Cache\TypeListInterface;

class ConfigPingToPong implements ObserverInterface
{
    protected $configWriter; //ghi config vao DB
    protected $request; // lấy req DB
    protected $cacheTypeList; // clear cache

    public function __construct(writerInterface $configWriter, RequestInterface $request, TypeListInterface $cacheTypeList)
    {
        $this->configWriter = $configWriter;
        $this->request = $request;
        $this->cacheTypeList = $cacheTypeList;
    }
    public function execute(Observer $observer): void
    {
        $groups = $this->request->getPost('groups');
        if($groups['excercises']['fields']['text_field']['value'] === 'Ping') {
            $this->configWriter->save(
                'movie/excercises/text_field',
                'Pong',
                'default',
                '0'
            );

            $this->cacheTypeList->cleanType('config');
        }
    }
}
