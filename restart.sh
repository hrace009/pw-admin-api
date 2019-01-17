
sudo docker rm -f $(sudo docker ps -a -q)
docker volume rm $(docker volume ls -qf dangling=true)
#docker rmi $(docker images -a -q)
sudo rm -R data
docker-compose up -d
