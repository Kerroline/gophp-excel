apt update

apt install wget

wget https://dl.google.com/go/go1.21.5.linux-amd64.tar.gz

tar -C /opt -xzf go1.21.5.linux-amd64.tar.gz

export PATH=$PATH:/opt/go/bin

go version 

MUST BE EQUAL `go version go1.21.5 linux/amd64`

go build -o generator main.go



