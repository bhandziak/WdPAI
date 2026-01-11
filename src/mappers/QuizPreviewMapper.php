<?php

require_once __DIR__ . './../models/QuizPreview.php';

class QuizPreviewMapper
{
    public static function fromArray(array $data): QuizPreview
    {
        return new QuizPreview(
            (int)$data['id'],
            $data['title'],
            $data['album_cover_url'] ?? null,
            $data['created_by_username']
        );
    }
}
