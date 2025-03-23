#!/bin/bash

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# default environment variables
APP_URL=${APP_URL}
FORWARD_PHPMYADMIN_PORT="http://localhost:${FORWARD_PHPMYADMIN_PORT}"

# cleanup the containers
cleanup() {
    echo -e "\n${YELLOW}Stopping containers...${NC}"
    docker-compose down
    echo -e "${GREEN}Containers stopped successfully${NC}"
    exit 0
}

# register the cleanup function to be called on the EXIT signal
trap cleanup EXIT SIGINT SIGTERM

# load environment variables from .env file if it exists
if [ -f ".env" ]; then
    export $(grep -v '^#' .env | xargs)
fi

echo -e "${GREEN}=== You Leveling Setup ===${NC}"

# check if laravel project is already installed or not. If not, create a new laravel project
if [ ! -f "artisan" ]; then
    echo -e "${YELLOW}First time setup: Creating new Laravel project...${NC}"
    curl -s https://laravel.build/you-leveling | bash
    # move all files to the root directory
    mv you-leveling/* you-leveling/.* . 2>/dev/null
    rmdir you-leveling
    echo -e "${GREEN}Laravel project created successfully!${NC}"
else
    echo -e "${GREEN}Laravel already installed, proceeding with container setup...${NC}"
fi

echo -e "${GREEN}Starting Docker containers...${NC}"

# stop and remove the containers if they are already running
docker-compose down

# start containers in detached mode
docker-compose up -d --build

# check container status
if [ $? -eq 0 ]; then
    echo -e "${GREEN}Containers started successfully!${NC}"
    echo -e "${YELLOW}Available URLs:${NC}"
    echo "Laravel: ${APP_URL}"
    echo "PhpMyAdmin: ${FORWARD_PHPMYADMIN_PORT}"
    echo -e "${GREEN}Setting permissions...${NC}"
    chmod -R 777 storage bootstrap/cache
    echo -e "${GREEN}Entering container bash...${NC}"
    docker-compose exec app bash
else
    echo -e "${RED}Error starting containers${NC}"
    exit 1
fi
