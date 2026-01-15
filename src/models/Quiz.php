<?php

class Quiz
{
    public int $id;
    public string $title;
    public ?string $album_cover_url;
    public int $owner_id;
    /** @var Question[] */
    public array $questions;

    public function __construct(
        int $id,
        string $title,
        ?string $album_cover_url,
        int $owner_id,
        array $questions
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->album_cover_url = $album_cover_url;
        $this->owner_id = $owner_id;
        $this->questions = $questions;
    }
}
