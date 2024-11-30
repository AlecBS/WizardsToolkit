/*900 rows of wtkRevenue data
AddDate values should be spread between Feb 1st, 2023 and June 11th, 2023
UserUID should all be associated with wtkUsers.UID that have a SecurityLevel of 1 (customers)
EcomUID should be 1, 2 or 3
have all `CurrencyCode` be ‘USD’
PaymentStatus set to ‘Authorized'
GrossAmount values should range from $10 to $120
let’s make the data look like we have 10 products.  Get creative and come up
with 10 products giving them each a unique `ItemName` and price.  Then use
those for the `ItemName` and `GrossAmount` in the wtkRevenue insert.
That way I can show analytic reports regarding which `ItemName` had the most
sales by count and dollar value.
MerchantFee should be calculated as 3% of the `GrossAmount`.  Probably best
done as an UPDATE after INSERT is completed.
the rest of the columns can be filled with fake data*/


-- This will fill the wtkRevenue with 900 rows of data

-- CALL GenerateSales();

DELIMITER //

CREATE PROCEDURE GenerateSales()

BEGIN

    -- Drop the temporary table
    DROP TEMPORARY TABLE IF EXISTS temp_cars;


    -- Create the temporary table for cars
    CREATE TEMPORARY TABLE temp_cars (
        UID INT AUTO_INCREMENT PRIMARY KEY,
        ItemName VARCHAR(120),
        Price DECIMAL(5,2)
    );

    -- Insert car data into the temporary table
    INSERT INTO temp_cars (ItemName, Price) VALUES
        ('1968 Ford Mustang GT', 67.99),
        ('1970 Chevrolet Camaro SS', 24.99),
        ('1957 Chevrolet Bel Air', 89.99),
        ('1969 Dodge Charger R/T', 34.99),
        ('1965 Shelby Cobra 427', 39.99),
        ('1966 Chevrolet Corvette', 77.99),
        ('1970 Plymouth Hemi Cuda', 49.99),
        ('1969 Pontiac GTO', 24.99),
        ('1971 Ford Mustang Mach 1', 29.99),
        ('1970 Chevrolet Chevelle SS', 34.99),
        ('1967 Chevrolet Impala', 9.99),
        ('1969 Chevrolet Camaro Z/28', 34.99),
        ('1967 Shelby GT500', 39.99),
        ('1971 Dodge Challenger R/T', 42.99),
        ('1969 Chevrolet Nova SS', 24.99),
        ('1972 Chevrolet C10 Pickup', 109.99),
        ('1965 Ford Mustang Fastback', 27.99),
        ('1969 Chevrolet Chevelle SS 396', 39.99),
        ('1968 Dodge Charger R/T', 34.99),
        ('1970 Plymouth Road Runner', 49.99),
        ('1967 Chevrolet Camaro SS', 24.99),
        ('1969 Pontiac Firebird Trans Am', 79.99),
        ('1964 Ford Fairlane Thunderbolt', 119.99),
        ('1969 Chevrolet Impala SS 427', 59.99),
        ('1970 Buick GSX', 24.99),
        ('1969 Plymouth Barracuda', 19.99),
        ('1970 Dodge Challenger T/A', 37.99),
        ('1971 Plymouth Duster 340', 29.99),
        ('1968 Pontiac Firebird 400', 39.99),
        ('1965 Chevrolet El Camino', 34.99);

    SET @i = 0;
    SET @userCount = 0;
    SET @carCount = 0;
    SET @randomUserUID = 0;
    SET @randomCarUID = 0;
    SET @randomGrossAmount = 0.0;
    SET @merchantFee = 0.0;

    -- Get the total number of users with SecurityLevel = 1
    SELECT COUNT(*) INTO @userCount FROM wtkUsers WHERE SecurityLevel = 1;

    -- Get the total number of cars in the temporary table
    SELECT COUNT(*) INTO @carCount FROM temp_cars;

    -- Loop to generate 900 sales
    WHILE @i < 900 DO
        -- Randomly select a UserUID with SecurityLevel = 1
        SELECT UID INTO @randomUserUID FROM wtkUsers WHERE SecurityLevel = 1 ORDER BY RAND() LIMIT 1;

        -- Randomly select a car UID from the temporary table
        SELECT UID INTO @randomCarUID FROM temp_cars ORDER BY RAND() LIMIT 1;

        -- Get the car details based on the random car UID
        SELECT ItemName, Price INTO @randomItemName, @randomPrice FROM temp_cars WHERE UID = @randomCarUID;

        -- Generate a random gross amount between $10 and $120 using the car price
        SET @randomGrossAmount = (FLOOR(10 + RAND() * (120 - @randomPrice)) + ROUND(RAND(), 2));

        -- Calculate merchant fee as 3% of the gross amount
        SET @merchantFee = @randomGrossAmount * 0.03;

        -- Insert the generated row into the wtkRevenue table
        INSERT INTO wtkRevenue (AddDate, UserUID, OrderUID, EcomUID, IPAddress, PayerEmail, PayerId, FirstName, LastName, ItemName, GrossAmount, MerchantFee, CurrencyCode, PaymentStatus)
        SELECT
            DATE_ADD('2023-02-01', INTERVAL FLOOR(RAND() * 130) DAY), -- Random date between Feb 1st and June 11th, 2023
            @randomUserUID,
            FLOOR(100000 + RAND() * 900000), -- Random 6-digit number for OrderUID
            FLOOR(1 + RAND() * 3), -- Random number between 1 and 3 for EcomUID
            wtkUsers.IPAddress,
            wtkUsers.Email,
            FLOOR(100000 + RAND() * 900000), -- Random 6-digit number for PayerId
            wtkUsers.FirstName,
            wtkUsers.LastName,
            @randomItemName,
            @randomGrossAmount,
            @merchantFee,
            'USD',
            'Authorized'
        FROM wtkUsers
        WHERE UID = @randomUserUID
        LIMIT 1;

        SET @i = @i + 1;
    END WHILE;

    -- Update the MerchantFee column using an UPDATE statement
    UPDATE wtkRevenue SET MerchantFee = GrossAmount * 0.03;

    -- Drop the temporary table
    DROP TEMPORARY TABLE IF EXISTS temp_cars;
END //

DELIMITER ;
