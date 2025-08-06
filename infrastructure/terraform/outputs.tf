output "cluster_name" {
    description = "Name of the GKE cluster"
    value       = google_container_cluster.primary.name
}

output "cluster_location" {
    description = "Location of the GKE cluster"
    value       = google_container_cluster.primary.location
}

output "cluster_endpoint" {
    description = "Endpoint of the GKE cluster"
    value       = google_container_cluster.primary.endpoint
    sensitive   = true
}

output "docker_repository_url" {
    description = "URL of the Docker repository"
    value       = "${var.region}-docker.pkg.dev/${var.project_id}/${google_artifact_registry_repository.docker_repo.repository_id}"
}

output "database_connection_name" {
    description = "Name of the Cloud SQL connection"
    value       = google_sql_database_instance.mysql.connection_name
}

output "database_private_ip" {
    description = "Private IP of the database"
    value       = google_sql_database_instance.mysql.private_ip_address
}

output "redis_host" {
    description = "Redis Host"
    value       = google_redis_instance.cache.host
}

output "redis_port" {
    description = "Redis Port"
    value       = google_redis_instance.cache.port
}

# Password of the database (sensitive)
output "database_password" {
    description = "Password of the database"
    value       = random_password.db_password.result
    sensitive   = true
}