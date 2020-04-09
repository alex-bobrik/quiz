<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ViolationRepository")
 */
class Violation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ViolationAct", mappedBy="violation")
     */
    private $violationActs;

    public function __construct()
    {
        $this->violationActs = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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
            $violationAct->setViolation($this);
        }

        return $this;
    }

    public function removeViolationAct(ViolationAct $violationAct): self
    {
        if ($this->violationActs->contains($violationAct)) {
            $this->violationActs->removeElement($violationAct);
            // set the owning side to null (unless already changed)
            if ($violationAct->getViolation() === $this) {
                $violationAct->setViolation(null);
            }
        }

        return $this;
    }
}
