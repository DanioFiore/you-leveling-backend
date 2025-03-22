#!/bin/bash

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}=== You Leveling Setup ===${NC}"

# Controlla se Laravel è già installato verificando l'esistenza di artisan
if [ ! -f "artisan" ]; then
    echo -e "${YELLOW}First time setup: Creating new Laravel project...${NC}"
    curl -s https://laravel.build/you-leveling | bash
    
    # Sposta tutti i file dalla sottodirectory you-leveling alla directory corrente
    mv you-leveling/* you-leveling/.* . 2>/dev/null
    rmdir you-leveling
    
    echo -e "${GREEN}Laravel project created successfully!${NC}"
else
    echo -e "${GREEN}Laravel already installed, proceeding with container setup...${NC}"
fi

echo -e "${GREEN}Starting Docker containers...${NC}"

# Ferma eventuali container in esecuzione
docker-compose down

# Avvia i container
docker-compose up -d --build

# Verifica lo stato dei container
if [ $? -eq 0 ]; then
    echo -e "${GREEN}Containers started successfully!${NC}"
    echo -e "${YELLOW}Available URLs:${NC}"
    echo "Laravel: http://localhost:8000"
    echo "PhpMyAdmin: http://localhost:8080"
    
    echo -e "${GREEN}Setting permissions...${NC}"
    chmod -R 777 storage bootstrap/cache
    
    echo -e "${GREEN}Entering container bash...${NC}"
    docker-compose exec app bash
else
    echo -e "${RED}Error starting containers${NC}"
    exit 1
fi
