#!/bin/bash

# Google Cloud Deploy Script for Laravel 12 Social Media Application
# This script automates the deployment process to Google Cloud Run

set -e  # Exit on any error

# Configuration - Update these values for your project
PROJECT_ID="dr3w-social"
REGION="us-east1"
SERVICE_NAME="social-app"
IMAGE_NAME="gcr.io/${PROJECT_ID}/${SERVICE_NAME}"
SQL_INSTANCE_NAME="dr3w-social"
SECRET_NAME="dr3w-social"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🚀 Starting deployment of Laravel Social Media App to Google Cloud Run${NC}"

# Check if required tools are installed
command -v gcloud >/dev/null 2>&1 || { echo -e "${RED}❌ gcloud CLI is required but not installed. Please install it first.${NC}" >&2; exit 1; }
command -v docker >/dev/null 2>&1 || { echo -e "${RED}❌ Docker is required but not installed. Please install it first.${NC}" >&2; exit 1; }

# Authenticate with Google Cloud
echo -e "${YELLOW}🔐 Authenticating with Google Cloud...${NC}"
gcloud auth configure-docker

# Set the project
echo -e "${YELLOW}📋 Setting project to ${PROJECT_ID}...${NC}"
gcloud config set project ${PROJECT_ID}

# Enable required APIs
echo -e "${YELLOW}🔧 Enabling required Google Cloud APIs...${NC}"
gcloud services enable \
    cloudbuild.googleapis.com \
    run.googleapis.com \
    sql-component.googleapis.com \
    sqladmin.googleapis.com \
    secretmanager.googleapis.com \
    container.googleapis.com

# Build the Docker image
echo -e "${YELLOW}🏗️  Building Docker image...${NC}"
docker build -t ${IMAGE_NAME}:latest .

# Push the image to Google Container Registry
echo -e "${YELLOW}📤 Pushing image to Google Container Registry...${NC}"
docker push ${IMAGE_NAME}:latest

# Create Secret Manager secrets if they don't exist
echo -e "${YELLOW}🔒 Creating/updating secrets in Secret Manager...${NC}"

# Function to create or update secret
create_or_update_secret() {
    local secret_name=$1
    local secret_value=$2
    
    if gcloud secrets describe ${secret_name} --project=${PROJECT_ID} >/dev/null 2>&1; then
        echo "Updating existing secret: ${secret_name}"
        echo -n "${secret_value}" | gcloud secrets versions add ${secret_name} --data-file=-
    else
        echo "Creating new secret: ${secret_name}"
        echo -n "${secret_value}" | gcloud secrets create ${secret_name} --data-file=-
    fi
}

# Create individual secrets (you'll need to update these values)
echo -e "${BLUE}ℹ️  Note: You'll need to update the secret values manually or set them as environment variables${NC}"

# Example secrets creation (uncomment and modify as needed)
# create_or_update_secret "APP_NAME" "W3RD SOCIAL"
# create_or_update_secret "APP_KEY" "$(php artisan key:generate --show)"
# create_or_update_secret "APP_URL" "https://your-domain.com"
# create_or_update_secret "DB_DATABASE" "social"
# create_or_update_secret "DB_USERNAME" "social_user"
# create_or_update_secret "DB_PASSWORD" "$(openssl rand -base64 32)"

# Create service account for the application
echo -e "${YELLOW}👤 Creating service account...${NC}"
SERVICE_ACCOUNT_NAME="social-app-service-account"
SERVICE_ACCOUNT_EMAIL="${SERVICE_ACCOUNT_NAME}@${PROJECT_ID}.iam.gserviceaccount.com"

if ! gcloud iam service-accounts describe ${SERVICE_ACCOUNT_EMAIL} >/dev/null 2>&1; then
    gcloud iam service-accounts create ${SERVICE_ACCOUNT_NAME} \
        --display-name="Social App Service Account" \
        --description="Service account for Social Media Laravel application"
fi

# Grant necessary permissions to the service account
echo -e "${YELLOW}🔑 Granting permissions to service account...${NC}"
gcloud projects add-iam-policy-binding ${PROJECT_ID} \
    --member="serviceAccount:${SERVICE_ACCOUNT_EMAIL}" \
    --role="roles/cloudsql.client"

gcloud projects add-iam-policy-binding ${PROJECT_ID} \
    --member="serviceAccount:${SERVICE_ACCOUNT_EMAIL}" \
    --role="roles/secretmanager.secretAccessor"

# Update the googlecloud.yaml with actual project values
echo -e "${YELLOW}📝 Updating googlecloud.yaml with project-specific values...${NC}"
sed -i.bak "s/PROJECT_ID/${PROJECT_ID}/g" googlecloud.yaml
sed -i.bak "s/REGION/${REGION}/g" googlecloud.yaml
sed -i.bak "s/INSTANCE_NAME/${SQL_INSTANCE_NAME}/g" googlecloud.yaml

# Deploy to Cloud Run using gcloud (alternative to kubectl)
echo -e "${YELLOW}🚢 Deploying to Google Cloud Run...${NC}"
gcloud run deploy ${SERVICE_NAME} \
    --image=${IMAGE_NAME}:latest \
    --region=${REGION} \
    --platform=managed \
    --allow-unauthenticated \
    --service-account=${SERVICE_ACCOUNT_EMAIL} \
    --add-cloudsql-instances=${PROJECT_ID}:${REGION}:${SQL_INSTANCE_NAME} \
    --memory=2Gi \
    --cpu=2 \
    --concurrency=80 \
    --timeout=300 \
    --min-instances=1 \
    --max-instances=10 \
    --port=80 \
    --set-env-vars="APP_ENV=production,LOG_CHANNEL=stderr" \
    --set-secrets="APP_KEY=dr3w-social:latest,DB_PASSWORD=dr3w-social:latest"

# Get the service URL
SERVICE_URL=$(gcloud run services describe ${SERVICE_NAME} --region=${REGION} --format='value(status.url)')

echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
echo -e "${GREEN}🌐 Service URL: ${SERVICE_URL}${NC}"