CREATE TYPE user_role AS ENUM ('user', 'admin');

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    passwordHash VARCHAR(255) NOT NULL,
    role user_role NOT NULL DEFAULT 'user'
);

CREATE TABLE quizzes (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    albumCoverUrl VARCHAR(500),
    createdBy INT NOT NULL,

    CONSTRAINT fk_quiz_user
        FOREIGN KEY (createdBy)
        REFERENCES users(id)
        ON DELETE CASCADE
);


INSERT INTO users (username, passwordHash, role)
VALUES (
    'admin',
    '$2a$12$9mWIpC1avGSNX7gtgA7EyeVpFTUAJ90Zng6A9yIEG9F1iPlwW6uK.',
    'admin'
);


INSERT INTO quizzes (title, albumCoverUrl, createdBy) VALUES
(
    'Classic Rock Riffs',
    'https://images.unsplash.com/photo-1511379938547-c1f69419868d',
    1
),
(
    'Jazz Essentials',
    'https://images.unsplash.com/photo-1507838153414-b4b713384a76',
    1
),
(
    'Movie Soundtracks',
    'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4',
    1
),
(
    '90s Pop Hits',
    'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f',
    1
);
