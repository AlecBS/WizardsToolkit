#!/bin/sh

##############################################################################################
#
# Script to build docker containers for database and webserver
#
###############################################################################################

export SCRIPTDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null && pwd )"

docker-compose build
# if you are deploying to Google Cloud Platform or other hosting service
# you may want to comment out next line, it is only needed to run in Docker
docker-compose up -d
