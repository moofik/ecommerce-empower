#!/usr/bin/env bash

if [[ -z "$1" ]]; then
    echo "You must give path to dump file"
    exit 1
fi

if [[ ! -f "$1" ]]; then
    echo "File not found"
    exit 1
fi

if [[ -z "$2" ]]; then
    echo "Database not specified"
    exit 1
fi

. $(dirname $0)/functions.sh

set -e

FILE_PATH=$(readlink -e $1)

${SUDO_CMD} docker run \
    -it \
    --rm \
    -v ${FILE_PATH}:/dump.gz:ro \
    --network masterhome \
    masterhome/postgresql \
    bash -c "zcat /dump.gz | psql -h masterhome-postgresql -U masterhome -d $2"
