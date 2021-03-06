<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuizRepository")
 */
class Quiz
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
     *     min="5",
     *     max="50",
     *     minMessage="Минимум {{ limit }} символов",
     *     maxMessage="Максимум {{ limit }} символов"
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\QuizQuestion", mappedBy="quiz", orphanRemoval=true, cascade={"persist"})
     */
    private $questions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Game", mappedBy="quiz", orphanRemoval=true, cascade={"persist"})
     */
    private $games;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\QuizCategory", inversedBy="quizes")
     */
    private $quizCategory;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Rating", mappedBy="quiz", orphanRemoval=true)
     */
    private $ratings;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="quizzes")
     */
    private $user;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isChecked;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isTimeLimited;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="checkedQuizzes")
     */
    private $checkedBy;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->games = new ArrayCollection();
        $this->ratings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection|QuizQuestion[]
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(QuizQuestion $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->setQuiz($this);
        }

        return $this;
    }

    public function removeQuestion(QuizQuestion $question): self
    {
        if ($this->questions->contains($question)) {
            $this->questions->removeElement($question);
            // set the owning side to null (unless already changed)
            if ($question->getQuiz() === $this) {
                $question->setQuiz(null);
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

    public function addGame(Game $game ): self
    {
        if (!$this->games->contains($game)) {
            $this->games[] = $game;
            $game->setQuiz($this);
        }

        return $this;
    }

    public function removeGame(Game $game): self
    {
        if ($this->games->contains($game)) {
            $this->games->removeElement($game);
            // set the owning side to null (unless already changed)
            if ($game->getQuiz() === $this) {
                $game->setQuiz(null);
            }
        }

        return $this;
    }

    public function getQuizCategory(): ?QuizCategory
    {
        return $this->quizCategory;
    }

    public function setQuizCategory(?QuizCategory $quizCategory): self
    {
        $this->quizCategory = $quizCategory;

        return $this;
    }

    /**
     * @return Collection|Rating[]
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): self
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings[] = $rating;
            $rating->setQuiz($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): self
    {
        if ($this->ratings->contains($rating)) {
            $this->ratings->removeElement($rating);
            // set the owning side to null (unless already changed)
            if ($rating->getQuiz() === $this) {
                $rating->setQuiz(null);
            }
        }

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

    public function getIsChecked(): ?bool
    {
        return $this->isChecked;
    }

    public function setIsChecked(?bool $isChecked): self
    {
        $this->isChecked = $isChecked;

        return $this;
    }

    public function getAverageRating(): float
    {
        $stars = array();

        foreach ($this->ratings as $rating) {
            $stars[] = $rating->getStars();
        }

        if (!count($this->ratings)) {
            return 0;
        }

        return array_sum($stars) / count($this->ratings);
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getIsTimeLimited(): ?bool
    {
        return $this->isTimeLimited;
    }

    public function setIsTimeLimited(?bool $isTimeLimited): self
    {
        $this->isTimeLimited = $isTimeLimited;

        return $this;
    }

    public function getCheckedBy(): ?User
    {
        return $this->checkedBy;
    }

    public function setCheckedBy(?User $checkedBy): self
    {
        $this->checkedBy = $checkedBy;

        return $this;
    }
}
