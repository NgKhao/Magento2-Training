<?php
namespace Magenest\Movie\Block\Widget;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magenest\Movie\Model\ResourceModel\Movie\CollectionFactory as MovieCollectionFactory;
use Magenest\Movie\Model\DirectorFactory;
use Magenest\Movie\Model\ResourceModel\Actor\CollectionFactory as ActorCollectionFactory;

class MovieList extends Template implements BlockInterface
{
    protected $_template = "Magenest_Movie::widget/movie_list.phtml";

    protected $movieCollectionFactory;
    protected $directorFactory;
    protected $actorCollectionFactory;
    public function __construct(
        Template\Context $context,
        MovieCollectionFactory $movieCollectionFactory,
        DirectorFactory $directorFactory,
        ActorCollectionFactory $actorCollectionFactory,
        array $data = []
    ) {
        $this->movieCollectionFactory = $movieCollectionFactory;
        $this->directorFactory = $directorFactory;
        $this->actorCollectionFactory = $actorCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magenest\Movie\Model\ResourceModel\Movie\Collection
     */
    public function getMovies()
    {
        // Tạo collection (giống như query builder)
        $collection = $this->movieCollectionFactory->create();

        // getData('min_rating'): Lấy giá trị từ widget config
        $minRating = $this->getData('min_rating');
        if ($minRating) {
            // ['gteq' => $minRating]:  greater than or equal - >=
            $collection->addFieldToFilter('rating', ['gteq' => $minRating]);
        }

        $sortBy = $this->getData('sort_by');
        switch ($sortBy) {
            case 'name_asc':
                $collection->setOrder('name', 'ASC');
                break;
            case 'name_desc':
                $collection->setOrder('name', 'DESC');
                break;
            case 'rating_desc':
                $collection->setOrder('rating', 'DESC');
                break;
            case 'rating_asc':
                $collection->setOrder('rating', 'ASC');
                break;
            default:
                $collection->setOrder('rating', 'DESC');
        }

        $movieCount = $this->getData('movie_count');
        if ($movieCount) {
            $collection->setPageSize($movieCount);
        }
        return $collection;
    }

    /**
     * @param $directorId
     * @return \Magenest\Movie\Model\Director
     */
    public function getDirector($directorId)
    {
        $director = $this->directorFactory->create();
        $director->load($directorId);
        return $director;
    }

    /**
     * @param $movieId
     * @return array
     */
    public function getActorsByMovieId($movieId)
    {
        $connection = $this->actorCollectionFactory->create()->getConnection();

        // select(): Khởi tạo query builder
        $select = $connection->select();

        $select->from(
            ['ma' => 'magenest_movie_actor'],
            [] // Không select column nào từ bảng này
        )->join(
            ['a' => 'magenest_actor'],
            'ma.actor_id = a.actor_id',
            ['actor_id', 'name']
        )->where(
            'ma.movie_id = ?',
            $movieId
        );

        // fetchAll(): Execute query và trả về array
        return $connection->fetchAll($select);
    }
}

