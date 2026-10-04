variable "aws_region" {
  description = "AWS region"
  type        = string
  default     = "eu-north-1"
}

variable "cluster_name" {
  description = "EKS cluster name"
  type        = string
  default     = "student-cluster"
}

variable "vpc_cidr" {
  description = "VPC CIDR block"
  type        = string
  default     = "10.0.0.0/16"
}

variable "node_instance_type" {
  description = "EC2 instance type for EKS worker nodes"
  type        = string
  default     = "c7i-flex.large"
}

variable "desired_nodes" {
  description = "Desired worker node count"
  type        = number
  default     = 2
}

variable "min_nodes" {
  description = "Minimum worker node count"
  type        = number
  default     = 1
}

variable "max_nodes" {
  description = "Maximum worker node count"
  type        = number
  default     = 3
}
