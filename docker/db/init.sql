CREATE TYPE user_role AS ENUM ('user', 'admin');

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    passwordHash VARCHAR(255) NOT NULL,
    role user_role NOT NULL DEFAULT 'user'
);

INSERT INTO users (username, passwordHash, role)
VALUES (
    'admin',
    '$2a$12$9mWIpC1avGSNX7gtgA7EyeVpFTUAJ90Zng6A9yIEG9F1iPlwW6uK.',
    'admin'
);