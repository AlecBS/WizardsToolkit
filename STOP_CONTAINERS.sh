#!/usr/bin/env bash

##############################################################################################
#
# Script to stop the containers
#
###############################################################################################

export SCRIPTDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null && pwd )"

docker-compose stop
