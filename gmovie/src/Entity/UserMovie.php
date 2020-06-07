<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserMovieRepository")
 */
class UserMovie
{
    const NOT_SEEN = 0;
    const LIKE = 1;
    const SEEN = 2;
    const DISLIKE = 3;
    const MIN_REQUIRED_MOVIES = 20;
    const MAX_BUCKET_MOVIES = 10;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Movie")
     * @ORM\JoinColumn(nullable=false)
     */
    private $movie;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":0})
     */
    private $isWatched;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default":0})
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMovie(): ?Movie
    {
        return $this->movie;
    }

    public function setMovie(?Movie $movie): self
    {
        $this->movie = $movie;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getIsWatched(): ?bool
    {
        return $this->isWatched;
    }

    public function setIsWatched(bool $isWatched): self
    {
        $this->isWatched = $isWatched;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function getStatusValue(): ?string
    {
        switch ($this->status) {
            case self::LIKE:
                return 'like';
                break;
            case self::SEEN:
                return 'already seen';
                break;
            case self::DISLIKE:
                return 'dislike';
                break;
            default:
                throw new \InvalidArgumentException("Invalid status");
                break;
        }
    }

    public function setStatus(?int $status): self
    {
        if (!in_array($status, array(self::NOT_SEEN, self::LIKE, self::DISLIKE, self::SEEN))) {
            throw new \InvalidArgumentException("Invalid status");
        }
        $this->status = $status;

        return $this;
    }

    public function setUserMovie(Movie $movie, User $user, bool $isWatched, $status)
    {
        $this->setMovie($movie);
        $this->setUser($user);
        $this->setIsWatched($isWatched);
        $this->setStatus($status);
        return $this;
    }
}
