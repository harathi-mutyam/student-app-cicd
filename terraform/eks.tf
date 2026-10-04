resource "aws_eks_cluster" "eks" {
  name = var.cluster_name

  role_arn = aws_iam_role.eks_cluster_role.arn

  vpc_config {
    subnet_ids = aws_subnet.public[*].id

    endpoint_private_access = true
    endpoint_public_access  = true
  }

  access_config {
    authentication_mode                         = "API_AND_CONFIG_MAP"
    bootstrap_cluster_creator_admin_permissions = true
  }

  depends_on = [
    aws_iam_role_policy_attachment.eks_cluster_policy
  ]

  tags = {
    Name    = var.cluster_name
    Project = "student-app-cicd"
  }
}


# --------------------------------------------------
# MANAGED NODE GROUP
# --------------------------------------------------

resource "aws_eks_node_group" "eks" {
  cluster_name = aws_eks_cluster.eks.name

  node_group_name = "student-node-group"

  node_role_arn = aws_iam_role.eks_node_group_role.arn

  subnet_ids = aws_subnet.public[*].id

  instance_types = [
    var.node_instance_type
  ]

  scaling_config {
    desired_size = var.desired_nodes
    min_size     = var.min_nodes
    max_size     = var.max_nodes
  }

  update_config {
    max_unavailable = 1
  }

  depends_on = [
    aws_iam_role_policy_attachment.eks_worker_node_policy,
    aws_iam_role_policy_attachment.eks_cni_policy,
    aws_iam_role_policy_attachment.eks_ecr_policy
  ]

  tags = {
    Name    = "student-node-group"
    Project = "student-app-cicd"
  }
}


# --------------------------------------------------
# POD IDENTITY AGENT
# --------------------------------------------------

resource "aws_eks_addon" "pod_identity_agent" {
  cluster_name = aws_eks_cluster.eks.name

  addon_name = "eks-pod-identity-agent"

  depends_on = [
    aws_eks_node_group.eks
  ]

  tags = {
    Project = "student-app-cicd"
  }
}
