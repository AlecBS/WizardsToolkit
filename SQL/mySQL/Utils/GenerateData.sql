-- ---------------------------------------------------------------------------------------------------------------------
-- Example of creating demo customers data
INSERT INTO `customers` (`name`, `email`, `altemails`, `phone`, `mobile`, `altphones`)
SELECT CONCAT(generate_fname(), ' ', generate_lname()),
	CONCAT(generate_fname(), ROUND((RAND()*900)), generate_email()),
	CONCAT(generate_fname(), generate_email()),
	CONCAT('(',ROUND((RAND()*900)+100),') 555-', ROUND((RAND()*999)+1000)),
	CONCAT('(',ROUND((RAND()*900)+100),') 555-', ROUND((RAND()*999)+1000)),
	CONCAT('(',ROUND((RAND()*900)+100),') 555-', ROUND((RAND()*999)+1000))
 FROM `wtkLookups`
 LIMIT 333;

SELECT * FROM `customers`
-- ---------------------------------------------------------------------------------------------------------------------

-- Example of updating all phone numbers and changing them to random numbers
UPDATE employee
SET contact_phone = CONCAT('(',ROUND((RAND()*900)+100),') 555-',
  ROUND((RAND()*999)+1000))
WHERE id > 0;

-- Example of filling data with fake W9 numbers
UPDATE employee
SET  w9_number = CONCAT(ROUND((RAND()*99)+1),'-',ROUND((RAND()*999999)+100))
WHERE id > 0;

-- Example of filling data with fake insurance certificate accounts
UPDATE employee
SET  insurance_certificate_number = concat(substring('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', rand()*36+1, 1),
              substring('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', rand()*36+1, 1),
              substring('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', rand()*36+1, 1),
              substring('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', rand()*36+1, 1),
              substring('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', rand()*36+1, 1),
              substring('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', rand()*36+1, 1),
              substring('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', rand()*36+1, 1),
              substring('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', rand()*36+1, 1)
             )
WHERE id > 0;
