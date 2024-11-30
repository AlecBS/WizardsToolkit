-- Generate a random last names for test data
CREATE OR REPLACE FUNCTION generate_lname()
  RETURNS VARCHAR(255) AS
$$
DECLARE
  lnames VARCHAR[] := ARRAY[
  'Smith','Johnson','Williams','Jones','Brown','Davis','Miller','Wilson','Moore',
  'Taylor','Anderson','Thomas','Jackson','White','Harris','Martin','Thompson',
  'Garcia','Martinez','Robinson','Clark','Rodriguez','Lewis','Lee','Walker',
  'Hall','Allen','Young','Hernandez','King','Wright','Lopez','Hill','Scott',
  'Green','Adams','Baker','Gonzalez','Nelson','Carter','Mitchell','Perez',
  'Roberts','Turner','Phillips','Campbell','Parker','Evans','Edwards',
  'Collins','Stewart','Sanchez','Morris','Rogers','Reed','Cook','Morgan',
  'Bell','Murphy','Bailey','Rivera','Cooper','Richardson','Cox','Howard',
  'Ward','Torres','Peterson','Gray','Ramirez','James','Watson','Brooks',
  'Kelly','Sanders','Price','Bennett','Wood','Barnes','Ross','Henderson',
  'Coleman','Jenkins','Perry','Powell','Long','Patterson','Hughes','Flores',
  'Washington','Butler','Simmons','Foster','Gonzales','Bryant','Alexander',
  'Russell','Griffin','Diaz','Hayes'
  ];
BEGIN
  RETURN lnames[FLOOR(1 + RANDOM() * (ARRAY_LENGTH(lnames, 1) - 1))];
END;
$$
LANGUAGE plpgsql;
