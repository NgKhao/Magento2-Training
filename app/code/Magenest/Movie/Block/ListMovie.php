<?php
namespace Magenest\Movie\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magenest\Movie\Model\ResourceModel\Movie\CollectionFactory as MovieCollectionFactory;
use Magenest\Movie\Model\DirectorFactory;
use Magenest\Movie\Model\ResourceModel\Actor\CollectionFactory as ActorCollectionFactory;

class ListMovie extends Template
{
    protected $movieCollectionFactory;
    protected $directorFactory;
    protected $actorCollectionFactory;

    public function __construct(Context $context, MovieCollectionFactory $movieCollectionFactory, DirectorFactory $directorFactory, ActorCollectionFactory $actorCollectionFactory,array $data = [])
    {
        $this->movieCollectionFactory = $movieCollectionFactory;
        $this->directorFactory = $directorFactory;
        $this->actorCollectionFactory = $actorCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getMovies()
    {
        return $this->movieCollectionFactory->create();
    }

    public function getDirector($directorId)
    {
        $director = $this->directorFactory->create();
        $director->load($directorId);
        return $director;
    }

    public function getActorsByMovieId($movieId)
    {
        $connection = $this->actorCollectionFactory->create()->getConnection(); //object collection query SQL
        $select = $connection->select(); //khoi tao query builder
        $select->from(
            ['ma' => 'magenest_movie_actor'],
            []
        )->join(
            ['a' => 'magenest_actor'],
            'ma.actor_id = a.actor_id',
            ['actor_id', 'name']
        )->where(
            'ma.movie_id = ?',
            $movieId
        );
        return $connection->fetchAll($select); // run query reutn ve array data
    }
}
