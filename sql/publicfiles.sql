-- Create field to show if file is public

ALTER TABLE files ADD COLUMN `public` tinyint(1) NOT NULL DEFAULT 0;