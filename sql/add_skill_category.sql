ALTER TABLE skills ADD COLUMN category VARCHAR(50) NOT NULL DEFAULT 'Other' AFTER description;
UPDATE skills SET category = 'Technical' WHERE name IN ('HTML', 'CSS', 'JavaScript');
UPDATE skills SET category = 'Creative' WHERE name IN ('Graphic Design', 'Animation');
UPDATE skills SET category = 'Soft skills' WHERE name IN ('Teamwork', 'Storytelling');
