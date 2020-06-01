<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Url;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GameRepository")
 */
class Game
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $start_date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $end_date;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $result_score;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $result_time;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="games")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Quiz", inversedBy="games")
     */
    private $quiz;

//    /**
//     * @ORM\Column(type="boolean")
//     */
//    private $gameIsOver;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $QuestionNumber;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->start_date;
    }

    public function setStartDate(?\DateTimeInterface $start_date): self
    {
        $this->start_date = $start_date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEndDate(?\DateTimeInterface $end_date): self
    {
        $this->end_date = $end_date;

        return $this;
    }

    public function getResultScore(): ?int
    {
        return $this->result_score;
    }

    public function setResultScore(?int $result_score): self
    {
        $this->result_score = $result_score;

        return $this;
    }

    public function getResultTime(): ?int
    {
        return $this->result_time;
    }

    public function setResultTime(?int $result_time): self
    {
        $this->result_time = $result_time;

        return $this;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): self
    {
        $this->quiz = $quiz;

        return $this;
    }

    public function getGameIsOver(): ?bool
    {
//        return $this->gameIsOver;
        if ($this->getEndDate()) {
            return true;
        }

        return false;
    }

    public function setGameIsOver(bool $gameIsOver): self
    {
        $this->gameIsOver = $gameIsOver;

        return $this;
    }

    public function getQuestionNumber(): ?int
    {
        return $this->QuestionNumber;
    }

    public function setQuestionNumber(?int $QuestionNumber): self
    {
        $this->QuestionNumber = $QuestionNumber;

        return $this;
    }
}
