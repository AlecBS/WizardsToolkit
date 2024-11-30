-- USE wiztools;
-- MySQL version of FUNCTIONs
DELIMITER $$

CREATE FUNCTION `fncOnlyDigits`(fncVal VARCHAR(150))
  RETURNS VARCHAR(40)
  DETERMINISTIC
  BEGIN
  -- Example usage:
  -- SELECT fncOnlyDigits('(209) 555-8765cell') AS 'PhoneReadyForSMS';
    DECLARE fncReturn VARCHAR(40);
    DECLARE fncCntr   SMALLINT;
    DECLARE fncStrLen SMALLINT;
    DECLARE fncChar   CHAR(1);

    -- shortcut exit for special cases
    SET fncReturn = '';
    IF COALESCE(fncVal,'') <> '' THEN
      -- initialize for loop
      SET fncCntr = 1;
      SET fncStrLen = CHAR_LENGTH(fncVal);
      IF (fncStrLen > 40) THEN
          SET fncVal = SUBSTRING(fncVal,1,40);
          SET fncStrLen = 40;
      END IF;
      do_loop:
      LOOP
          SET fncChar = SUBSTRING(fncVal,fncCntr, 1);
          IF fncChar IN ('0','1','2','3','4','5','6','7','8','9') THEN
              SET fncReturn = CONCAT(fncReturn, fncChar);
          END IF;
          IF fncCntr = fncStrLen THEN
              LEAVE do_loop;
          END IF;
          SET fncCntr = fncCntr + 1;
      END LOOP do_loop;
    END IF;
    RETURN fncReturn;
END
$$

CREATE FUNCTION `fncWordCaps`(fncText VARCHAR(255))
    RETURNS VARCHAR(255)
    DETERMINISTIC
  BEGIN
	DECLARE fncLen INT;
	DECLARE i INT;

	SET fncLen  = CHAR_LENGTH(fncText);
	SET fncText = LOWER(fncText);
	SET i = 0;

	WHILE (i < fncLen) DO
		IF (MID(fncText,i,1) = ' ' OR i = 0) THEN
			IF (i < fncLen) THEN
				SET fncText = CONCAT(
					LEFT(fncText,i),
					UPPER(MID(fncText,i + 1,1)),
					RIGHT(fncText, fncLen - i - 1)
				);
			END IF;
		END IF;
		SET i = i + 1;
	END WHILE;

	RETURN fncText;
END
$$

CREATE FUNCTION `fncCalcDistance`(fncFromLat decimal(24,7), fncFromLong decimal(24,7),
        fncToLat decimal(24,7), fncToLong decimal(24,7)) RETURNS decimal(24,7)
  DETERMINISTIC  -- ABS 10/18/17  this line seems to be required for Percona 5.6
BEGIN
	SET @return_value = (SELECT ((ACOS(SIN(fncFromLat * PI() / 180) * SIN(fncToLat * PI() / 180) +
				COS(fncFromLat * PI() / 180) * COS(fncToLat * PI() / 180) * COS((fncFromLong - fncToLong) * PI() / 180)) * 180 / PI()) * 60 * 1.1515)
);
	RETURN @return_value;
END
$$

CREATE FUNCTION `fncContactIcons`(fncEmail VARCHAR(80),
      fncPhone VARCHAR(80), fncLat DECIMAL(20,14), fncLong DECIMAL(20,14),
      fncShowPhone CHAR(1), fncUserUID INT, fncCanSMS CHAR(1), fncModalEmail CHAR(1), fncEmailCode CHAR(10)
    ) RETURNS varchar(800) CHARSET latin1
    DETERMINISTIC
  BEGIN
  /*
  Example usage:
  SELECT `FirstName`, `fncContactIcons`(`Email`, `Phone`, NULL, NULL, 'Y',`UID`,`SMSEnabled`,'N','')
    FROM `wtkUsers`
  ORDER BY `UID` DESC;
  */
    DECLARE fncHTM VARCHAR(800);
    DECLARE fncPhoneNum VARCHAR(80);
    SET fncHTM = '';

    IF COALESCE(fncEmail,'') <> '' THEN
        IF (fncModalEmail = 'Y') THEN
            SET fncHTM = CONCAT(fncHTM,'<a onclick="wtkModal(', "'/wtk/emailModal','", fncEmailCode, "',",
                fncUserUID, ",''", ');"><i class="material-icons tiny">contact_mail</i></a> ');
        ELSE
            SET fncHTM = CONCAT('<a href="mailto:', fncEmail,
                '"><i class="material-icons tiny">email</i></a> ');
        END IF;
    END IF;
    -- enhanced for future mobile app functionality
    SET fncPhoneNum = fncOnlyDigits(COALESCE(fncPhone,''));
    IF (fncPhoneNum <> '') AND (fncPhoneNum <> '0') THEN
        IF (fncCanSMS = 'Y') THEN
            SET fncHTM = CONCAT(fncHTM,'<a onclick="wtkModal(', "'/wtk/smsModal','sms',", fncUserUID,
                ",'", fncPhoneNum, '\');"><i class="material-icons tiny">sms</i></a> ');
        END IF;
        IF (fncShowPhone = 'Y') THEN
          SET fncHTM = CONCAT(fncHTM,'<a onclick="JavaScript:wtkDialPhone(', fncPhoneNum,
              ')"><i class="material-icons tiny">contact_phone</i></a> ',fncPhone,' ');
-- if you want to use phone formating, switch above line with below 2 lines
              -- ')"><i class="material-icons tiny">contact_phone</i></a> ',
              -- fncFormatPhone(fncOnlyDigits(fncPhone),'(###)###-####'),' ');
        ELSE
          SET fncHTM = CONCAT(fncHTM,'<a onclick="JavaScript:wtkDialPhone(', fncPhoneNum,
            ')"><i class="material-icons tiny">contact_phone</i></a> ');
        END IF;
    END IF;

    IF (COALESCE(fncLat,0) <> 0) THEN
        SET fncHTM = CONCAT(fncHTM,'<a href="http://www.google.com/maps/?q=',
          fncLat,',', fncLong, '" target="_blank"><i class="material-icons tiny">place</i></a> ');
    END IF;
    RETURN fncHTM;
END
$$

CREATE FUNCTION `fncFormatPhone`(fncOrigPhone VARCHAR(30), fncFormatString CHAR(32))
    RETURNS CHAR(32)
    DETERMINISTIC
  BEGIN
  /*
  Example usage:
  SELECT fncFormatPhone(123456789,'###-##-####');
  Result:  123-45-6789

  SELECT fncFormatPhone(123456789,'(###) ###-####');
  Result:  (012) 345-6789

  SELECT fncFormatPhone(123456789,'###-#!##@(###)');
  Result:  123-4!56@(789)
  */
    -- Declare variables
    DECLARE unformatted_value BIGINT;
    DECLARE input_len TINYINT;
    DECLARE output_len TINYINT;
    DECLARE temp_char CHAR;

    -- Initialize variables
    SET unformatted_value = fncOnlyDigits(fncOrigPhone);
    SET input_len = LENGTH(unformatted_value);
    SET output_len = LENGTH(fncFormatString);

    -- Construct formated string
    WHILE ( output_len > 0 ) DO
        SET temp_char = SUBSTR(fncFormatString, output_len, 1);
        IF ( temp_char = '#' ) THEN
            IF ( input_len > 0 ) THEN
                SET fncFormatString = INSERT(fncFormatString, output_len, 1, SUBSTR(unformatted_value, input_len, 1));
                SET input_len = input_len - 1;
            ELSE
                SET fncFormatString = INSERT(fncFormatString, output_len, 1, '0');
            END IF;
        END IF;

        SET output_len = output_len - 1;
    END WHILE;

    RETURN fncFormatString;
  END
$$

CREATE FUNCTION `fncWTKhash`(fncUID INT)
  RETURNS INT UNSIGNED
  DETERMINISTIC
BEGIN
	RETURN (SELECT CRC32(((fncUID + 3) * 7) - 1));
END
$$

DELIMITER ;
