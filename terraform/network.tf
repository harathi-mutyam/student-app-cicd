resource "aws_vpc" "eks_vpc" {
  cidr_block           = var.vpc_cidr
  enable_dns_support   = true
  enable_dns_hostnames = true

  tags = {
    Name    = "${var.cluster_name}-vpc"
    Project = "student-app-cicd"
  }
}

resource "aws_internet_gateway" "eks_igw" {
  vpc_id = aws_vpc.eks_vpc.id

  tags = {
    Name    = "${var.cluster_name}-igw"
    Project = "student-app-cicd"
  }
}

resource "aws_subnet" "public" {
  count = 2

  vpc_id = aws_vpc.eks_vpc.id

  cidr_block = cidrsubnet(
    aws_vpc.eks_vpc.cidr_block,
    8,
    count.index
  )

  availability_zone = element(
    [
      "${var.aws_region}a",
      "${var.aws_region}b"
    ],
    count.index
  )

  map_public_ip_on_launch = true

  tags = {
    Name = "${var.cluster_name}-public-${count.index + 1}"

    "kubernetes.io/role/elb" = "1"

    Project = "student-app-cicd"
  }
}

resource "aws_route_table" "public" {
  vpc_id = aws_vpc.eks_vpc.id

  route {
    cidr_block = "0.0.0.0/0"
    gateway_id = aws_internet_gateway.eks_igw.id
  }

  tags = {
    Name    = "${var.cluster_name}-public-route-table"
    Project = "student-app-cicd"
  }
}

resource "aws_route_table_association" "public" {
  count = 2

  subnet_id      = aws_subnet.public[count.index].id
  route_table_id = aws_route_table.public.id
}
