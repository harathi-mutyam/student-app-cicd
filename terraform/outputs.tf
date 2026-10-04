output "cluster_name" {
  description = "EKS cluster name"

  value = aws_eks_cluster.eks.name
}

output "cluster_endpoint" {
  description = "EKS API endpoint"

  value = aws_eks_cluster.eks.endpoint
}

output "node_group_id" {
  description = "EKS managed node group ID"

  value = aws_eks_node_group.eks.id
}

output "vpc_id" {
  description = "VPC ID"

  value = aws_vpc.eks_vpc.id
}

output "subnet_ids" {
  description = "Public subnet IDs"

  value = aws_subnet.public[*].id
}

output "ebs_csi_role_arn" {
  description = "IAM role used by EBS CSI"

  value = aws_iam_role.ebs_csi_role.arn
}
