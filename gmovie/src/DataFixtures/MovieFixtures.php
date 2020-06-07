<?php

namespace App\DataFixtures;

use App\Entity\Movie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class MovieFixtures extends Fixture
{
    protected $genres = ['Horror', 'Action', 'Comedy', 'Thriller', 'Adventure', 'Mystery', 'Documental'];
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 20; $i++) {
            $movie = new Movie();
            $movie->setTitle('movie' . $i);
            $movie->setUuid($i);
            $movie->setSourceClass('https://api.themoviedb.org');
            $movie->setRate(5.5);
            $movie->setGenre($this->genres[array_rand($this->genres)]);
            $manager->persist($movie);
        }

        $manager->flush();
    }
}
