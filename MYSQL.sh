#!/usr/bin/env bash

##############################################################################################
#
# Script to connect to the running MySQL instance
#
###############################################################################################

docker exec -it wtk_db_mysql mysql -uroot -pLowCodeViaWTK
