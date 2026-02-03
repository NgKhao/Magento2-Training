<?php
declare(strict_types=1);

namespace Magenest\Movie\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerSaveBefore implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if($customer)
        {
            $customer->setFirstname('Magenest');
        }
    }
}
