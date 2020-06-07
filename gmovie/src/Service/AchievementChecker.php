<?php

namespace App\Service;

use App\Repository\AchievementBadgeRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MovieRepository;
use App\Repository\UserMovieRepository;
use Symfony\Component\Security\Core\Security;
use App\Entity\UserAchievement;
use App\Repository\UserAchievementRepository;
use App\Entity\UserMovie;

class AchievementChecker
{

    private $achievementRepository;
    private $entityManager;
    private $movieRepository;
    private $security;
    private $userMovieRepository;
    private $achievementsWon = array();
    private $userAchievementRepository;
    public function __construct(
        AchievementBadgeRepository $achievementBadgeRepository,
        EntityManagerInterface $entityManager,
        MovieRepository $movieRepository,
        Security $security,
        UserMovieRepository $userMovieRepository,
        UserAchievementRepository $userAchievementRepository
    ) {
        $this->achievementRepository = $achievementBadgeRepository;
        $this->entityManager = $entityManager;
        $this->movieRepository = $movieRepository;
        $this->security = $security;
        $this->userMovieRepository = $userMovieRepository;
        $this->userAchievementRepository = $userAchievementRepository;
    }

    public function isEligibleForAchievement()
    {
        $result = true;
        foreach ($this->getAllAchievements() as $achievement) {
            foreach ($achievement->getAchievement()->getRule() as $rule) {
                $moviesBasedOnRule = $this->getUserMoviesBasedOnRule($rule);
                if (!$moviesBasedOnRule) {
                    $result = false;
                }
            }
            if ($result) {
                $this->registerAchievement($achievement);
                array_push($this->achievementsWon, $achievement);
            }
        }
        return $result;
    }

    public function getAchievementsWon()
    {
        return $this->achievementsWon;
    }

    private function registerAchievement($achievement)
    {
        $result = false;
        if ($this->userAchievementDoesNotExists($achievement)) {
            $userAchievement = new UserAchievement();
            $userAchievement->setAchievementBadge($achievement)
                ->setUser($this->security->getUser())
                ->setDate(new \DateTime('now'));
            $this->entityManager->persist($userAchievement);
            $this->entityManager->flush();
            $result = true;
        }
        return $result;
    }

    private function userAchievementDoesNotExists($achievement)
    {
        $result = true;
        if ($this->userAchievementRepository->findOneBy(
            array(
                'achievementBadge' => $achievement,
                'user'             => $this->security->getUser()
            )
        )) {
            $result = false;
        }
        return $result;
    }

    private function getUserMoviesBasedOnRule($rule)
    {
        $movies = $this->movieRepository->findBy(
            $this->getRuleCriteria($rule)['criteria']
        );
        $userMovies = $this->userMovieRepository->findBy(
            array(
                'user' => $this->security->getUser(),
                'movie' => $movies,
                'isWatched' => true,
                'status' => array(UserMovie::LIKE, UserMovie::DISLIKE)
            )
        );
        if (count($userMovies) == $rule['count']) {
            return $userMovies;
        }
        return 0;
    }

    private function getAllAchievements()
    {
        $achievements = $this->achievementRepository->findAll();
        return $achievements;
    }

    private function getRuleCriteria($rule)
    {
        $criteria = array();
        $criteria[strtolower($rule['type'])] = strtolower($rule['value']);
        return array('criteria' => $criteria, 'count' => $rule['count']);
    }
}
