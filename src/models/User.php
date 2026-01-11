<?php

class User
{
    private int $id;
    private string $username;
    private string $role;
    private ?string $email;
    private ?string $favoriteGenre;
    private int $totalScore;

    public function __construct(
        int $id,
        string $username,
        string $role,
        ?string $email,
        ?string $favoriteGenre,
        int $totalScore
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->role = $role;
        $this->email = $email;
        $this->favoriteGenre = $favoriteGenre;
        $this->totalScore = $totalScore;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getFavoriteGenre(): ?string
    {
        return $this->favoriteGenre;
    }

    public function getTotalScore(): int
    {
        return $this->totalScore;
    }
}
