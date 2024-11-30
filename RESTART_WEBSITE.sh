#!/usr/bin/env bash

##############################################################################################
#
# Script restart apache on the web server container
#
###############################################################################################

export SCRIPTDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null && pwd )"

# docker exec -it webservver sh -c "/usr/sbin/httpd -k graceful"
docker-compose stop
docker-compose up -d
