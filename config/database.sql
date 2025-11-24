CREATE DATABASE IF NOT EXISTS foro_completo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE foro_completo;

-------------------------------------------------------
-- 1. TABLAS SIN FOREIGN KEYS
-------------------------------------------------------

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    real_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    user_id INT NOT NULL,
    content VARCHAR(500) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_path VARCHAR(300) NOT NULL,
    question_id INT DEFAULT NULL,
    answer_id INT DEFAULT NULL,
    CONSTRAINT check_attachment_target CHECK (
        (question_id IS NOT NULL AND answer_id IS NULL) OR 
        (question_id IS NULL AND answer_id IS NOT NULL)
    )
) ENGINE=InnoDB;

CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    question_id INT DEFAULT NULL,
    answer_id INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT check_like_target CHECK (
        (question_id IS NOT NULL AND answer_id IS NULL) OR 
        (question_id IS NULL AND answer_id IS NOT NULL)
    )
) ENGINE=InnoDB;

CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE question_tags (
    question_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (question_id, tag_id)
) ENGINE=InnoDB;

CREATE TABLE friend_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requester_id INT NOT NULL,
    requested_id INT NOT NULL,
    status ENUM('pending','accepted','rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE friendships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user1_id INT NOT NULL,
    user2_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-------------------------------------------------------
-- 2. FOREIGN KEYS SIN CASCADE
-------------------------------------------------------

ALTER TABLE questions
ADD CONSTRAINT fk_questions_user
    FOREIGN KEY (user_id) REFERENCES users(id);

ALTER TABLE answers
ADD CONSTRAINT fk_answers_question
    FOREIGN KEY (question_id) REFERENCES questions(id),
ADD CONSTRAINT fk_answers_user
    FOREIGN KEY (user_id) REFERENCES users(id);

ALTER TABLE attachments
ADD CONSTRAINT fk_attach_question
    FOREIGN KEY (question_id) REFERENCES questions(id),
ADD CONSTRAINT fk_attach_answer
    FOREIGN KEY (answer_id) REFERENCES answers(id);

ALTER TABLE likes
ADD CONSTRAINT fk_likes_user
    FOREIGN KEY (user_id) REFERENCES users(id),
ADD CONSTRAINT fk_likes_question
    FOREIGN KEY (question_id) REFERENCES questions(id),
ADD CONSTRAINT fk_likes_answer
    FOREIGN KEY (answer_id) REFERENCES answers(id);

ALTER TABLE question_tags
ADD CONSTRAINT fk_qtags_question
    FOREIGN KEY (question_id) REFERENCES questions(id),
ADD CONSTRAINT fk_qtags_tag
    FOREIGN KEY (tag_id) REFERENCES tags(id);

ALTER TABLE friend_requests
ADD CONSTRAINT fk_req_requester
    FOREIGN KEY (requester_id) REFERENCES users(id),
ADD CONSTRAINT fk_req_requested
    FOREIGN KEY (requested_id) REFERENCES users(id);

ALTER TABLE friendships
ADD CONSTRAINT fk_friend_user1
    FOREIGN KEY (user1_id) REFERENCES users(id),
ADD CONSTRAINT fk_friend_user2
    FOREIGN KEY (user2_id) REFERENCES users(id);

ALTER TABLE messages
ADD CONSTRAINT fk_msg_sender
    FOREIGN KEY (sender_id) REFERENCES users(id),
ADD CONSTRAINT fk_msg_receiver
    FOREIGN KEY (receiver_id) REFERENCES users(id);
