#!/usr/bin/env bash

##############################################################################################
#
# This adds data tables and data necessary for Wizard's Toolkit framework
#
###############################################################################################

docker exec -it wtk_db_mysql mysql -uroot -pLowCodeViaWTK -e "CREATE DATABASE wiztools;"
docker exec -it wtk_db_mysql mysql -uroot -pLowCodeViaWTK -e "CREATE USER 'wtkdba'@'localhost' IDENTIFIED BY 'LowCodeViaWTK';"
docker exec -it wtk_db_mysql mysql -uroot -pLowCodeViaWTK -e "GRANT ALL ON wiztools.* TO 'wtkdba'@'localhost';"

## Below should only be used for development local testing
docker exec -it wtk_db_mysql mysql -uroot -pLowCodeViaWTK -e "CREATE USER 'wtkdba'@'%' IDENTIFIED BY 'LowCodeViaWTK';"
docker exec -it wtk_db_mysql mysql -uroot -pLowCodeViaWTK -e "GRANT ALL ON wiztools.* TO 'wtkdba'@'%';"

docker exec -it wtk_db_mysql mysql -uroot -pLowCodeViaWTK -e "FLUSH PRIVILEGES ;"
docker exec -it wtk_db_mysql mysql -uroot -pLowCodeViaWTK -e "SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));"

docker exec -i wtk_db_mysql mysql -uroot -pLowCodeViaWTK -h 127.0.0.1 wiztools < ./SQL/mySQL/SetupDB/wtk1Tables.sql
docker exec -i wtk_db_mysql mysql -uroot -pLowCodeViaWTK -h 127.0.0.1 wiztools < ./SQL/mySQL/SetupDB/wtk2InitialData.sql
docker exec -i wtk_db_mysql mysql -uroot -pLowCodeViaWTK -h 127.0.0.1 wiztools < ./SQL/mySQL/SetupDB/wtk3Triggers.sql
docker exec -i wtk_db_mysql mysql -uroot -pLowCodeViaWTK -h 127.0.0.1 wiztools < ./SQL/mySQL/SetupDB/wtk4Functions.sql
docker exec -i wtk_db_mysql mysql -uroot -pLowCodeViaWTK -h 127.0.0.1 wiztools < ./SQL/mySQL/SetupDB/wtk5Procedures.sql
docker exec -i wtk_db_mysql mysql -uroot -pLowCodeViaWTK -h 127.0.0.1 wiztools < ./SQL/mySQL/SetupDB/wtk6Views.sql

echo "Database SQL scripts have run preparing site for new framework"
echo
