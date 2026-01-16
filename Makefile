SHELL := /bin/bash

DC := docker compose
SERVICE := php

.DEFAULT_GOAL := help

.PHONY: help build up down bin logs

help:
	@echo "Targets:"
	@echo "  build      Build image"
	@echo "  up         Launch the container"
	@echo "  down       Stop and delete the container"
	@echo "  logs       Display logs"

build:
	$(DC) build

up:
	$(DC) up -d

down:
	$(DC) down --remove-orphans

logs:
	$(DC) logs -f $(SERVICE)