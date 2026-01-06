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

CREATE TABLE questions (
    id SERIAL PRIMARY KEY,
    quizId INT NOT NULL,
    text VARCHAR(1000) NOT NULL,
    audioUrl VARCHAR(500),

    CONSTRAINT fk_question_quiz
        FOREIGN KEY (quizId)
        REFERENCES quizzes(id)
        ON DELETE CASCADE
);

CREATE TABLE answers (
    id SERIAL PRIMARY KEY,
    questionId INT NOT NULL,
    text VARCHAR(1000) NOT NULL,
    isCorrect BOOLEAN NOT NULL DEFAULT FALSE,

    CONSTRAINT fk_answer_question
        FOREIGN KEY (questionId)
        REFERENCES questions(id)
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
    '/uploads/albumCover/photo.png',
    1
),
(
    'Jazz Essentials',
    '/uploads/albumCover/photo.png',
    1
),
(
    'Movie Soundtracks',
    '/uploads/albumCover/photo.png',
    1
),
(
    '90s Pop Hits',
    '/uploads/albumCover/photo.png',
    1
);
