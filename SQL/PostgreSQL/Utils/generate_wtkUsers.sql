CREATE OR REPLACE FUNCTION "generate_wtkUsers"(
  "fncQuantity" INT,
  "fncSecurityLevel" SMALLINT,
  "fncStaffRole" VARCHAR(4)
)
RETURNS TABLE (
  "FirstName" VARCHAR(50),
  "LastName" VARCHAR(50),
  "Address" VARCHAR(100),
  "Address2" VARCHAR(20),
  "City" VARCHAR(50),
  "State" VARCHAR(50),
  "Zipcode" VARCHAR(10),
  "CountryCode" CHAR(2),
  "Phone" VARCHAR(20),
  "CellPhone" VARCHAR(20),
  "Email" VARCHAR(100),
  "IPAddress" VARCHAR(20),
  "SecurityLevel" SMALLINT,
  "StaffRole" VARCHAR(4)
)
AS $$
BEGIN
    -- Drop temporary tables if they exist
    DROP TABLE IF EXISTS "wtkAddresses";
    DROP TABLE IF EXISTS "temp_info";

    -- Create temporary table "wtkAddresses"
    CREATE TEMPORARY TABLE "wtkAddresses" (
      "UID" SERIAL PRIMARY KEY,
      "AddDate" timestamp DEFAULT CURRENT_TIMESTAMP,
      "Address" varchar(45),
      "Address2" varchar(30),
      "City" varchar(30),
      "State" varchar(2),
      "Zipcode" varchar(10)
    );

    -- Fill temporary table "wtkAddresses"
    INSERT INTO "wtkAddresses" ("Address", "City", "State", "Zipcode")
    VALUES
      ('8320 Cloud Street', 'Laurel', 'MD', '20724'),
      ('8820 Vaughn Road', 'Montgomery', 'AL', '36117'),
      ('2013 Talbot Terrace', 'Montgomery', 'AL', '36106'),
      ('1106 Commanders Way South', 'Annapolis', 'MD', '21409');

    -- Create temporary table "temp_info"
    CREATE TEMPORARY TABLE "temp_info" (
      "tFirstName" VARCHAR(50),
      "tLastName" VARCHAR(50),
      "tAddress" VARCHAR(100),
      "tAddress2" VARCHAR(20),
      "tCity" VARCHAR(50),
      "tState" VARCHAR(50),
      "tZipcode" VARCHAR(10),
      "tCountryCode" CHAR(2) DEFAULT 'US',
      "tPhone" VARCHAR(20),
      "tCellPhone" VARCHAR(20),
      "tEmail" VARCHAR(100),
      "tIPAddress" VARCHAR(20)
    );

    -- Generate random info and insert into temporary table
    INSERT INTO "temp_info" ("tFirstName", "tLastName", "tAddress", "tAddress2", "tCity", "tState", "tZipcode", "tPhone", "tCellPhone", "tIPAddress")
    SELECT
      "generate_fname"() AS "FirstName",
      "generate_lname"() AS "LastName",
      address."Address" AS "Address",
      CASE FLOOR(1 + (random() * 3))
          WHEN 1 THEN CONCAT('Apt. #', LPAD(cast(FLOOR(random() * 1000) AS varchar(3)), 3, '0'))
          WHEN 2 THEN CONCAT('Suite #', LPAD(cast(FLOOR(random() * 1000) AS varchar(3)), 3, '0'))
          WHEN 3 THEN NULL
      END AS "Address2",
      address."City" AS "City",
      address."State" AS "State",
      address."Zipcode" AS "Zipcode",
      CONCAT('(', ROUND((random() * 900) + 100), ') 555-', ROUND((random() * 999) + 1000)) AS "Phone",
      CONCAT('(', ROUND((random() * 900) + 100), ') 555-', ROUND((random() * 999) + 1000)) AS "CellPhone",
      CONCAT(
        FLOOR(random() * 255), '.',
        FLOOR(random() * 255), '.',
        FLOOR(random() * 255), '.',
        FLOOR(random() * 255)
      ) AS "IPAddress"
    FROM
        (SELECT * FROM "wtkAddresses" ORDER BY RANDOM() LIMIT "fncQuantity") AS address;

    -- Update the email column with the generated email using FirstName
    UPDATE "temp_info"
    SET "tEmail" = CONCAT("tFirstName", "generate_email"());

    -- Insert into wtkUsers table with the provided security level and staff role
    INSERT INTO "wtkUsers" ("FirstName", "LastName", "Address", "Address2", "City", "State", "Zipcode", "CountryCode", "Phone", "CellPhone", "IPAddress", "Email", "SecurityLevel", "StaffRole")
    SELECT "tFirstName", "tLastName", "tAddress", "tAddress2", "tCity", "tState", "tZipcode", "tCountryCode", "tPhone", "tCellPhone", "tIPAddress", "tEmail", "fncSecurityLevel", "fncStaffRole"
    FROM "temp_info";

    -- Select the generated rows
    RETURN QUERY
    SELECT "tFirstName", "tLastName", "tAddress", "tAddress2", "tCity", "tState", "tZipcode", "tCountryCode", "tPhone", "tCellPhone", "tIPAddress", "tEmail", "fncSecurityLevel", "fncStaffRole"
    FROM "temp_info";

    -- Drop the temporary tables
    DROP TABLE IF EXISTS "temp_info";
    DROP TABLE IF EXISTS "wtkAddresses";
END;
$$ LANGUAGE plpgsql;
