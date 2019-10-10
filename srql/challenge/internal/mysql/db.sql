CREATE USER 'ctflol'@'devserver.ctfnet' identified by 'c7fL00l!';
CREATE DATABASE randomcorp;
use randomcorp;
CREATE table developers (secret varchar(255), name varchar(255));
INSERT INTO developers (name,secret) VALUES ("bobby tables","dsu{w0w_U_rlly_p1v0ted}");
GRANT ALL PRIVILEGES ON randomcorp.* TO 'ctflol'@'devserver.ctfnet';
