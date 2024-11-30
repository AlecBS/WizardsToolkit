
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
