<?php

require_once __DIR__ . '/../models/User.php';


class UserMapper
{
    public static function fromRows(array $rows): array
    {
        return array_map(
            fn(array $row) => new User(
                (int) $row['user_id'],
                $row['username'],
                $row['role'],
                $row['email'] ?? null,
                $row['favorite_genre'] ?? null,
                (int) $row['total_score']
            ),
            $rows
        );
    }
}
