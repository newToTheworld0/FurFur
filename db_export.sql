CREATE TABLE IF NOT EXISTS users (
  id INT NOT NULL AUTO_INCREMENT,
  username VARCHAR(100) NOT NULL UNIQUE,
  contact VARCHAR(150) NOT NULL,
  password VARCHAR(255) NOT NULL,
  PRIMARY KEY (id)
);

SELECT id, username, contact, password FROM users WHERE username = :username LIMIT 1;
SELECT id FROM users WHERE username = :username AND id != :id LIMIT 1;
UPDATE users SET password = :password WHERE id = :id;
UPDATE users SET contact = :contact WHERE id = :id;
DELETE FROM users WHERE id = :id;
You sent
CREATE DATABASE IF NOT EXISTS schooldata;
USE schooldata;

CREATE TABLE IF NOT EXISTS records (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(150) NOT NULL,
  age INT NOT NULL,
  socsci DECIMAL(5,2) NOT NULL,
  csac DECIMAL(5,2) NOT NULL,
  cc DECIMAL(5,2) NOT NULL,
  cspl DECIMAL(5,2) NOT NULL,
  csos DECIMAL(5,2) NOT NULL,
  csse DECIMAL(5,2) NOT NULL,
  cswebsys DECIMAL(5,2) NOT NULL,
  PRIMARY KEY (id)
);

INSERT INTO records (name, age, socsci, csac, cc, cspl, csos, csse, cswebsys)
VALUES (:name, :age, :socsci, :csac, :cc, :cspl, :csos, :csse, :cswebsys);

CREATE TABLE post_likes (
    id INT NOT NULL AUTO_INCREMENT,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_post_user (post_id, user_id),
    INDEX idx_post_id (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE post_comments (
    id INT NOT NULL AUTO_INCREMENT,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_post_id (post_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE post_bookmarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_bookmark (user_id, post_id)
);