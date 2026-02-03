<?php
declare(strict_types=1);

namespace Magenest\Movie\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ResetMovieRating implements ObserverInterface
{
    function execute(Observer $observer)
    {
        $movie = $observer->getEvent()->getMovie();
        if($movie)
        {
            $movie->setRating(0);
        }
    }
}
