up:
	docker compose -f docker-compose.yaml up -d --build > /dev/null

down:
	docker compose -f docker-compose.yaml down  > /dev/null

attach:
	docker exec -it php_flat_identity_dto_mapper bash

test:
	composer phpunit

test-without-coverage:
	vendor/bin/phpunit

benchmark:
	composer benchmark

help:
	# Usage:
	#   make <target>
	#
	# Targets on local machine:
	#   up ............................. Up Docker
	#   down ........................... Down Docker
	#   attach ......................... attaches to docker PHP container
