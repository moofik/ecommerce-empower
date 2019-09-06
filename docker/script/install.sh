#!/usr/bin/env bash

NET_NAME=master_home
PHP_CONTAINER_NAME=masterhome/php-fpm

. $(dirname $0)/functions.sh
. $(dirname $0)/colored_output.sh

set -e

if [[ -z "$(${SUDO_CMD} docker network inspect ${NET_NAME} > /dev/null 2>&1 && echo 1)" ]]; then
    ${SUDO_CMD} docker network create --subnet 172.22.0.0/24 ${NET_NAME}
    echo -e "${CYAN}Docker network created. Gateway is 172.22.0.1\r\n${NC}"
fi

if [[ ! -f "${DIR}/../docker-compose.yml" ]]; then
    cp "${DIR}/../docker-compose.yml.dist" "${DIR}/../docker-compose.yml"
    echo -e "${CYAN}Docker config created from dist\r\n${NC}"
fi

if [[ -z "$(${SUDO_CMD} docker image inspect ${PHP_CONTAINER_NAME} > /dev/null 2>&1 && echo 1)" ]]; then
    bash -c "cd ${DIR}/.. && ${SUDO_CMD} docker-compose build"
    echo -e "${CYAN}Docker containers were built\r\n${NC}"
fi

# install frontend node modules
if [[ ! -d "${DIR}/../../frontend/node_modules" ]]; then
    bash -c "cd ${DIR}/../.. && MOUNT_DIR=frontend docker/npm install"
    echo -e "${CYAN}Node modules for frontend bundle were successfully installed\r\n${NC}"
fi

# install socket server node modules
if [[ ! -d "${DIR}/../../socket-server/node_modules" ]]; then
    bash -c "cd ${DIR}/../.. && MOUNT_DIR=socket-server docker/npm install"
    echo -e "${CYAN}Node modules for socket server bundle were successfully installed\r\n${NC}"
fi

# install backend vendor packages
if [[ ! -d "${DIR}/../../backend/vendor" ]]; then
    bash -c "cd ${DIR}/../.. && docker/composer install"
    echo -e "${CYAN}Vendor packages for backend bundle were successfully installed\r\n${NC}"
fi

# up containers
bash -c "cd ${DIR}/../.. && docker/up"
echo -e "${CYAN}Containers were successfully started\r\n${NC}"

# initialize database if it was not previously initialized
bash -c "cd ${DIR}/../.. && docker/psql_init"
echo -e "${CYAN}Database checks and installation completed\r\n${NC}"

# migrate doctrine migrations
bash -c "cd ${DIR}/../.. && docker/console doctrine:migrations:migrate"
echo -e "${CYAN}Migrations was successfully executed\r\n${NC}"

### run fixtures
bash -c "cd ${DIR}/../.. && docker/console doctrine:fixtures:load"
echo -e "${CYAN}Database was successfully seeded\r\n${NC}"
echo -e "${CYAN}Installation completed\r\n${NC}"