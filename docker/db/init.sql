CREATE TYPE user_role AS ENUM ('user', 'admin');

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role user_role NOT NULL DEFAULT 'user'
);


CREATE TABLE user_details (
    user_id INT PRIMARY KEY,
    email VARCHAR(255),
    favorite_genre VARCHAR(50),

    CONSTRAINT fk_user_details_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE TABLE quizzes (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    album_cover_url VARCHAR(500),
    created_by INT NOT NULL,

    CONSTRAINT fk_quiz_user
        FOREIGN KEY (created_by)
        REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE TABLE questions (
    id SERIAL PRIMARY KEY,
    quiz_id INT NOT NULL,
    text VARCHAR(1000) NOT NULL,
    audio_url VARCHAR(500),

    CONSTRAINT fk_question_quiz
        FOREIGN KEY (quiz_id)
        REFERENCES quizzes(id)
        ON DELETE CASCADE
);

CREATE TABLE answers (
    id SERIAL PRIMARY KEY,
    question_id INT NOT NULL,
    text VARCHAR(1000) NOT NULL,
    is_correct BOOLEAN NOT NULL DEFAULT FALSE,

    CONSTRAINT fk_answer_question
        FOREIGN KEY (question_id)
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
    INSERT INTO users (username, password_hash, role)
    VALUES (p_username, p_password_hash, p_role)
    RETURNING id INTO v_user_id;

    INSERT INTO user_details (user_id, email, favorite_genre)
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
    ud.favorite_genre,
    COALESCE(SUM(qr.score), 0) AS total_score
FROM users u
LEFT JOIN user_details ud ON u.id = ud.user_id
LEFT JOIN quiz_results qr ON u.id = qr.user_id
GROUP BY u.id, u.username, u.role, ud.email, ud.favorite_genre
ORDER BY total_score DESC;

CREATE OR REPLACE VIEW quiz_content AS
SELECT
    q.id AS quiz_id,
    q.title AS quiz_title,
    q.album_cover_url AS quiz_cover,
    q.created_by AS owner_id,

    qs.id AS question_id,
    qs.text AS question_text,
    qs.audio_url AS question_audio,

    a.id AS answer_id,
    a.text AS answer_text,
    a.is_correct AS answer_correct
FROM quizzes q
LEFT JOIN questions qs ON qs.quiz_id = q.id
LEFT JOIN answers a ON a.question_id = qs.id
ORDER BY qs.id, a.id;

CREATE OR REPLACE VIEW quiz_preview AS
SELECT 
    q.id,
    q.title,
    q.album_cover_url,
    u.username AS created_by_username
FROM quizzes q
JOIN users u ON u.id = q.created_by
ORDER BY q.id DESC;

-- TRIGGERS

CREATE OR REPLACE FUNCTION check_quiz_result()
RETURNS TRIGGER AS $$
DECLARE
    number_of_questions INT;
BEGIN
    SELECT COUNT(*) INTO number_of_questions
    FROM questions
    WHERE quiz_id = NEW.quiz_id;

    IF (NEW.correct_answers + NEW.incorrect_answers) != number_of_questions THEN
        RAISE EXCEPTION
            'The sum of correct answers and incorrect answers (% + %) does not match the number of questions in the quiz (%)',
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

SELECT create_user(
    'user',
    '$2a$12$9mWIpC1avGSNX7gtgA7EyeVpFTUAJ90Zng6A9yIEG9F1iPlwW6uK.',
    'user',
    'user@quizmusic.local',
    'rock'
);

INSERT INTO quizzes (id, title, album_cover_url, created_by)
VALUES (1, 'The Doors', '/images/cover/thedoors.jpg', 1);
INSERT INTO quizzes (id, title, album_cover_url, created_by)
VALUES (2, 'Pink Floyd', NULL, 1);

INSERT INTO questions (id, quiz_id, text, audio_url) VALUES
(1, 1, 'W którym utworze The Doors solo organowe Raya Manzarka zostało oparte na strukturze barokowej, inspirowanej Bachem?', '/audio/light_my_fire.mp3'),
(2, 1, 'Który utwór był ostatnim nagranym wspólnie przez czterech członków zespołu?', '/audio/riders_on_the_storm.mp3'),
(3, 1, 'Który utwór jest hołdem dla restauracji "Olivia’s" w Venice Beach, gdzie Jim Morrison często przesiadywał do późnej nocy?', '/audio/soul_kitchen.mp3'),

(4, 2, 'W którym utworze wykorzystano chór uczniów z Islington Green School, co wywołało skandal w ówczesnym brytyjskim systemie szkolnictwa?', '/audio/another_brick_in_the_wall.mp3'),
(5, 2, 'Który instrumentalny utwór z albumu "Dark Side of the Moon" jest pokazem możliwości syntezatora VCS3 i efektów Uni-Vibe Davida Gilmoura?', '/audio/any_colour_you_like.mp3'),
(6, 2, 'Który utwór otwiera słynna kakofonia zegarów, nagrana przez Alana Parsonsa w antykwariacie przy użyciu techniki quadrophonic?', '/audio/time.mp3');

INSERT INTO answers (id, question_id, text, is_correct) VALUES
(1, 1, 'Riders on the Storm', FALSE),
(2, 1, 'Soul Kitchen', FALSE),
(3, 1, 'Light My Fire', TRUE),
(4, 1, 'The End', FALSE),

(5, 2, 'Riders on the Storm', TRUE),
(6, 2, 'Light My Fire', FALSE),
(7, 2, 'Soul Kitchen', FALSE),
(8, 2, 'Break on Through', FALSE),

(9, 3, 'People Are Strange', FALSE),
(10, 3, 'The End', FALSE),
(11, 3, 'Light My Fire', FALSE),
(12, 3, 'Soul Kitchen', TRUE),

(13, 4, 'Another Brick in the Wall', TRUE),
(14, 4, 'Time', FALSE),
(15, 4, 'Any Colour You Like', FALSE),
(16, 4, 'Comfortably Numb', FALSE),

(17, 5, 'Any Colour You Like', TRUE),
(18, 5, 'Money', FALSE),
(19, 5, 'Time', FALSE),
(20, 5, 'Shine On You Crazy Diamond', FALSE),

(21, 6, 'People Are Strange', FALSE),
(22, 6, 'Brain Damage', FALSE),
(23, 6, 'Another Brick in the Wall', FALSE),
(24, 6, 'Time', TRUE);


-- UPDATE KEYS

SELECT setval('quizzes_id_seq', (SELECT MAX(id) FROM quizzes));

SELECT setval('questions_id_seq', (SELECT MAX(id) FROM questions));

SELECT setval('answers_id_seq', (SELECT MAX(id) FROM answers));

SELECT setval('users_id_seq', (SELECT MAX(id) FROM users));
