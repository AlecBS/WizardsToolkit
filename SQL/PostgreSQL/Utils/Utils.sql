SELECT pg_get_serial_sequence('wtkUsers', 'UID');

ALTER SEQUENCE "wtkUsers_UID_seq" RESTART WITH 3;
