#!/usr/bin/env bash

##############################################################################################
#
# Script restart nginx on the web server container
#
###############################################################################################

export SCRIPTDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null && pwd )"

docker-compose stop
docker-compose up -d
