CREATE TYPE user_role AS ENUM ('user', 'admin');

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    passwordHash VARCHAR(255) NOT NULL,
    role user_role NOT NULL DEFAULT 'user'
);

CREATE TABLE user_details (
    user_id INT PRIMARY KEY,
    email VARCHAR(255),
    favoriteGenre VARCHAR(50),

    CONSTRAINT fk_user_details_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
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

CREATE TABLE quiz_results (
    user_id INT NOT NULL,
    quiz_id INT NOT NULL,
    score INT DEFAULT 0,
    played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (user_id, quiz_id),

    CONSTRAINT fk_quiz_results_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_quiz_results_quiz
        FOREIGN KEY (quiz_id)
        REFERENCES quizzes(id)
        ON DELETE CASCADE
);



-- FUNCTIONS

CREATE OR REPLACE FUNCTION create_user(
    p_username VARCHAR,
    p_password_hash VARCHAR,
    p_role user_role DEFAULT 'user',
    p_email VARCHAR DEFAULT NULL,
    p_favorite_genre VARCHAR DEFAULT NULL
)
RETURNS INT
LANGUAGE plpgsql
AS $$
DECLARE
    v_user_id INT;
BEGIN
    INSERT INTO users (username, passwordHash, role)
    VALUES (p_username, p_password_hash, p_role)
    RETURNING id INTO v_user_id;

    INSERT INTO user_details (user_id, email, favoriteGenre)
    VALUES (v_user_id, p_email, p_favorite_genre);

    RETURN v_user_id;
END;
$$;

-- VIEWS

CREATE OR REPLACE VIEW user_data AS
SELECT
    u.id AS user_id,
    u.username,
    u.role,
    ud.email,
    ud.favoriteGenre,
    COALESCE(SUM(qr.score), 0) AS total_score
FROM users u
LEFT JOIN user_details ud
    ON u.id = ud.user_id
LEFT JOIN quiz_results qr
    ON u.id = qr.user_id
GROUP BY u.id, u.username, u.role ,ud.email, ud.favoriteGenre
ORDER BY total_score DESC;



-- DUMMY DATA

SELECT create_user(
    'admin',
    '$2a$12$9mWIpC1avGSNX7gtgA7EyeVpFTUAJ90Zng6A9yIEG9F1iPlwW6uK.',
    'admin',
    'admin@quizmusic.local',
    'rock'
);


INSERT INTO quizzes (title, albumCoverUrl, createdBy) VALUES
(
    'Classic Rock Riffs',
    '/uploads/albumCover/photo.jpg',
    1
),
(
    'Jazz Essentials',
    '/uploads/albumCover/photo.jpg',
    1
),
(
    'Movie Soundtracks',
    NULL,
    1
),
(
    '90s Pop Hits',
    '/uploads/albumCover/photo.jpg',
    1
);



