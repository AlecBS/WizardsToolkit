/* This PROCEDURE generates users and inserts them into the wtkUsers table.
  it is limited to generating 100 users at a time and has the ability to
  accept NULL as the fncStaffRole input. See Example CALLs below:

  3 programmers - SecurityLevel = 99 - StaffRole 'Tech'
    CALL generate_wtkUsers(3, 99, 'Tech');

  1 owner - SecurityLevel = 95 - StaffRole 'Mgr'
    CALL generate_wtkUsers(1, 95, 'Mgr');

  2 managers - SecurityLevel = 80 - StaffRole 'Mgr'
    CALL generate_wtkUsers(2, 80, 'Mgr');

  25 staff - SecurityLevel = 30 - StaffRole 'Mgr'
  CALL generate_wtkUsers(25, 30, 'Emp');

  600 customers - SecurityLevel = 1 - StaffRole NULL (Customers are not considered staff so StaffRole is NULL)
  CALL generate_wtkUsers(100, 1, NULL); -- Run this 6 times to generate 600 users

  NOTE: StaffRole can be found in wtkLookups.LookupValue = 'StaffRole'

  To INSERT New StaffRoles use the below insert

  INSERT INTO `wtkLookups` (`LookupType`, `LookupValue`, `LookupDisplay`)
    VALUES
  ('StaffRole', 'Mgr', 'Manager'),
	('StaffRole', '3 letter value EXAMPLE Mgr', 'Display Name EXAMPLE Manager');

-- CALL generate_wtkUsers(100, 1, NULL);
*/

DROP PROCEDURE IF EXISTS generate_wtkUsers;

DELIMITER //

CREATE PROCEDURE generate_wtkUsers(
  IN Quantity INT,
  IN fncSecurityLevel SMALLINT,
  IN fncStaffRole VARCHAR(4)
)

BEGIN
    -- Drop temporary tables if it exists
    DROP TEMPORARY TABLE IF EXISTS wtkAddresses;
    DROP TEMPORARY TABLE IF EXISTS temp_info;

    -- Create temporary table wtkAddresses
    CREATE TEMPORARY TABLE `wtkAddresses` (
      `UID` int unsigned NOT NULL AUTO_INCREMENT,
      `AddDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `Address` varchar(45) DEFAULT NULL,
      `Address2` varchar(30) DEFAULT NULL,
      `City` varchar(30) DEFAULT NULL,
      `State` varchar(2) DEFAULT NULL,
      `Zipcode` varchar(10) DEFAULT NULL,
      PRIMARY KEY (`UID`)
      );

    -- Fill TEMPORARY TABLE `wtkAddresses`
    INSERT INTO `wtkAddresses` (`Address`, `City`, `State`, `Zipcode`)
      VALUES
      ("8320 Cloud Street", "Laurel", "MD", "20724"),
      ("8820 Vaughn Road", "Montgomery", "AL", "36117"),
      ("2013 Talbot Terrace", "Montgomery", "AL", "36106"),
      ("1106 Commanders Way South", "Annapolis", "MD", "21409"),
      ("150 Meadowview Street", "Marshfield", "MA", "02050"),
      ("19590 East Batavia Drive", "Aurora", "CO", "80011"),
      ("513 10th Street Southeast", "Washington", "AR", "20003"),
      ("2269 Eastern Boulevard", "Montgomery", "AL", "36117"),
      ("2708 Mabel Street", "Berkeley", "CA", "94702"),
      ("18 Hamilton Street Northwest", "Washington", "AR", "20011"),
      ("6802 North 67th Avenue", "Glendale", "AZ", "85301"),
      ("15267 Hesperian Boulevard", "San Leandro", "CA", "94578"),
      ("1145 Chaseway Drive", "Pike Road", "AL", "36064"),
      ("5 Westlund Avenue", "Auburn", "MA", "01501"),
      ("301 Argonaut Street", "Panama City Beach", "FL", "32413"),
      ("77 Gerald Drive", "Manchester", "CT", "06040"),
      ("75 Calfoster Drive", "Wolcott", "VT", "05680"),
      ("12 Winter Street", "Manchester", "CT", "06040"),
      ("2201 Fendall Court", "Crofton", "MD", "21114"),
      ("8642 Yule Street", "Arvada", "CO", "80007"),
      ("9 Kimball Court", "Burlington", "MA", "01803"),
      ("206 Blue Marlin Drive", "Savannah", "GA", "31410"),
      ("517 Wesley Avenue", "Nashville", "TN", "37207"),
      ("905 Richardson Vista Road", "Anchorage", "AK", "99501"),
      ("102 Derondo Street", "Panama City Beach", "FL", "32413"),
      ("1001 6th Street Northwest", "Washington", "AR", "20001"),
      ("1643 North Jordan Lane", "Fayetteville", "AR", "72703"),
      ("5029 Montclair Drive", "Nashville", "TN", "37211"),
      ("5348 Main Street", "Franklin", "VT", "05457"),
      ("6601 North 62nd Avenue", "Glendale", "AZ", "85301"),
      ("1149 Darwin Street", "Savannah", "GA", "31415"),
      ("1903 Bashford Manor Lane", "Louisville", "KY", "40218"),
      ("11933 West 71st Avenue", "Arvada", "CO", "80004"),
      ("43 Westminster Street", "Pittsfield", "MA", "01201"),
      ("1257 John Street", "Nashville", "TN", "37210"),
      ("725 65th Street", "Oakland", "CA", "94609"),
      ("11724 Rushmore", "Oklahoma City", "OK", "73162"),
      ("60 Willow Lakes Drive", "Savannah", "GA", "31419"),
      ("3770 North Front Street", "Fayetteville", "AR", "72703"),
      ("70 East Terrace", "South Burlington", "VT", "05403"),
      ("12 Netherclift Way", "Savannah", "GA", "31411"),
      ("98 Lee Drive", "Annapolis", "MD", "21403"),
      ("106 Camelot Drive", "Plymouth", "MA", "02360"),
      ("222 Plymouth Street", "Middleborough", "MA", "02346"),
      ("7003 9th Street Northwest", "Washington", "AR", "20012"),
      ("2237 Northwest 18th Street", "Oklahoma City", "OK", "73107"),
      ("159 Adams Street", "Manchester", "CT", "06040"),
      ("8772 West 79th Avenue", "Arvada", "CO", "80005"),
      ("539 Palermo Road", "Panama City", "FL", "32405"),
      ("26 Seaman Circle", "Manchester", "CT", "06040"),
      ("173 Spruce Street", "Manchester", "CT", "06040"),
      ("5005 North Miller Avenue", "Oklahoma City", "OK", "73112"),
      ("840 Inglewood Drive", "West Sacramento", "CA", "95605"),
      ("3720 West 86th Avenue", "Anchorage", "AK", "99502"),
      ("906 West Berry Street", "Fayetteville", "AR", "72701"),
      ("23 North Hill Street", "Nashville", "TN", "37210"),
      ("93 Alpine Avenue", "Oak Bluffs", "MA", "02568"),
      ("10725 Sunset Boulevard", "Spencer", "OK", "73084"),
      ("45 Brackett Street", "Quincy", "MA", "02169"),
      ("30451 Servilla Place", "Castaic", "CA", "91384"),
      ("8935 Cole Drive", "Arvada", "CO", "80004"),
      ("6244 Sun River Drive", "Sacramento", "CA", "95824"),
      ("10277 West 52nd Place", "Wheat Ridge", "CO", "80033"),
      ("105 French Run Road", "Savannah", "GA", "31404"),
      ("7202 Jump Street", "Youngstown", "FL", "32466"),
      ("402 Carlton Place", "Goodlettsville", "TN", "37072"),
      ("175 Creek Road", "Castleton", "VT", "05735"),
      ("1901 North Midwest Boulevard", "Edmond", "OK", "73034"),
      ("5113 Southeast 51st Street", "Oklahoma City", "OK", "73135"),
      ("205 North Henderson Bend Road Northwest", "Calhoun", "GA", "30701"),
      ("4800 Huffman Road", "Anchorage", "AK", "99516"),
      ("5936 North 80th Drive", "Glendale", "AZ", "85303"),
      ("553 South Arlington Road", "Orange", "CA", "92869"),
      ("474 Merritt Avenue", "Oakland", "CA", "94610"),
      ("13525 West Stella Lane", "Litchfield Park", "AZ", "85340"),
      ("3114 US Highway 98", "Mexico Beach", "FL", "32456"),
      ("6434 Wright Street", "Arvada", "CO", "80004"),
      ("4035 East 8th Street", "Panama City", "FL", "32404"),
      ("415 West 42nd Street", "Savannah", "GA", "31401"),
      ("1918 Spruce Street", "Boulder", "CO", "80302"),
      ("10332 Thuja Circle", "Anchorage", "AK", "99507"),
      ("12 Kane Road", "Manchester", "CT", "06040"),
      ("627 Emerson Street Northwest", "Washington", "AR", "20011"),
      ("8398 West Denton Lane", "Glendale", "AZ", "85305"),
      ("2305 Ranchland Drive", "Savannah", "GA", "31404"),
      ("7609 Doris Place", "Oklahoma City", "OK", "73162"),
      ("1770 Colony Way", "Fayetteville", "AR", "72704"),
      ("1852 Cherry Road", "Annapolis", "MD", "21409"),
      ("2622 Martin Luther King Junior Boulevard", "Fayetteville", "AR", "72704"),
      ("42 West Louise Street", "Fayetteville", "AR", "72701"),
      ("3607 R Street Northwest", "Washington", "AR", "20007"),
      ("6073 Harlan Street", "Arvada", "CO", "80003"),
      ("3517 S Street Northwest", "Washington", "AR", "20007"),
      ("217 Northeast 1st Street", "Moore", "OK", "73160"),
      ("312 Shepherd Hills Drive", "Nashville", "TN", "37115"),
      ("7728 Twin Oaks Road", "Severn", "MD", "21144"),
      ("35805 Alcazar Court", "Fremont", "CA", "94536"),
      ("30 Windermere Drive", "Agawam", "MA", "01030"),
      ("1311 Elm Hill Pike", "Nashville", "TN", "37210"),
      ("1559 Alabama Avenue Southeast", "Washington", "AR", "20032");



    -- Create temporary table
    CREATE TEMPORARY TABLE temp_info (
      FirstName VARCHAR(50),
      LastName VARCHAR(50),
      Address VARCHAR(100),
      Address2 VARCHAR(20),
      City VARCHAR(50),
      State VARCHAR(50),
      Zipcode VARCHAR(10),
      CountryCode VARCHAR(2),
      Phone VARCHAR(20),
      CellPhone VARCHAR(20),
      Email VARCHAR(100),
      IPAddress VARCHAR(20)
    );

    -- Generate random info and insert into temporary table
    INSERT INTO temp_info (FirstName, LastName, Address, Address2, City, State, Zipcode, CountryCode, Phone, CellPhone, IPAddress)
    SELECT
      generate_fname() AS `FirstName`,
      generate_lname() AS `LastName`,
      address.Address AS `Address`,
      CASE FLOOR(1 + (RAND() * 3))
          WHEN 1 THEN CONCAT('Apt. #', LPAD(FLOOR(RAND() * 1000), 3, '0'))
          WHEN 2 THEN CONCAT('Suite #', LPAD(FLOOR(RAND() * 1000), 3, '0'))
          WHEN 3 THEN NULL
      END AS Address2,
      address.City AS 'City',
      address.State AS 'State',
      address.Zipcode AS 'Zipcode',
      'US' AS `CountryCode`,
      CONCAT('(', ROUND((RAND() * 900) + 100), ') 555-', ROUND((RAND() * 999) + 1000)) AS `Phone`,
      CONCAT('(', ROUND((RAND() * 900) + 100), ') 555-', ROUND((RAND() * 999) + 1000)) AS `CellPhone`,
      CONCAT(
      FLOOR(RAND() * 255), '.',
      FLOOR(RAND() * 255), '.',
      FLOOR(RAND() * 255), '.',
      FLOOR(RAND() * 255)
          ) AS `IPAddress`
    FROM
        (SELECT * FROM wtkAddresses ORDER BY RAND() LIMIT Quantity) AS address;

    -- Update the email column with the generated email using FirstName
    UPDATE temp_info
    SET Email = CONCAT(FirstName, generate_email());

    -- Insert into wtkUsers table with the provided security level and staff role
    INSERT INTO wtkUsers (FirstName, LastName, Address, Address2, City, State, Zipcode, CountryCode, Phone, CellPhone, IPAddress, Email, SecurityLevel, StaffRole)
    SELECT FirstName, LastName, Address, Address2, City, State, Zipcode, CountryCode, Phone, CellPhone, IPAddress, Email, fncSecurityLevel, fncStaffRole
    FROM temp_info;

    -- Select the generated rows.

    SELECT FirstName, LastName, Address, Address2, City, State, Zipcode, CountryCode, Phone, CellPhone, IPAddress, Email, SecurityLevel, StaffRole
    FROM wtkUsers
    WHERE SecurityLevel = fncSecurityLevel AND (StaffRole = fncStaffRole OR fncStaffRole IS NULL)
    ORDER BY UID DESC
    LIMIT Quantity;


    -- Drop the temporary table
    DROP TEMPORARY TABLE IF EXISTS temp_info;
    DROP TEMPORARY TABLE IF EXISTS wtkAddresses;
END //

DELIMITER ;
