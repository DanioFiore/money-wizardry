# 1. STORAGE BUCKET for Terraform State
resource "google_storage_bucket" "terraform_state" {
    name     = "money-wizardry-terraform-state"
    location = var.region
    
    versioning {
        enabled = true
    }
    
    lifecycle_rule {
        action {
            type = "Delete"
        }
        condition {
            age = 30
        }
    }
}

# 2. ARTIFACT REGISTRY for docker images
resource "google_artifact_registry_repository" "docker_repo" {
    location      = var.region
    repository_id = "${var.app_name}-repo"
    description   = "Docker Repo for ${var.app_name}"
    format        = "DOCKER"
    
    labels = {
        environment = var.environment
        app         = var.app_name
    }
}

# 3. custom vpc network
resource "google_compute_network" "vpc" {
    name                    = "${var.app_name}-vpc"
    auto_create_subnetworks = false
    
    depends_on = [google_storage_bucket.terraform_state]
}

resource "google_compute_subnetwork" "subnet" {
    name          = "${var.app_name}-subnet"
    ip_cidr_range = "10.10.0.0/24"
    region        = var.region
    network       = google_compute_network.vpc.id
    
    # set private Google access to true to allow private access to Google APIs
    private_ip_google_access = true
}

# 4. KUBERNETES CLUSTER (GKE) - Autopilot
resource "google_container_cluster" "primary" {
    name     = var.cluster_name
    location = var.region  # Autopilot richiede regional cluster
    
    # configuration
    network    = google_compute_network.vpc.name
    subnetwork = google_compute_subnetwork.subnet.name
    
    # Enable Autopilot - gestione automatica dei nodi
    enable_autopilot = true

    # configure Workload Identity (security best practice)
    workload_identity_config {
        workload_pool = "${var.project_id}.svc.id.goog"
    }

    # IP allocation policy per Autopilot
    ip_allocation_policy {
        cluster_ipv4_cidr_block  = "/17"
        services_ipv4_cidr_block = "/22"
    }
}

# 5. SERVICE ACCOUNT per GKE
# Con Autopilot non è necessario un node pool separato
# Google gestisce automaticamente i nodi

resource "google_service_account" "gke_service_account" {
    account_id   = "${var.app_name}-gke-sa"
    display_name = "Service Account for ${var.app_name} GKE"
}

# Assegna ruoli alla service account
resource "google_project_iam_binding" "gke_service_account" {
    project = var.project_id
    role    = "roles/container.nodeServiceAccount"
    
    members = [
        "serviceAccount:${google_service_account.gke_service_account.email}",
    ]
}

# 6. CLOUD SQL per il database (MySQL)
resource "google_sql_database_instance" "mysql" {
    name             = "${var.app_name}-mysql"
    database_version = "MYSQL_8_0"
    region           = var.region
    
    settings {
        tier = "db-f1-micro"  # Tier economico per iniziare
        
        backup_configuration {
            enabled = true
            start_time = "03:00"
        }
        
        ip_configuration {
            ipv4_enabled    = false
            private_network = google_compute_network.vpc.self_link
        }
        
        database_flags {
            name  = "innodb_buffer_pool_size"
            value = "134217728"  # 128MB
        }
    }
    
    depends_on = [google_service_networking_connection.private_vpc_connection]
}

# Database for the application
resource "google_sql_database" "app_database" {
    name     = var.app_name
    instance = google_sql_database_instance.mysql.name
}

# Database user
resource "google_sql_user" "app_user" {
    name     = "${var.app_name}_user"
    instance = google_sql_database_instance.mysql.name
    password = random_password.db_password.result
}

# Password random per il database
resource "random_password" "db_password" {
    length  = 16
    special = true
}

# 7. PEERING for private IP addresses
resource "google_compute_global_address" "private_ip_address" {
    name          = "${var.app_name}-private-ip"
    purpose       = "VPC_PEERING"
    address_type  = "INTERNAL"
    prefix_length = 16
    network       = google_compute_network.vpc.id
}

resource "google_service_networking_connection" "private_vpc_connection" {
    network                 = google_compute_network.vpc.id
    service                 = "servicenetworking.googleapis.com"
    reserved_peering_ranges = [google_compute_global_address.private_ip_address.name]
}

# 8. REDIS for caching (optional but recommended)
resource "google_redis_instance" "cache" {
    name           = "${var.app_name}-redis"
    tier           = "BASIC"
    memory_size_gb = 1
    
    location_id    = var.zone
    
    authorized_network = google_compute_network.vpc.id
    
    redis_version = "REDIS_6_X"
    display_name  = "${var.app_name} Redis Cache"
}