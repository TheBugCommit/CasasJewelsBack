DOCKER_APP = docker-compose exec --index=1 backend

.PHONY: help up down install db-init cache-clear logs-lb bash

help:
	@echo "Comandes disponibles:"
	@echo "  make up          - Aixeca tots els contenidors (Load Balancer, 3 Apps, BD, Redis)"
	@echo "  make down        - Atura i elimina els contenidors"
	@echo "  make install     - Executa 'composer install' dins d'una instància"
	@echo "  make db-init     - Crea la base de dades i executa les migracions"
	@echo "  make cache-clear - Neteja la memòria cau de Symfony i Redis, i reinicia per buidar APCu"
	@echo "  make logs-lb     - Mostra els logs de Nginx en temps real"
	@echo "  make bash        - Entra dins del primer contenidor de Symfony per executar coses manualment"

up:
	docker-compose up -d

down:
	docker-compose down

install:
	$(DOCKER_APP) git config --global --add safe.directory /var/www/html
	$(DOCKER_APP) composer install

db-init:
	$(DOCKER_APP) php bin/console doctrine:database:create --if-not-exists
	$(DOCKER_APP) php bin/console doctrine:migrations:migrate --no-interaction
	$(DOCKER_APP) php bin/console doctrine:fixtures:load

cache-clear:
	@echo "Netejant la memòria cau de Symfony i Redis..."
	$(DOCKER_APP) php bin/console cache:pool:clear cache.global_clearer
	@echo "Reiniciant les instàncies per buidar les memòries RAM d'APCu..."
	docker-compose restart backend
	@echo "Memòria cau completament neta!"

logs-lb:
	docker-compose logs -f loadbalancer

bash:
	$(DOCKER_APP) bash
