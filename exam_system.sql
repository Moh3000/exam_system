
DROP DATABASE IF EXISTS exam_system;
create database exam_system;
USE exam_system;


CREATE TABLE Users (
  UserID BIGINT AUTO_INCREMENT PRIMARY KEY,
  Username VARCHAR(200) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('teacher','student') NOT NULL DEFAULT 'student'
 
) ;


CREATE TABLE QuestionBank (
  question_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  teacher_id BIGINT NOT NULL,
  question_text TEXT NOT NULL,
  option_a TEXT,
  option_b TEXT,
  option_c TEXT,
  option_d TEXT,
  correct_option ENUM('A','B','C','D') NOT NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (teacher_id) REFERENCES Users(UserID) ON DELETE CASCADE
) ;


CREATE TABLE Exams (
  ExamID BIGINT AUTO_INCREMENT PRIMARY KEY,
  teacher_id BIGINT NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  time_limit_minutes INT DEFAULT 60,
  status ENUM('draft','published') DEFAULT 'draft',
   random_order TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (teacher_id) REFERENCES Users(UserID) ON DELETE CASCADE
) ;


CREATE TABLE ExamQuestions (
  ExamQuestionID BIGINT AUTO_INCREMENT PRIMARY KEY,
  ExamID BIGINT NOT NULL,
  question_id BIGINT NOT NULL,
  points DECIMAL(6,2) DEFAULT 1.00,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (ExamID) REFERENCES Exams(ExamID) ON DELETE CASCADE,
  FOREIGN KEY (question_id) REFERENCES QuestionBank(question_id) ON DELETE CASCADE,
  UNIQUE (ExamID, question_id)
);


CREATE TABLE StudentExams (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  student_id BIGINT NOT NULL,
  exam_id BIGINT NOT NULL,
  status ENUM('not_started','in_progress','submitted') DEFAULT 'not_started',
  start_time DATETIME NULL,
  submit_time DATETIME NULL,
  score DECIMAL(8,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES Users(UserID) ON DELETE CASCADE,
  FOREIGN KEY (exam_id) REFERENCES Exams(ExamID) ON DELETE CASCADE,
  UNIQUE (student_id, exam_id) 
) ;


CREATE TABLE StudentAnswers (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  student_exam_id BIGINT NOT NULL,
  question_id BIGINT NOT NULL,
  student_answer ENUM('A','B','C','D') NULL,
  is_correct TINYINT(1) DEFAULT NULL, 
  answered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_exam_id) REFERENCES StudentExams(id) ON DELETE CASCADE,
  FOREIGN KEY (question_id) REFERENCES QuestionBank(question_id) ON DELETE CASCADE,
  UNIQUE (student_exam_id, question_id)
);
