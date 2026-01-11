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
    score INT DEFAULT 0 CHECK (score >= 0),
    correct_answers INT NULL CHECK (correct_answers >= 0),
    incorrect_answers INT NULL CHECK (incorrect_answers >= 0),
    taken_time INT NULL CHECK (taken_time >= 0),
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

CREATE OR REPLACE VIEW quiz_content AS
SELECT
    q.id            AS quiz_id,
    q.title         AS quiz_title,
    q.albumCoverUrl AS quiz_cover,

    qs.id           AS question_id,
    qs.text         AS question_text,
    qs.audioUrl     AS question_audio,

    a.id            AS answer_id,
    a.text          AS answer_text,
    a.isCorrect     AS answer_correct
FROM quizzes q
LEFT JOIN questions qs ON qs.quizId = q.id
LEFT JOIN answers a ON a.questionId = qs.id
ORDER BY qs.id, a.id;

CREATE OR REPLACE VIEW quiz_preview AS
SELECT 
    q.id,
    q.title,
    q.albumCoverUrl,
    u.username AS createdByUsername
FROM quizzes q
JOIN users u ON u.id = q.createdBy
ORDER BY q.id DESC;

-- TRIGGERS

CREATE OR REPLACE FUNCTION check_quiz_result()
RETURNS TRIGGER AS $$
DECLARE
    number_of_questions INT;
BEGIN
    SELECT COUNT(*) INTO number_of_questions
    FROM questions
    WHERE quizId = NEW.quiz_id;

    IF (NEW.correct_answers + NEW.incorrect_answers) != number_of_questions THEN
        RAISE EXCEPTION 'The sum of correct answers and incorrect answers (% + %) does not match the number of questions in the quiz (%)',
            NEW.correct_answers, NEW.incorrect_answers, number_of_questions;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_check_quiz_result
BEFORE INSERT OR UPDATE ON quiz_results
FOR EACH ROW
EXECUTE FUNCTION check_quiz_result();


-- DUMMY DATA

SELECT create_user(
    'admin',
    '$2a$12$9mWIpC1avGSNX7gtgA7EyeVpFTUAJ90Zng6A9yIEG9F1iPlwW6uK.',
    'admin',
    'admin@quizmusic.local',
    'rock'
);


-- QUIZ 1: The Doors
INSERT INTO quizzes (id, title, albumCoverUrl, createdBy)
VALUES (1, 'The Doors', '/images/cover/thedoors.jpg', 1);

-- Pytania i odpowiedzi dla The Doors
INSERT INTO questions (id, quizId, text, audioUrl) VALUES
(1, 1, 'Który utwór The Doors otwiera ich debiutancki album?', '/audio/light_my_fire.mp3'),
(2, 1, 'W którym utworze The Doors możemy usłyszeć słowa "Riders on the storm"?', '/audio/riders_on_the_storm.mp3'),
(3, 1, 'Który utwór The Doors pochodzi z albumu "The Doors" i jest jednym z ich pierwszych hitów?', '/audio/soul_kitchen.mp3');

-- Odpowiedzi dla pytań
INSERT INTO answers (id, questionId, text, isCorrect) VALUES
(1, 1, 'Light My Fire', TRUE),
(2, 1, 'Soul Kitchen', FALSE),
(3, 1, 'Riders on the Storm', FALSE),
(4, 1, 'The End', FALSE),

(5, 2, 'Riders on the Storm', TRUE),
(6, 2, 'Light My Fire', FALSE),
(7, 2, 'Soul Kitchen', FALSE),
(8, 2, 'Break on Through', FALSE),

(9, 3, 'Soul Kitchen', TRUE),
(10, 3, 'The End', FALSE),
(11, 3, 'Light My Fire', FALSE),
(12, 3, 'People Are Strange', FALSE);


-- QUIZ 2: Pink Floyd
INSERT INTO quizzes (id, title, albumCoverUrl, createdBy)
VALUES (2, 'Pink Floyd', NULL, 1);

INSERT INTO questions (id, quizId, text, audioUrl) VALUES
(4, 2, 'W którym utworze Pink Floyd znajdziemy charakterystyczny śpiew dzieci w refrenie?', '/audio/another_brick_in_the_wall.mp3'),
(5, 2, 'Który utwór Pink Floyd ma słynną gitarową melodię instrumentalną?', '/audio/any_colour_you_like.mp3'),
(6, 2, 'W którym utworze Pink Floyd czas jest centralnym motywem tekstu?', '/audio/time.mp3');

INSERT INTO answers (id, questionId, text, isCorrect) VALUES
(13, 4, 'Another Brick in the Wall', TRUE),
(14, 4, 'Time', FALSE),
(15, 4, 'Any Colour You Like', FALSE),
(16, 4, 'Comfortably Numb', FALSE),

(17, 5, 'Any Colour You Like', TRUE),
(18, 5, 'Money', FALSE),
(19, 5, 'Time', FALSE),
(20, 5, 'Shine On You Crazy Diamond', FALSE),

(21, 6, 'Time', TRUE),
(22, 6, 'Brain Damage', FALSE),
(23, 6, 'Another Brick in the Wall', FALSE),
(24, 6, 'Us and Them', FALSE);


-- UPDATE KEYS

SELECT setval('quizzes_id_seq', (SELECT MAX(id) FROM quizzes));

SELECT setval('questions_id_seq', (SELECT MAX(id) FROM questions));

SELECT setval('answers_id_seq', (SELECT MAX(id) FROM answers));

SELECT setval('users_id_seq', (SELECT MAX(id) FROM users));
