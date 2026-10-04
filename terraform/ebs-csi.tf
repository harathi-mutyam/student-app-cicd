# --------------------------------------------------
# EBS CSI POD IDENTITY IAM ROLE
# --------------------------------------------------

data "aws_iam_policy_document" "ebs_csi_assume_role" {
  statement {
    effect = "Allow"

    principals {
      type = "Service"

      identifiers = [
        "pods.eks.amazonaws.com"
      ]
    }

    actions = [
      "sts:AssumeRole",
      "sts:TagSession"
    ]
  }
}


# --------------------------------------------------
# CREATE IAM ROLE FOR EBS CSI
# --------------------------------------------------

resource "aws_iam_role" "ebs_csi_role" {
  name = "${var.cluster_name}-ebs-csi-role"

  assume_role_policy = data.aws_iam_policy_document.ebs_csi_assume_role.json

  tags = {
    Project = "student-app-cicd"
  }
}


# --------------------------------------------------
# ATTACH AWS MANAGED EBS CSI POLICY
# --------------------------------------------------

resource "aws_iam_role_policy_attachment" "ebs_csi_policy" {
  role = aws_iam_role.ebs_csi_role.name

  policy_arn = "arn:aws:iam::aws:policy/AmazonEBSCSIDriverPolicyV2"
}


# --------------------------------------------------
# INSTALL AWS EBS CSI DRIVER ADD-ON
# --------------------------------------------------

resource "aws_eks_addon" "ebs_csi" {
  cluster_name = aws_eks_cluster.eks.name

  addon_name = "aws-ebs-csi-driver"

  pod_identity_association {
    role_arn = aws_iam_role.ebs_csi_role.arn

    service_account = "ebs-csi-controller-sa"
  }

  depends_on = [
    aws_eks_node_group.eks,
    aws_eks_addon.pod_identity_agent,
    aws_iam_role_policy_attachment.ebs_csi_policy
  ]

  tags = {
    Project = "student-app-cicd"
  }
}
