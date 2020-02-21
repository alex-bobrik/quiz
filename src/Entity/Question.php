<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuestionRepository")
 */
class Question
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Can't be blank.")
     * @Assert\Length(
     *     max="50",
     *     min="5",
     *     maxMessage="Max {{ limit }} characters.",
     *     minMessage="Min {{ limit }} characters."
     * )
     */
    private $text;

//    /**
//     * @ORM\OneToMany(targetEntity="App\Entity\Answer", mappedBy="question", orphanRemoval=true, cascade={"persist"})
//     */
//    private $answers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\QuizQuestion", mappedBy="question")
     */
    private $quizQuestions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Answer", mappedBy="question" ,orphanRemoval=true, cascade={"persist"})
     */
    private $answers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Game", mappedBy="currentQuestion")
     */
    private $games;

//    /**
//     * @ORM\ManyToOne(targetEntity="App\Entity\Answer")
//     */
//    private $answers;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->quizQuestions = new ArrayCollection();
        $this->games = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

//    /**
//     * @return Collection|QuestionAnswer[]
//     */
//    public function getAnswers(): Collection
//    {
//        return $this->answers;
//    }
//
//    public function addAnswer(QuestionAnswer $questionAnswer): self
//    {
//        if (!$this->answers->contains($questionAnswer)) {
//            $this->answers[] = $questionAnswer;
//            $questionAnswer->setQuestion($this);
//        }
//
//        return $this;
//    }
//
//    public function removeAnswer(QuestionAnswer $questionAnswer): self
//    {
//        if ($this->answers->contains($questionAnswer)) {
//            $this->answers->removeElement($questionAnswer);
//            // set the owning side to null (unless already changed)
//            if ($questionAnswer->getQuestion() === $this) {
//                $questionAnswer->setQuestion(null);
//            }
//        }
//
//        return $this;
//    }

    /**
     * @return Collection|QuizQuestion[]
     */
    public function getQuizQuestions(): Collection
    {
        return $this->quizQuestions;
    }

    public function addQuizQuestion(QuizQuestion $quizQuestion): self
    {
        if (!$this->quizQuestions->contains($quizQuestion)) {
            $this->quizQuestions[] = $quizQuestion;
            $quizQuestion->setQuestions($this);
        }

        return $this;
    }

    public function removeQuizQuestion(QuizQuestion $quizQuestion): self
    {
        if ($this->quizQuestions->contains($quizQuestion)) {
            $this->quizQuestions->removeElement($quizQuestion);
            // set the owning side to null (unless already changed)
            if ($quizQuestion->getQuestions() === $this) {
                $quizQuestion->setQuestions(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Answer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->contains($answer)) {
            $this->answers->removeElement($answer);
            // set the owning side to null (unless already changed)
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Game[]
     */
    public function getGames(): Collection
    {
        return $this->games;
    }

    public function addGame(Game $game): self
    {
        if (!$this->games->contains($game)) {
            $this->games[] = $game;
            $game->setCurrentQuestion($this);
        }

        return $this;
    }

    public function removeGame(Game $game): self
    {
        if ($this->games->contains($game)) {
            $this->games->removeElement($game);
            // set the owning side to null (unless already changed)
            if ($game->getCurrentQuestion() === $this) {
                $game->setCurrentQuestion(null);
            }
        }

        return $this;
    }

//    public function getAnswers(): ?Answer
//    {
//        return $this->answers;
//    }
//
//    public function setAnswers(?Answer $answers): self
//    {
//        $this->answers = $answers;
//
//        return $this;
//    }
}
