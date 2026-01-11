<?php

class QuizPreview
{
    private int $id;
    private string $title;
    private ?string $albumCoverUrl;
    private string $createdByUsername;

    public function __construct(
        int $id,
        string $title,
        ?string $albumCoverUrl,
        string $createdByUsername
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->albumCoverUrl = $albumCoverUrl;
        $this->createdByUsername = $createdByUsername;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAlbumCoverUrl(): ?string
    {
        return $this->albumCoverUrl;
    }

    public function getCreatedByUsername(): string
    {
        return $this->createdByUsername;
    }
}
