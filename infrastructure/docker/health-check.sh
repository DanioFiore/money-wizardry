#!/bin/bash
# Health check script for Laravel Octane
set -e

# Check if the application responds
# Use the same port that the application is using
PORT=${PORT:-80}

# Try a simple HTTP request with shorter timeout for Cloud Run
if timeout 3 curl -f -s -m 2 "http://localhost:${PORT}/health" > /dev/null 2>&1; then
    echo "Health check passed"
    exit 0
else
    echo "Health check failed, trying root endpoint..."
    # Fallback: try root endpoint
    if timeout 3 curl -f -s -m 2 "http://localhost:${PORT}/" > /dev/null 2>&1; then
        echo "Root endpoint responsive"
        exit 0
    else
        echo "Health check failed completely"
        exit 1
    fi
fi


steps:
  - name: gcr.io/cloud-builders/docker
    args:
      - build
      - '--no-cache'
      - '-t'
      - >-
        $_AR_HOSTNAME/$_AR_PROJECT_ID/$_AR_REPOSITORY/$REPO_NAME/$_SERVICE_NAME:$COMMIT_SHA
      - .
      - '-f'
      - Dockerfile
    id: Build
  - name: gcr.io/cloud-builders/docker
    args:
      - push
      - >-
        $_AR_HOSTNAME/$_AR_PROJECT_ID/$_AR_REPOSITORY/$REPO_NAME/$_SERVICE_NAME:$COMMIT_SHA
    id: Push
  - name: 'gcr.io/google.com/cloudsdktool/cloud-sdk:slim'
    args:
      - run
      - services
      - update
      - $_SERVICE_NAME
      - '--platform=managed'
      - >-
        --image=$_AR_HOSTNAME/$_AR_PROJECT_ID/$_AR_REPOSITORY/$REPO_NAME/$_SERVICE_NAME:$COMMIT_SHA
      - >-
        --labels=managed-by=gcp-cloud-build-deploy-cloud-run,commit-sha=$COMMIT_SHA,gcb-build-id=$BUILD_ID,gcb-trigger-id=$_TRIGGER_ID
      - '--region=$_DEPLOY_REGION'
      - '--quiet'
    id: Deploy
    entrypoint: gcloud
images:
  - >-
    $_AR_HOSTNAME/$_AR_PROJECT_ID/$_AR_REPOSITORY/$REPO_NAME/$_SERVICE_NAME:$COMMIT_SHA
options:
  substitutionOption: ALLOW_LOOSE
  logging: CLOUD_LOGGING_ONLY
substitutions:
  _AR_PROJECT_ID: money-wizardry-prod
  _PLATFORM: managed
  _SERVICE_NAME: app
  _DEPLOY_REGION: europe-west1
  _AR_HOSTNAME: europe-west1-docker.pkg.dev
  _AR_REPOSITORY: cloud-run-source-deploy
  _TRIGGER_ID: 715246bb-d55c-4e64-a55d-d5af76cabe0a
tags:
  - gcp-cloud-build-deploy-cloud-run
  - gcp-cloud-build-deploy-cloud-run-managed
  - app
