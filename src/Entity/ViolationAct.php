<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ViolationActRepository")
 */
class ViolationAct
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $violation_date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="violationActs")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Violation", inversedBy="violationActs")
     */
    private $violation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getViolationDate(): ?\DateTimeInterface
    {
        return $this->violation_date;
    }

    public function setViolationDate(\DateTimeInterface $violation_date): self
    {
        $this->violation_date = $violation_date;

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

    public function getViolation(): ?Violation
    {
        return $this->violation;
    }

    public function setViolation(?Violation $violation): self
    {
        $this->violation = $violation;

        return $this;
    }
}
