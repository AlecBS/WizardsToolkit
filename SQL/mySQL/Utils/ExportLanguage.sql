-- This SQL is good for exporting languages from one server to import to another
SELECT `MassUpdateId`, `Language`, `PrimaryText`, `NewText`
FROM `wtkLanguage`
WHERE `NewText` IS NOT NULL
ORDER BY IF(`MassUpdateId` IS NOT NULL, 'A', 'B') ASC,
   `MassUpdateId` ASC, `Language` ASC, `PrimaryText` ASC;
