<?php

declare(strict_types=1);

namespace App\Service;


use Doctrine\ORM\EntityManagerInterface;

class QuestionService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createQuestion()
    {
    }
}