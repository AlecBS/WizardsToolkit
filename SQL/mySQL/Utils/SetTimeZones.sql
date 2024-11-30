
UPDATE `wtkProspects`
  SET   `Country` = 'USA'
WHERE `Country` IS NULL AND `State` IN ('CT', 'DE', 'GA', 'ME', 'MD', 'MA', 'NH', 'NJ', 'NY', 'NC',
     'OH', 'PA', 'RI', 'SC', 'VM', 'VA', 'WV', 'WA', 'CA', 'NV', 'MT', 'WY', 'CO','AZ', 'NM', 'UT',
     'OK', 'MN', 'IA', 'MO', 'AR', 'LA', 'WI', 'IL', 'MS', 'AL', 'PR');

UPDATE `wtkProspects`
  SET  `TimeZone` = 'US/Eastern'
WHERE `TimeZone` IS NULL AND `State` IN ('CT', 'DE', 'GA', 'ME', 'MD', 'MA',
    'NH', 'NJ', 'NY', 'NC', 'OH', 'PA', 'RI', 'SC', 'VM', 'VA', 'WV');

UPDATE `wtkProspects`
  SET  `TimeZone` = 'US/Pacific'
WHERE `TimeZone` IS NULL AND `State` IN ('WA', 'CA', 'NV');

UPDATE `wtkProspects`
  SET  `TimeZone` = 'US/Mountain'
WHERE `TimeZone` IS NULL AND `State` IN ('MT', 'WY', 'CO','AZ');
    -- NM and UT

UPDATE `wtkProspects`
  SET  `TimeZone` = 'US/Central'
WHERE `TimeZone` IS NULL AND `State` IN ('OK', 'MN', 'IA', 'MO', 'AR', 'LA', 'WI', 'IL', 'MS', 'AL');

UPDATE `wtkProspects`
  SET  `Country` = 'Canada', `TimeZone` = 'US/Mountain'
WHERE  `State` = 'AB' ;

UPDATE `wtkProspects`
  SET  `Country` = 'USA', `TimeZone` = 'America/Puerto_Rico'
WHERE  `State` = 'PR' ;

-- Remove from Prospect Emails those that are likely generic
UPDATE `wtkProspectStaff`
    SET `DoNotContact` = 'Y'
  WHERE `Email` LIKE 'info@%'
     OR `Email` LIKE 'admin@%'
     OR `Email` LIKE 'contact@%'
     OR `Email` LIKE 'sales@%'
     OR `Email` LIKE 'support@%'
;
