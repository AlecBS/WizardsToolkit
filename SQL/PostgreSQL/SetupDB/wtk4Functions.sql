CREATE OR REPLACE FUNCTION "fncOnlyDigits"(fncVal VARCHAR(120))
  RETURNS VARCHAR(40) AS $fncResult$
  -- Example usage:
  -- SELECT "fncOnlyDigits"('(209) 555-8765cell') AS "PhoneReadyForSMS";
    DECLARE fncReturn VARCHAR(24) := '';
    DECLARE fncStrLen SMALLINT;
    DECLARE fncCntr   SMALLINT;
    DECLARE fncChar   CHAR(1);
BEGIN

    -- shortcut exit for special cases
    IF (COALESCE(fncVal,'') <> '') THEN
      -- initialize for loop
      SELECT CHAR_LENGTH(fncVal) INTO fncStrLen;

      FOR fncCntr IN 1 .. fncStrLen BY 1 LOOP
          SELECT SUBSTRING(fncVal,fncCntr, 1) INTO fncChar;
          IF fncChar IN ('0','1','2','3','4','5','6','7','8','9') THEN
             SELECT CONCAT(fncReturn, fncChar) INTO fncReturn;
          END IF;
      END LOOP;

    END IF;
    RETURN fncReturn;
END
$fncResult$ LANGUAGE plpgsql;

--SELECT "fncWordCaps"('this IS my tEst!') as "Result";
CREATE OR REPLACE FUNCTION "fncWordCaps"(fncText VARCHAR(255))
  RETURNS VARCHAR(255) AS $fncText$
    DECLARE fncStrLen SMALLINT;
    DECLARE fncCntr   SMALLINT;
BEGIN

	SELECT CHAR_LENGTH(fncText) INTO fncStrLen;
	SELECT LOWER(fncText) INTO fncText;

    FOR fncCntr IN 1 .. fncStrLen BY 1 LOOP
        IF (fncCntr = 1) THEN
            SELECT CONCAT(
                UPPER(SUBSTRING(fncText,1,1)),
                SUBSTRING(fncText,2, fncStrLen - 1)
            ) INTO fncText;
        END IF;
        IF (SUBSTRING(fncText,fncCntr,1) = ' ') THEN
            IF (fncCntr < fncStrLen) THEN
                SELECT CONCAT(
                    LEFT(fncText,fncCntr),
                    UPPER(SUBSTRING(fncText,fncCntr + 1,1)),
                    RIGHT(fncText, fncStrLen - fncCntr - 1)
                ) INTO fncText;
            END IF;
        END IF;
    END LOOP;

	RETURN fncText;
END
$fncText$ LANGUAGE plpgsql;


-- SELECT "fncCalcDistance"(37.650432,-120.99584,37.4862252,-120.8716269) AS "Distance";
CREATE OR REPLACE FUNCTION "fncCalcDistance"(
        fncFromLat decimal(24,7), fncFromLong decimal(24,7),
        fncToLat decimal(24,7), fncToLong decimal(24,7)
    ) RETURNS decimal(24,7) AS $fncResult$
     DECLARE fncDistance decimal(24,7);
BEGIN
	fncDistance := (SELECT ((ACOS(SIN(fncFromLat * PI() / 180) * SIN(fncToLat * PI() / 180) +
				COS(fncFromLat * PI() / 180) * COS(fncToLat * PI() / 180) * COS((fncFromLong - fncToLong) * PI() / 180)) * 180 / PI()) * 60 * 1.1515));

    RETURN fncDistance;
END
$fncResult$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "fncFormatPhone"(fncOrigPhone VARCHAR(30))
  RETURNS VARCHAR(15) AS $fncResult$
  /*
  Example usage:

  SELECT fncFormatPhone("Phone");
  Result:  (012) 345-6789
  */
    -- Declare variables
    DECLARE fncDigits VARCHAR(24);
    DECLARE fncResult VARCHAR(15);
BEGIN
    -- Initialize variables
    fncDigits := "fncOnlyDigits"(fncOrigPhone);

    fncResult := CONCAT('(',SUBSTRING(fncDigits, 1, 3), ') ', SUBSTRING(fncDigits, 4,3), '-', SUBSTRING(fncDigits,7,4));
    RETURN fncResult;
END
$fncResult$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION "fncContactIcons"(
        fncEmail VARCHAR(80), fncPhone VARCHAR(80), fncLat DECIMAL(20,14), fncLong DECIMAL(20,14),
        fncShowPhone CHAR(1), fncUserUID INT, fncCanSMS CHAR(1), fncModalEmail CHAR(1), fncEmailCode VARCHAR(10)
        ) RETURNS varchar(800) AS $fncResult$
    DECLARE fncHTM VARCHAR(800) := '';
    /*
    Example usage:
SELECT "FirstName", "fncContactIcons"("Email", "Phone", NULL, NULL, 'Y',"UID","SMSEnabled",'N','')
  FROM "wtkUsers"
ORDER BY "UID" DESC;
    */
    BEGIN
        IF COALESCE(fncEmail,'') <> '' THEN
            IF (fncModalEmail = 'Y') THEN
                fncHTM := CONCAT('<a onclick="wtkModal(''/wtk/emailModal'',''', fncEmailCode, ''',',fncUserUID, ');"><i class="material-icons tiny">contact_mail</i></a> ');
            ELSE
                fncHTM := CONCAT('<a href="mailto:', fncEmail,'"><i class="material-icons tiny">email</i></a> ');
            END IF;
        END IF;
        IF COALESCE(fncPhone,'') <> '' THEN
            IF (fncCanSMS = 'Y') THEN
                fncHTM := CONCAT(fncHTM,'<a onclick="wtkModal(''/wtk/smsModal'',''sms'',', fncUserUID,',''', "fncOnlyDigits"(fncPhone), ''');"',
                    '><i class="material-icons tiny">sms</i></a> ');
            END IF;

            IF (fncShowPhone = 'Y') THEN
                fncHTM = CONCAT(fncHTM,'<a onclick="JavaScript:wtkDialPhone(', "fncOnlyDigits"(fncPhone),
                ')"><i class="material-icons tiny">contact_phone</i></a> ',
                "fncFormatPhone"("fncOnlyDigits"(fncPhone)),' ');
            ELSE
                fncHTM = CONCAT(fncHTM,'<a onclick="JavaScript:wtkDialPhone(', "fncOnlyDigits"(fncPhone),
                ')"><i class="material-icons tiny">contact_phone</i></a> ');
            END IF;
        END IF;

        IF (COALESCE(fncLat,0) <> 0) THEN
            fncHTM := CONCAT(fncHTM,'<a href="http://www.google.com/maps/?q=',
              fncLat,',', fncLong, '" target="_blank"><i class="material-icons tiny">place</i></a> ');
        END IF;
        RETURN fncHTM;
    END
$fncResult$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION "st_LanguageSwap"(
    fncLang         char(3),
    fncPrimaryText  varchar(120))
  RETURNS VARCHAR(240) AS $fncNewText$
    DECLARE fncCount INT;
    DECLARE fncNewText VARCHAR(240);
BEGIN
-- find language equivalent for passed text
  SELECT COUNT(*) INTO fncCount FROM "wtkLanguage" WHERE "PrimaryText" = fncPrimaryText AND "Language" = fncLang AND "NewText" IS NOT NULL;

  IF (fncCount > 0) THEN
      SELECT "NewText" INTO fncNewText FROM "wtkLanguage" WHERE "PrimaryText" = fncPrimaryText AND "Language" = fncLang;
  ELSE
      fncNewText := fncPrimaryText;
      IF ((SELECT COUNT(*) FROM "wtkLanguage" WHERE "PrimaryText" = fncPrimaryText AND "Language" = fncLang AND "NewText" IS NULL ) = 0) THEN
          INSERT INTO "wtkLanguage" ("Language", "PrimaryText") VALUES (fncLang, fncPrimaryText);
      END IF;
  END IF;
  RETURN fncNewText;
END
$fncNewText$ LANGUAGE plpgsql;


CREATE FUNCTION CRC32(text_string text) RETURNS bigint AS $$
DECLARE
    tmp bigint;
    i int;
    j int;
    byte_length int;
    binary_string bytea;
BEGIN
    IF text_string = '' THEN
        RETURN 0;
    END IF;

    i = 0;
    tmp = 4294967295;
    byte_length = bit_length(text_string) / 8;
    binary_string = decode(replace(text_string, E'\\\\', E'\\\\\\\\'), 'escape');
    LOOP
        tmp = (tmp # get_byte(binary_string, i))::bigint;
        i = i + 1;
        j = 0;
        LOOP
            tmp = ((tmp >> 1) # (3988292384 * (tmp & 1)))::bigint;
            j = j + 1;
            IF j >= 8 THEN
                EXIT;
            END IF;
        END LOOP;
        IF i >= byte_length THEN
            EXIT;
        END IF;
    END LOOP;
    RETURN (tmp # 4294967295);
END
$$ IMMUTABLE LANGUAGE plpgsql;

-- Usage:  SELECT * FROM make_widget('1');
-- used to create widgets showing summary statistics
CREATE OR REPLACE FUNCTION make_widget(fncDays smallint)
	RETURNS TABLE("Description" varchar(40), "Count" int)
AS
$procedure$
    DECLARE
        fncuid integer := 0;
	BEGIN
		CREATE TEMPORARY TABLE IF NOT EXISTS "tmpResults" (
			"UID"      	SERIAL PRIMARY KEY,
			"tDescrip"	varchar(40),
			"tCount"	int
		);
		DELETE FROM "tmpResults";

		-- Number of Users logged in, Page Views, Updates, Reports Viewed
		INSERT INTO "tmpResults" ("tDescrip", "tCount")
		  SELECT 'Number of Users logged in', COUNT(DISTINCT("UserUID"))
  		  FROM "wtkLoginLog"
  		WHERE "LastLogin" > (NOW() - (fncDays * interval '1 Day'));

		SELECT "UID" INTO fncuid
		  FROM "wtkUpdateLog"
		WHERE "AddDate" < (NOW() - (fncDays * interval '1 Day'))
		ORDER BY "UID" DESC LIMIT 1;

		INSERT INTO "tmpResults" ("tDescrip", "tCount")
		  SELECT 'Data Updates', COUNT("UID")
		  FROM "wtkUpdateLog"
		WHERE "UID" > fncuid;

		SELECT "UID" INTO fncuid
		  FROM "wtkReportCntr"
		WHERE "AddDate" < (NOW() - (fncDays * interval '1 Day'))
		ORDER BY "UID" DESC LIMIT 1;

		INSERT INTO "tmpResults" ("tDescrip", "tCount")
		  SELECT 'Reports Viewed', COUNT("UID")
		  FROM "wtkReportCntr"
		WHERE "UID" > fncuid;

		INSERT INTO "tmpResults" ("tDescrip", "tCount")
		  SELECT 'Page Views', COUNT("UID")
		  FROM "wtkUserHistory"
		WHERE "AddDate" > (NOW() - (fncDays * interval '1 Day'));

		RETURN QUERY
		  SELECT "tDescrip", "tCount"
			FROM "tmpResults"
		  ORDER BY "UID" ASC;

	END;
$procedure$ language plpgsql;

CREATE OR REPLACE FUNCTION get_recent_user_history()
  RETURNS TABLE ("User" text, "LastAccess" text, "Page" varchar(150))
AS $procedure$
BEGIN

    CREATE TEMPORARY TABLE IF NOT EXISTS recent_history_ids (
        "MaxUID"    int
    );
	DELETE FROM recent_history_ids;

    INSERT INTO recent_history_ids ("MaxUID")
        SELECT MAX("UID") AS "MaxUID"
        FROM "wtkUserHistory"
        WHERE "UserUID" IS NOT NULL
        GROUP BY "UserUID"
        ORDER BY "MaxUID" DESC
        LIMIT 10;

    RETURN QUERY
    SELECT CONCAT(u."FirstName" || ' ' || COALESCE(u."LastName", '')
		|| '<br>' || L."LookupDisplay") AS "User",
        to_char(h."AddDate", 'FMMM/FMDD/YY at HH24:MI') AS "LastAccess", h."PageURL" AS "Page"
    FROM recent_history_ids r
        INNER JOIN "wtkUserHistory" h ON h."UID" = r."MaxUID"
        INNER JOIN "wtkUsers" u ON u."UID" = h."UserUID"
		INNER JOIN "wtkLookups" L ON L."LookupType" = 'SecurityLevel'
		  AND CAST(L."LookupValue" AS DECIMAL) = u."SecurityLevel"
    ORDER BY r."MaxUID" DESC;

    DROP TABLE recent_history_ids;

END;
$procedure$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION TO_BASE64(fncText varchar(380))
	RETURNS VARCHAR(400)
 AS $fncResult$
    -- encode('Your \ my text'::bytea, 'base64'); fails due to \
    -- this fixes that problem
	DECLARE fncReturn VARCHAR(400) := '';
	BEGIN
		IF (fncText IS NULL) THEN
			fncReturn := NULL;
		ELSE
			IF (fncText = '') THEN
				fncReturn := '';
			ELSE
				fncText := REPLACE(fncText, '\', '\\');
				fncReturn := encode(fncText::bytea, 'base64');
			END IF;
		END IF;

		RETURN fncReturn;
	END
$fncResult$ LANGUAGE plpgsql;
