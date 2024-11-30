-- Generate a random first names  for test data
CREATE OR REPLACE FUNCTION generate_fname()
  RETURNS VARCHAR(255) AS
$$
DECLARE
  fnames VARCHAR[] := ARRAY[
    'James', 'Mary', 'John', 'Patricia', 'Robert', 'Linda', 'Michael', 'Barbara', 'William', 'Elizabeth',
    'David', 'Jennifer', 'Richard', 'Maria', 'Charles', 'Susan', 'Joseph', 'Margaret', 'Thomas', 'Dorothy',
    'Christopher', 'Lisa', 'Daniel', 'Nancy', 'Paul', 'Karen', 'Mark', 'Betty', 'Donald', 'Helen',
    'George', 'Sandra', 'Kenneth', 'Donna', 'Steven', 'Carol', 'Edward', 'Ruth', 'Brian', 'Sharon',
    'Ronald', 'Michelle', 'Anthony', 'Laura', 'Kevin', 'Sarah', 'Jason', 'Kimberly', 'Matthew', 'Deborah',
    'Gary', 'Jessica', 'Timothy', 'Shirley', 'Jose', 'Cynthia', 'Larry', 'Angela', 'Jeffrey', 'Melissa',
    'Frank', 'Brenda', 'Scott', 'Amy', 'Eric', 'Anna', 'Stephen', 'Rebecca', 'Andrew', 'Virginia',
    'Raymond', 'Kathleen', 'Gregory', 'Pamela', 'Joshua', 'Martha', 'Jerry', 'Debra', 'Dennis', 'Amanda',
    'Walter', 'Stephanie', 'Patrick', 'Carolyn', 'Peter', 'Christine', 'Harold', 'Marie', 'Douglas', 'Janet',
    'Henry', 'Catherine', 'Carl', 'Frances', 'Arthur', 'Ann', 'Ryan', 'Joyce', 'Roger', 'Diane'
  ];
BEGIN
  RETURN fnames[FLOOR(1 + RANDOM() * (ARRAY_LENGTH(fnames, 1) - 1))];
END;
$$
LANGUAGE plpgsql;
