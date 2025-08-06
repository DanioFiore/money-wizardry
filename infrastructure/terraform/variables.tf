variable "project_id" {
    description = "ID Google Cloud Project"
    type        = string
}

variable "region" {
    description = "Google Cloud Region"
    type        = string
    default     = "europe-west1"
}

variable "zone" {
    description = "Google Cloud Zone"
    type        = string
    default     = "europe-west1-b"
}

variable "cluster_name" {
    description = "Kubernetes Cluster Name"
    type        = string
}

variable "app_name" {
    description = "Application Name"
    type        = string
    default     = "your-app-name"
}

variable "environment" {
    description = "Environment (dev/staging/prod)"
    type        = string
    default     = "prod"
}

variable "min_nodes" {
    description = "Minimum number of nodes"
    type        = number
    default     = 1
}

variable "max_nodes" {
    description = "Maximum number of nodes"
    type        = number
    default     = 5
}

variable "machine_type" {
    description = "Machine type for the nodes"
    type        = string
    default     = "e2-standard-2"
}