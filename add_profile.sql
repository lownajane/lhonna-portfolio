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
