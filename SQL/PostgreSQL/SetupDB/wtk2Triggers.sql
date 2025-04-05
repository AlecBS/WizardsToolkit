
CREATE OR REPLACE FUNCTION "tia_fnc_wtkWidgetGroup_X_Widget"()
    RETURNS TRIGGER
    LANGUAGE PLPGSQL
  AS
  $$
  DECLARE
    fncLastPriority SMALLINT;
BEGIN

    IF (NEW."UserUID" IS NULL) THEN
        SELECT COUNT(*) INTO fncLastPriority
          FROM "wtkWidgetGroup_X_Widget"
        WHERE "WidgetGroupUID" = NEW."WidgetGroupUID" AND "UserUID" IS NULL
          AND "UID" <> NEW."UID";

        IF (fncLastPriority > 0) THEN
            SELECT "WidgetPriority" INTO fncLastPriority
              FROM "wtkWidgetGroup_X_Widget"
            WHERE "WidgetGroupUID" = NEW."WidgetGroupUID" AND "UserUID" IS NULL
                AND "UID" <> NEW."UID"
            ORDER BY "WidgetPriority" DESC LIMIT 1;
        END IF;
    ELSE
        SELECT COUNT(*) INTO fncLastPriority
          FROM "wtkWidgetGroup_X_Widget"
        WHERE "WidgetGroupUID" = NEW."WidgetGroupUID" AND "UserUID" = NEW."UserUID"
            AND "UID" <> NEW."UID";

        IF (fncLastPriority > 0) THEN
            SELECT "WidgetPriority" INTO fncLastPriority
              FROM "wtkWidgetGroup_X_Widget"
            WHERE "WidgetGroupUID" = NEW."WidgetGroupUID" AND "UserUID" = NEW."UserUID"
                AND "UID" <> NEW."UID"
            ORDER BY "WidgetPriority" DESC LIMIT 1;
        END IF;
    END IF;

    UPDATE "wtkWidgetGroup_X_Widget"
      SET "WidgetPriority" = (fncLastPriority + 10)
    WHERE "UID" = NEW."UID";

    RETURN NEW;
END;
$$;

CREATE TRIGGER "tia_wtkWidgetGroup_X_Widget"
    AFTER INSERT ON "wtkWidgetGroup_X_Widget"
    FOR EACH ROW
      EXECUTE PROCEDURE "tia_fnc_wtkWidgetGroup_X_Widget"();


CREATE OR REPLACE FUNCTION "tia_fnc_wtkMenuGroups"()
    RETURNS TRIGGER
    LANGUAGE PLPGSQL
  AS
  $$
  DECLARE
    fncLastPriority SMALLINT;
BEGIN

    SELECT COUNT(*) INTO fncLastPriority
      FROM "wtkMenuGroups"
    WHERE "MenuUID" = NEW."MenuUID";

    IF (fncLastPriority > 0) THEN
        SELECT "Priority" INTO fncLastPriority
          FROM "wtkMenuGroups"
        WHERE "MenuUID" = NEW."MenuUID"
        ORDER BY "Priority" DESC LIMIT 1;
    END IF;

    UPDATE "wtkMenuGroups"
      SET "Priority" = (fncLastPriority + 10)
    WHERE "UID" = NEW."UID";

    RETURN NEW;
END;
$$;

CREATE TRIGGER "tia_wtkMenuGroups"
    AFTER INSERT ON "wtkMenuGroups"
    FOR EACH ROW
      EXECUTE PROCEDURE "tia_fnc_wtkMenuGroups"();


CREATE OR REPLACE FUNCTION "tia_fnc_wtkMenuItems"()
    RETURNS TRIGGER
    LANGUAGE PLPGSQL
  AS
  $$
  DECLARE
    fncLastPriority SMALLINT;
BEGIN

    SELECT COUNT(*) INTO fncLastPriority
      FROM "wtkMenuItems"
    WHERE "MenuGroupUID" = NEW."MenuGroupUID";

    IF (fncLastPriority > 0) THEN
        SELECT "Priority" INTO fncLastPriority
          FROM "wtkMenuItems"
        WHERE "MenuGroupUID" = NEW."MenuGroupUID"
        ORDER BY "Priority" DESC LIMIT 1;
    END IF;

    UPDATE "wtkMenuItems"
      SET "Priority" = (fncLastPriority + 10)
    WHERE "UID" = NEW."UID";

    RETURN NEW;
END;
$$;

CREATE TRIGGER "tia_wtkMenuItems"
    AFTER INSERT ON "wtkMenuItems"
    FOR EACH ROW
      EXECUTE PROCEDURE "tia_fnc_wtkMenuItems"();
