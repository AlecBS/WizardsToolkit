#!/usr/bin/env bash

##############################################################################################
#
# Force a full rebuild of the docker containers
#
###############################################################################################

export SCRIPTDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null && pwd )"

docker-compose stop
docker-compose rm -f
docker-compose build --no-cache




