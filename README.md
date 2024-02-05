wget https://dl.google.com/go/go1.21.5.linux-amd64.tar.gz
sudo tar -C /opt -xzf go1.21.5.linux-amd64.tar.gz
export PATH=$PATH:/opt/go/bin
go version 
MUST BE EQUAL `go version go1.21.5 linux/amd64`
cd vendor/kerroline/gophp-excel/go-generator/
go build -o bin/generator main.go
- cd root
cd app
mkdir PhpGoExcel
- cd root
mv vendor/kerroline/gophp-excel/go-generator/bin/generator app/PhpGoExcel/generator

