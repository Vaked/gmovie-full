<?php

namespace App\DataFixtures;

use App\Entity\Movie;
use App\Entity\User;
use App\Entity\UserMovie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class UserMovieFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $movieRepository = $manager->getRepository(Movie::class);
        $userRepository = $manager->getRepository(User::class);
        $users = $userRepository->findAll();
        $movies = $movieRepository->findAll();
        for ($i = 0; $i < 20; $i++) {
            $usermovie = new UserMovie();
            $usermovie->setUser($users[array_rand($users)]);
            $usermovie->setMovie($movies[array_rand($movies)]);
            $manager->persist($usermovie);
        }

        $manager->flush();
    }
}
