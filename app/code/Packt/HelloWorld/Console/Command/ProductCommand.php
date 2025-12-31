<?php

namespace Packt\HelloWorld\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class ProductCommand extends Command
{

    protected $collectionFactory;
    public function __construct( CollectionFactory $collectionFactory )
    {
        $this->collectionFactory = $collectionFactory;
        parent::__construct(); // Gọi hàm khởi tạo của lớp cha Command.
    }

    protected function configure()
    {
        $this->setName('product:filter')
            ->setDescription('Lọc sản phẩm theo điều kiện')
            ->addOption('min_price', null, InputOption::VALUE_REQUIRED, 'Minimum price')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Name');

        parent::configure(); // Gọi hàm configure của lớp cha để hoàn tất cấu hình command.
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $minPrice = $input->getOption('min_price');
        $name = $input->getOption('name');

        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect(['name', 'price', 'sku']);

        if($minPrice){
            $collection->addAttributeToFilter('price', ['gt' => $minPrice]);
        }

        if($name){
            $collection->addAttributeToFilter('name', ['like' =>'%' . $name . '%']);
        }

        $collection->setOrder('price', 'ASC');
        $collection->setPageSize(5);

        $output->writeln("<comment>SQL QUery: </comment>" . $collection->getSelectSql()->__toString());

        if($collection->getSize() > 0){
            foreach ($collection as $item) {
                $output->writeln("<comment>" . $item->getName() . "\t" .$item->getPrice(). '\t' . $item->getSku() . "</comment>");
            }
        }

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}


