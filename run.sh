#!/bin/bash

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# default environment variables
APP_NAME=${APP_NAME:-"my-laravel-app"}
APP_PORT=${APP_PORT:-8000}
PHPMYADMIN_PORT=${PHPMYADMIN_PORT:-3000}

# cleanup the containers
cleanup() {
    echo -e "\n${YELLOW}Stopping containers...${NC}"
    docker-compose down
    echo -e "${GREEN}Containers stopped successfully${NC}"
    exit 0
}

# secure permissions setters
set_secure_permissions() {
    for DIR in storage bootstrap/cache; do
        if [ -d "$DIR" ]; then
            # set owner to current user and permissions to 700
            chown -R "$(id -u):$(id -g)" "$DIR"
            chmod -R u+rwX,go-rwx "$DIR"
        fi
    done
}

# register the cleanup function to be called on the EXIT signal
trap cleanup EXIT SIGINT SIGTERM

# load environment variables from .env file if it exists
if [ -f ".env" ]; then
    export $(grep -v '^#' .env | xargs)
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
    echo "Laravel: http://localhost:${APP_PORT}"
    echo "PhpMyAdmin: http://localhost:${PHPMYADMIN_PORT}"
    echo "Vite: run 'npm run dev' in the container to start Vite server"
    echo "Docs available at: http://localhost:${APP_PORT}/docs/api"
    echo -e "${GREEN}Setting permissions...${NC}"
    set_secure_permissions
    echo -e "${GREEN}Permissions set successfully!${NC}"
    echo -e "${GREEN}Waiting to setup everything...${NC}"

    # extra wait time for the containers to be fully up
    sleep 5

    echo -e "${GREEN}Entering container bash...${NC}"
    docker-compose exec laravel.test bash
else
    echo -e "${RED}Error starting containers${NC}"
    exit 1
fi
