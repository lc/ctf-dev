# build external vuln site
docker build -t sqlol:latest .
# create a network bridge
docker network create --driver bridge --subnet 172.18.0.0/16 --gateway 172.18.0.1 ctfnet
# run vulnerable blog site
docker run -d -p 80:80 --name blog sqlol:latest

# assign it an IP within the subnet
docker network connect --ip 172.18.0.2 ctfnet blog

cd internal
docker build -t internal:latest .
docker run -d --name devserver internal:latest
docker network connect --ip 172.18.0.3 ctfnet devserver
# run mysql
docker run -d --name mysql -e "MYSQL_ROOT_PASSWORD=DsU@cTF#Fun" mysql:5.6.24
docker network connect --ip 172.18.0.4 ctfnet mysql
cd mysql
docker cp db.sql mysql:/var/