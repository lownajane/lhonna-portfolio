CREATE DATABASE IF NOT EXISTS portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portfolio_db;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL
);
CREATE TABLE about_sections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  heading VARCHAR(100) NOT NULL,
  body TEXT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0
);
CREATE TABLE skills (
  id INT AUTO_INCREMENT PRIMARY KEY,
  icon VARCHAR(10) NOT NULL,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(255) NOT NULL,
  category VARCHAR(50) NOT NULL DEFAULT 'Other',
  sort_order INT NOT NULL DEFAULT 0
);
CREATE TABLE projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  category ENUM('web','networking','animation') NOT NULL,
  description TEXT NOT NULL,
  technology VARCHAR(150) NOT NULL,
  image VARCHAR(100) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0
);
CREATE TABLE messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  subject VARCHAR(150) NOT NULL DEFAULT '',
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  is_read TINYINT(1) NOT NULL DEFAULT 0
);

INSERT INTO about_sections (heading, body, sort_order) VALUES
('Background', 'Hello! I''m Lhonna Jane, a student interested in technology, creativity, design, and learning new skills. This portfolio showcases some of my academic work, projects, and personal interests.', 1),
('Education', 'I am currently pursuing my studies in Information Technology. Throughout my studies, I have developed skills in web development, multimedia, animation, networking and computer-related technologies.', 2),
('My Goal', 'My goal is to continue improving myself, gain useful knowledge, skills, and become successful in life while doing something meaningful.', 3),
('Interests', 'My interests include web design, animation, creative projects, technology, music, entertainment, and exploring different cultures.', 4);

INSERT INTO skills (icon, name, description, sort_order) VALUES
('💻','HTML','Creating structured web pages.',1),
('🎨','CSS','Designing and styling websites',2),
('⚡','JavaScript','Adding interaction to websites.',3),
('🖌️','Graphic Design','Creating visual designs and layouts.',4),
('🎬','Animation','Creating animated multimedia projects.',5),
('🤝','Teamwork','Working effectively with others.',6),
('🗣️','Storytelling','I enjoy telling stories and expressing ideas in a creative way.',7),
('👩‍🍳','Cooking','Enjoy preparing different dishes and exploring new recipes and cooking techniques.',8);

INSERT INTO projects (title, category, description, technology, image, sort_order) VALUES
('Ice Cream Advertisement','animation','An animated ice cream advertisement created as a Midterm Project.','Adobe Animate','icecream1.png',1),
('Space-themed Animation','animation','A space-themed animation featuring animated space elements.','Adobe Animate','project2.png',2),
('One-Page Website Activity','web','A skeleton website design.','HTML · CSS','project3.png',3),
('Network Configuration','networking','A networking activity involving network devices, VLANs, switches, and connectivity configuration.','Cisco Packet Tracer','project4.png',4);

CREATE TABLE resume_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  label VARCHAR(50) NOT NULL,
  title VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0
);
INSERT INTO resume_items (label, title, description, sort_order) VALUES
('Education','Bachelor''s Degree in Information Technology','Currently pursuing my studies in Information Technology.',1),
('Activities','Academic Project','Participated in various academic activities involving web development, multimedia, networking, and technology.',2),
('Training','Technology & Digital Skills','Developed practical knowledge through classroom activities, projects, and hands-on exercises.',3),
('Experience','Student Project Experience','Created websites, multimedia projects, advertisements, and networking activities as part of academic requirements.',4);

CREATE TABLE IF NOT EXISTS profile (
  id TINYINT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  tagline VARCHAR(255) NOT NULL DEFAULT '',
  intro TEXT,
  biography TEXT,
  email VARCHAR(150) NOT NULL DEFAULT '',
  facebook VARCHAR(150) NOT NULL DEFAULT '',
  instagram VARCHAR(150) NOT NULL DEFAULT '',
  profile_image VARCHAR(100) NOT NULL DEFAULT 'profile.jpg.webp'
);
INSERT INTO profile (id, full_name, tagline, intro, biography, email, facebook, instagram, profile_image) VALUES
(1, 'Lhonna Jane Dantes Bohol', 'If it''s meant to be, it will be.',
 'Welcome to my personal portfolio website. Here you can learn more about me, my skills, projects, and experiences.',
 'Hello! I''m Lhonna Jane, a student interested in technology, creativity, design, and learning new skills. This portfolio showcases some of my academic work, projects, and personal interests.',
 'lhonnabohol@gmail.com', 'Lhonna Jane Dantes Bohol', 'lhnna_122804', 'profile.jpg.webp');
-- The biography now lives in the profile table, so remove the duplicate About card.
DELETE FROM about_sections WHERE heading = 'Background';

UPDATE skills SET category = 'Technical' WHERE name IN ('HTML', 'CSS', 'JavaScript');
UPDATE skills SET category = 'Creative' WHERE name IN ('Graphic Design', 'Animation');
UPDATE skills SET category = 'Soft skills' WHERE name IN ('Teamwork', 'Storytelling');
