<?php

namespace Magenest\Movie\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        // Insert Directors
        $directorData = [
            ['name' => 'Christopher Nolan'],
            ['name' => 'Steven Spielberg'],
            ['name' => 'Quentin Tarantino'],
            ['name' => 'Martin Scorsese'],
            ['name' => 'James Cameron'],
        ];

        foreach ($directorData as $data) {
            $setup->getConnection()->insert(
                $setup->getTable('magenest_director'),
                $data
            );
        }

        // Insert Actors
        $actorData = [
            ['name' => 'Leonardo DiCaprio'],
            ['name' => 'Tom Hanks'],
            ['name' => 'Brad Pitt'],
            ['name' => 'Samuel L. Jackson'],
            ['name' => 'Christian Bale'],
            ['name' => 'Morgan Freeman'],
            ['name' => 'Robert De Niro'],
            ['name' => 'Al Pacino'],
            ['name' => 'Kate Winslet'],
            ['name' => 'Scarlett Johansson'],
        ];

        foreach ($actorData as $data) {
            $setup->getConnection()->insert(
                $setup->getTable('magenest_actor'),
                $data
            );
        }

        // Insert Movies
        $movieData = [
            [
                'name' => 'Inception',
                'description' => 'A thief who steals corporate secrets through the use of dream-sharing technology.',
                'rating' => 9,
                'director_id' => 1 // Christopher Nolan
            ],
            [
                'name' => 'The Dark Knight',
                'description' => 'Batman faces the Joker, a criminal mastermind who wants to plunge Gotham into anarchy.',
                'rating' => 9,
                'director_id' => 1 // Christopher Nolan
            ],
            [
                'name' => 'Saving Private Ryan',
                'description' => 'Following the Normandy Landings, a group of U.S. soldiers go behind enemy lines to retrieve a paratrooper.',
                'rating' => 8,
                'director_id' => 2 // Steven Spielberg
            ],
            [
                'name' => 'Pulp Fiction',
                'description' => 'The lives of two mob hitmen, a boxer, a gangster and his wife intertwine in four tales of violence and redemption.',
                'rating' => 9,
                'director_id' => 3 // Quentin Tarantino
            ],
            [
                'name' => 'The Wolf of Wall Street',
                'description' => 'Based on the true story of Jordan Belfort, from his rise to a wealthy stock-broker to his fall.',
                'rating' => 8,
                'director_id' => 4 // Martin Scorsese
            ],
            [
                'name' => 'Titanic',
                'description' => 'A seventeen-year-old aristocrat falls in love with a kind but poor artist aboard the luxurious, ill-fated R.M. S. Titanic.',
                'rating' => 8,
                'director_id' => 5 // James Cameron
            ],
            [
                'name' => 'Avatar',
                'description' => 'A paraplegic Marine dispatched to the moon Pandora on a unique mission becomes torn between following his orders and protecting the world.',
                'rating' => 8,
                'director_id' => 5 // James Cameron
            ],
        ];

        foreach ($movieData as $data) {
            $setup->getConnection()->insert(
                $setup->getTable('magenest_movie'),
                $data
            );
        }

        // Insert Movie-Actor relationships (Many-to-Many)
        $movieActorData = [
            // Inception - Leonardo DiCaprio, Tom Hanks
            ['movie_id' => 1, 'actor_id' => 1],
            ['movie_id' => 1, 'actor_id' => 2],

            // The Dark Knight - Christian Bale, Morgan Freeman
            ['movie_id' => 2, 'actor_id' => 5],
            ['movie_id' => 2, 'actor_id' => 6],

            // Saving Private Ryan - Tom Hanks
            ['movie_id' => 3, 'actor_id' => 2],

            // Pulp Fiction - Samuel L. Jackson, Brad Pitt
            ['movie_id' => 4, 'actor_id' => 4],
            ['movie_id' => 4, 'actor_id' => 3],

            // The Wolf of Wall Street - Leonardo DiCaprio
            ['movie_id' => 5, 'actor_id' => 1],

            // Titanic - Leonardo DiCaprio, Kate Winslet
            ['movie_id' => 6, 'actor_id' => 1],
            ['movie_id' => 6, 'actor_id' => 9],

            // Avatar - Scarlett Johansson
            ['movie_id' => 7, 'actor_id' => 10],
        ];

        foreach ($movieActorData as $data) {
            $setup->getConnection()->insert(
                $setup->getTable('magenest_movie_actor'),
                $data
            );
        }

        $setup->endSetup();
    }
}
