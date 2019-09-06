#!/bin/bash
set -e

while ! nc -z $RABBIT_MQ_HOST $RABBIT_MQ_PORT;  do sleep 3; done

echo "Rabbit MQ is up"

exec "$@"