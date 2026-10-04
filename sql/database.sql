-- CREATE DATABASE student_management;

-- USE student_management;

CREATE TABLE IF NOT EXISTS admins(
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100),
    password VARCHAR(255)
);

INSERT INTO admins(username,password)
VALUES('admin','admin123');

CREATE TABLE IF NOT EXISTS students(
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) UNIQUE,
    full_name VARCHAR(100),
    email VARCHAR(100),
    password VARCHAR(255),
    address TEXT,
    course VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS marks(
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50),
    subject_name VARCHAR(100),
    marks INT
);

CREATE TABLE IF NOT EXISTS attendance(
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50),
    attendance_date DATE,
    status VARCHAR(20)
);
