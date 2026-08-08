DOCKER_USERNAME  ?= gideonip
APPLICATION_NAME ?= sehatsatli-v2-api
DOCKER_TAG       := 0.0.1
VERSION_FILE     := version.txt

-include $(VERSION_FILE)

VERMAJMIN      := $(subst ., ,$(DOCKER_TAG))
VERSION        := $(word 1,$(VERMAJMIN))
MAJOR          := $(word 2,$(VERMAJMIN))
MINOR          := $(word 3,$(VERMAJMIN))
NEW_MINOR      := $(shell expr "$(MINOR)" + 1)
NEW_DOCKER_TAG := $(VERSION).$(MAJOR).$(NEW_MINOR)

build:
	docker buildx build  --platform linux/amd64 --target prod -t ${APPLICATION_NAME} -f ./Dockerfile . 

upload:
	docker tag ${APPLICATION_NAME} ${DOCKER_USERNAME}/${APPLICATION_NAME}:${DOCKER_TAG}
	docker push ${DOCKER_USERNAME}/${APPLICATION_NAME}:${DOCKER_TAG}
	echo "DOCKER_TAG := $(NEW_DOCKER_TAG)" > "$(VERSION_FILE)"

deploy:
	docker buildx build  --platform linux/amd64 --target prod -t ${APPLICATION_NAME} -f ./Dockerfile . 
	docker tag ${APPLICATION_NAME} ${DOCKER_USERNAME}/${APPLICATION_NAME}:${DOCKER_TAG}
	docker push ${DOCKER_USERNAME}/${APPLICATION_NAME}:${DOCKER_TAG}
	echo "DOCKER_TAG := $(NEW_DOCKER_TAG)" > "$(VERSION_FILE)"