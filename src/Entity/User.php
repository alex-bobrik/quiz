<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\Email()
     * @Assert\NotBlank(message="Can't be blank.")
     */
    private $email;

    /**
     * @ORM\Column(type="array")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Game", mappedBy="user")
     */
    private $games;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Rating", mappedBy="user")
     */
    private $ratings;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Quiz", mappedBy="user")
     */
    private $quizzes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Question", mappedBy="user")
     */
    private $questions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ViolationAct", mappedBy="user", cascade={"persist"})
     */
    private $violationActs;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(
     *     max="25",
     *     maxMessage="Максимальная длина - 25 символов",
     *     min="3",
     *     minMessage="Минимальная длина - 3 символа"
     * )
     * @Assert\Regex(
     *     pattern="/^[a-zA-Z0-9]+([_-]?[a-zA-Z0-9])*$/",
     *     message="Никнейм может содержать только символы A-z, 0-9, -, _ (Пр.: alex-bobrik, xxx_CooL-BoY_xxx)"
     * )
     */
    private $nickname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\File()
     */
    private $image;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Quiz", mappedBy="checkedBy")
     */
    private $checkedQuizzes;

    public function __construct()
    {
        $this->games = new ArrayCollection();
        $this->roles = array('ROLE_USER');
        $this->ratings = new ArrayCollection();
        $this->quizzes = new ArrayCollection();
        $this->questions = new ArrayCollection();
        $this->violationActs = new ArrayCollection();
        $this->checkedQuizzes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive($isActive): void
    {
        $this->isActive = $isActive;
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
            $game->setUser($this);
        }

        return $this;
    }

    public function removeGame(Game $game): self
    {
        if ($this->games->contains($game)) {
            $this->games->removeElement($game);
            // set the owning side to null (unless already changed)
            if ($game->getUser() === $this) {
                $game->setUser(null);
            }
        }

        return $this;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

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
            $rating->setUser($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): self
    {
        if ($this->ratings->contains($rating)) {
            $this->ratings->removeElement($rating);
            // set the owning side to null (unless already changed)
            if ($rating->getUser() === $this) {
                $rating->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Quiz[]
     */
    public function getQuizzes(): Collection
    {
        return $this->quizzes;
    }

    public function addQuiz(Quiz $quiz): self
    {
        if (!$this->quizzes->contains($quiz)) {
            $this->quizzes[] = $quiz;
            $quiz->setUser($this);
        }

        return $this;
    }

    public function removeQuiz(Quiz $quiz): self
    {
        if ($this->quizzes->contains($quiz)) {
            $this->quizzes->removeElement($quiz);
            // set the owning side to null (unless already changed)
            if ($quiz->getUser() === $this) {
                $quiz->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Question[]
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->setUser($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->contains($question)) {
            $this->questions->removeElement($question);
            // set the owning side to null (unless already changed)
            if ($question->getUser() === $this) {
                $question->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ViolationAct[]
     */
    public function getViolationActs(): Collection
    {
        return $this->violationActs;
    }

    public function addViolationAct(ViolationAct $violationAct): self
    {
        if (!$this->violationActs->contains($violationAct)) {
            $this->violationActs[] = $violationAct;
            $violationAct->setUser($this);
        }

        return $this;
    }

    public function removeViolationAct(ViolationAct $violationAct): self
    {
        if ($this->violationActs->contains($violationAct)) {
            $this->violationActs->removeElement($violationAct);
            // set the owning side to null (unless already changed)
            if ($violationAct->getUser() === $this) {
                $violationAct->setUser(null);
            }
        }

        return $this;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
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

    // Sum points of all games by user
    public function getSumOfPoints(): int
    {
        $points = 0;
        foreach ($this->games as $game) {
            $points += $game->getResultScore();
        }

        return $points;
    }

    // Average value of game.result_time by user in milliseconds
    public function getAvgGameTime(): float
    {
        $amountOfGames = $this->games->count();

        if (!$amountOfGames) {
            return 0;
        }

        $totalTime = 0;
        foreach ($this->games as $game) {
            $totalTime += $game->getResultTime();
        }

        return ($totalTime / $amountOfGames) * 1000;
    }

    /**
     * @return Collection|Quiz[]
     */
    public function getCheckedQuizzes(): Collection
    {
        return $this->checkedQuizzes;
    }

    public function addCheckedQuiz(Quiz $checkedQuiz): self
    {
        if (!$this->checkedQuizzes->contains($checkedQuiz)) {
            $this->checkedQuizzes[] = $checkedQuiz;
            $checkedQuiz->setCheckedBy($this);
        }

        return $this;
    }

    public function removeCheckedQuiz(Quiz $checkedQuiz): self
    {
        if ($this->checkedQuizzes->contains($checkedQuiz)) {
            $this->checkedQuizzes->removeElement($checkedQuiz);
            // set the owning side to null (unless already changed)
            if ($checkedQuiz->getCheckedBy() === $this) {
                $checkedQuiz->setCheckedBy(null);
            }
        }

        return $this;
    }
}
