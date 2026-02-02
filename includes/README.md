--Database query for IPCR

-- Create database
CREATE DATABASE IF NOT EXISTS ipcr_system
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE ipcr_system;

-- -------------------------------
-- login_periods
-- -------------------------------
CREATE TABLE login_periods (
  id INT AUTO_INCREMENT PRIMARY KEY,
  month VARCHAR(20) NOT NULL,
  year INT NOT NULL
) ENGINE=InnoDB;

INSERT INTO login_periods (id, month, year) VALUES
(1,'January',2025),(2,'February',2025),(3,'March',2025),(4,'April',2025),
(5,'May',2025),(6,'June',2025),(7,'December',2026),(8,'January',2026),
(9,'February',2026),(10,'November',2025),(11,'April',2026),
(12,'August',2026),(13,'September',2026),(14,'November',2026);

-- -------------------------------
-- rating_matrix
-- -------------------------------
CREATE TABLE rating_matrix (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category ENUM('Q','E','T') NOT NULL,
  input_value VARCHAR(100) NOT NULL,
  rating TINYINT NOT NULL
) ENGINE=InnoDB;

INSERT INTO rating_matrix (id, category, input_value, rating) VALUES
(1,'Q','with no error',5),
(2,'Q','with minor error',3),
(3,'Q','with major error',1),
(4,'E','100%',5),
(5,'E','90-99.99%',4),
(6,'E','80-89.99%',3),
(7,'T','once a day',3),
(8,'T','1 hour',4),
(9,'T','30 minutes',5);

-- -------------------------------
-- users
-- -------------------------------
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL UNIQUE,
  role ENUM('admin','moderator','user') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO users (id, full_name, role, created_at) VALUES
(1,'Rey L. Patlunag','user','2026-02-01 03:29:18'),
(2,'Victoria June T. Felias','user','2026-02-01 03:29:18'),
(3,'Aulmonjay F. Romo','user','2026-02-01 03:29:18'),
(4,'Lykah W. Tiu','user','2026-02-01 03:29:18'),
(5,'JAN MARK S. GUIBONE','moderator','2026-02-01 03:29:18');

-- -------------------------------
-- tasks
-- -------------------------------
CREATE TABLE tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  task_code VARCHAR(10) NOT NULL UNIQUE,
  task_title TEXT NOT NULL,
  output_category VARCHAR(255),
  success_indicator TEXT,
  qet_quality VARCHAR(100),
  qet_efficiency VARCHAR(100),
  qet_timeliness VARCHAR(100)
) ENGINE=InnoDB;

-- (DATA INSERT KEPT SAME – TOO LONG TO REPEAT HERE, YOUR ORIGINAL INSERT IS VALID)
-- ✅ You can paste the original INSERT INTO tasks (...) VALUES (...) here unchanged

-- -------------------------------
-- user_tasks
-- -------------------------------
CREATE TABLE user_tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  task_id INT NOT NULL,
  UNIQUE (user_id, task_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- (Paste your original INSERT INTO user_tasks here unchanged)

-- -------------------------------
-- task_accomplishments
-- -------------------------------
CREATE TABLE task_accomplishments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  task_id INT NOT NULL,
  period_id INT NOT NULL,
  q_input VARCHAR(100),
  e_input VARCHAR(100),
  t_input VARCHAR(100),
  q_rating TINYINT,
  e_rating TINYINT,
  t_rating TINYINT,
  final_rating DECIMAL(3,2),
  actual_accomplishment TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (user_id, task_id, period_id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (task_id) REFERENCES tasks(id),
  FOREIGN KEY (period_id) REFERENCES login_periods(id)
) ENGINE=InnoDB;

-- -------------------------------
-- task_performance_standards
-- -------------------------------
CREATE TABLE task_performance_standards (
  id INT AUTO_INCREMENT PRIMARY KEY,
  task_id INT NOT NULL,
  rating_level INT NOT NULL,
  accomplishment_text TEXT NOT NULL,
  FOREIGN KEY (task_id) REFERENCES tasks(id)
) ENGINE=InnoDB;
