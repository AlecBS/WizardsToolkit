-- List all tables in a database that have a data column name with a specific name
SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_TYPE
FROM information_schema.columns
WHERE TABLE_SCHEMA = 'wiztools'
  AND COLUMN_NAME = 'IPaddress'
  AND COLUMN_TYPE != 'varchar(40)'
ORDER BY TABLE_SCHEMA ASC, TABLE_NAME ASC;

-- Create ALTER COLUMN scripts to change a column for several tables in several databases.
-- For example if you want increase length to VARCHAR(40), then you could write this:
SELECT CONCAT('ALTER TABLE `', TABLE_SCHEMA, '`.`', TABLE_NAME, '` CHANGE `IpAddress`',
      ' `IpAddress` VARCHAR(40) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;') AS `Scripts`
FROM information_schema.columns
WHERE COLUMN_NAME = 'IPaddress'
  AND COLUMN_TYPE != 'varchar(40)'
ORDER BY TABLE_SCHEMA ASC, TABLE_NAME ASC;
