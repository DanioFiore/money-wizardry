terraform {
    required_version = ">= 1.0"
    
    required_providers {
        google = {
            source  = "hashicorp/google"
            version = "~> 5.0"
        }
    }
    
    # where to store the state file
    # use Google Cloud Storage (GCS) as the backend for storing the Terraform state
    # this allows for remote state management and collaboration
    backend "gcs" {
        bucket = "money-wizardry-terraform-state"
    }
}

provider "google" {
    project = var.project_id
    region  = var.region
    
    # use terraform service account key for authentication
    credentials = file("../service_accounts/money-wizardry-terraform-sa.json")
}