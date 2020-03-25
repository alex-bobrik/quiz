<?php declare(strict_types=1);


namespace App\Service;


class TokenGenerator
{
    private $token;

    public function __construct()
    {
        $this->token = str_replace("/", "", password_hash((string)rand(0, 10000), PASSWORD_DEFAULT));
    }

    public function getToken(): string
    {
        return $this->token;
    }
}