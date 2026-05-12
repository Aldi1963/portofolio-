-- =============================================================
-- Migration 001: Fix duplicate skill rows
-- =============================================================
-- Problem : Running install.php more than once caused multiple
--           identical rows in the `skills` table (e.g. JavaScript x4,
--           MySQL x3) because `skills.name` had no UNIQUE constraint
--           so `INSERT IGNORE` could not deduplicate.
--
-- Fix     : 1) Delete duplicate rows keeping the lowest id for each
--              skill name.
--           2) Add a UNIQUE KEY on `name` so future inserts with the
--              same name are rejected (and `INSERT IGNORE` works as
--              intended).
--
-- Safe to run multiple times (idempotent).
-- =============================================================

-- 1. Remove duplicates (keep the earliest inserted row per name)
DELETE s1 FROM `skills` s1
INNER JOIN `skills` s2
    ON s1.`name` = s2.`name`
    AND s1.`id`  > s2.`id`;

-- 2. Add UNIQUE key. We wrap in a procedure because MySQL does not
--    support `ADD UNIQUE KEY IF NOT EXISTS` directly before 8.0.
DROP PROCEDURE IF EXISTS add_unique_skills_name;
DELIMITER $$
CREATE PROCEDURE add_unique_skills_name()
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'skills'
          AND INDEX_NAME   = 'uk_skills_name'
    ) THEN
        ALTER TABLE `skills` ADD UNIQUE KEY `uk_skills_name` (`name`);
    END IF;
END$$
DELIMITER ;

CALL add_unique_skills_name();
DROP PROCEDURE add_unique_skills_name;
