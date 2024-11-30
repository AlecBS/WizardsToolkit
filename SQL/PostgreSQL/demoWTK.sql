-- Run below scripts so all files in /demo/ folder work (only a few require below tables and data)
CREATE TABLE "pets" (
    "UID" SERIAL PRIMARY KEY,
    "AddDate" timestamp without time zone DEFAULT now(),
    "DelDate" timestamp without time zone,
    "UserUID" int NOT NULL,
    "PetName" varchar(40),
    "Gender" char(1) default NULL,
    "PetType" varchar(4) DEFAULT NULL,
    "City" varchar(40),
    "State" varchar(2),
    "Zipcode" varchar(10),
    "OwnerPhone" varchar(20),
    "OwnerEmail" varchar(60),
    "CanTreat" char(1) default 'N',
    "BirthDate" date,
    "NextTime" char(8),
    "FilePath" varchar(30) NULL,
    "NewFileName" varchar(12) NULL,
    "Latitude" DECIMAL(20,14) NULL,
    "Longitude" DECIMAL(20,14) NULL,
    "Note" text NULL
);
CREATE INDEX "ix_pets_UserUID" ON "pets" ("UserUID");
COMMENT ON COLUMN "pets"."UserUID" IS 'Owner';

CREATE TABLE "petNotes" (
    "UID" SERIAL PRIMARY KEY,
    "AddDate" timestamp without time zone DEFAULT now(),
    "PetUID" int NOT NULL,
    "UserUID" int NOT NULL,
    "PetNote" varchar(120),
    CONSTRAINT "fk_petNotes_UserUID"
      FOREIGN KEY("UserUID")
      REFERENCES "wtkUsers"("UID")
);
COMMENT ON COLUMN "petNotes"."UserUID" IS 'who added note';

INSERT INTO "wtkLookups" ("LookupType","LookupValue","LookupDisplay")
   VALUES ('PetType','D','Dog'),
           ('PetType','C','Cat'),
           ('PetType','R','Rabbit');

INSERT INTO "pets" ("DelDate", "UserUID", "PetName", "Gender", "PetType", "City", "State", "Zipcode", "OwnerPhone", "OwnerEmail", "CanTreat", "BirthDate", "NextTime", "FilePath", "NewFileName", "Latitude", "Longitude", "Note")
   VALUES
   	(NULL, 1, 'Dogbert', 'M', 'D', 'Ceres', 'CA', NULL, '(209) 555-1212', 'dude@email.com', 'Y', '2019-04-09', '04:20 PM', NULL, NULL, NULL, NULL, NULL),
   	('2022-05-27 09:16:33', 1, 'Coati deleted', 'M', 'C', NULL, 'AK', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'female'),
   	(NULL, 1, 'Teva Bunner', 'M', 'R', NULL, 'AK', NULL, NULL, NULL, 'Y', NULL, '05:15 PM', NULL, NULL, NULL, NULL, 'edit'),
   	(NULL, 1, 'Carrot Muncher', 'U', 'R', 'San Jose', 'CA', NULL, '(408) 555-6400', 'coati@email.com', 'Y', NULL, NULL, NULL, NULL, 37.32096470000000, -121.86118270000000, NULL),
   	(NULL, 1, 'Puppers', 'M', 'D', NULL, 'MT', NULL, NULL, NULL, 'N', '2016-05-04', NULL, NULL, NULL, NULL, NULL, 'edit text'),
   	(NULL, 1, 'Cwoat the Coati', 'F', 'C', NULL, 'AK', NULL, NULL, NULL, 'Y', '2022-05-03', '03:25 AM', NULL, NULL, NULL, NULL, 'added photo');

INSERT INTO "petNotes" ("PetUID", "UserUID", "PetNote")
  VALUES
	(1, 1, 'likes meat mucho'),
	(1, 1, 'Loves peanut Butter!'),
	(3, 1, 'likes stuff a lot'),
	(1, 1, 'Cool!'),
	(4, 1, 'Very cute bunny'),
	(4, 1, 'And super fuzzy!'),
	(2, 1, 'name changed again');
