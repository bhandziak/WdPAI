<?php

class Quiz
{
    public int $id;
    public string $title;
    public ?string $album_cover_url;
    /** @var Question[] */
    public array $questions;

    public function __construct(
        int $id,
        string $title,
        ?string $album_cover_url,
        array $questions
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->album_cover_url = $album_cover_url;
        $this->questions = $questions;
    }
}
