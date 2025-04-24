#!/bin/bash

ddev start
ddev composer install
cp .env.example .env

curl -o performance.sql.gz https://christoph-daum.com/wp-content/uploads/workshop/db_2025-04-24.sql.gz
gunzip performance.sql.gz
echo "Importing Database, this will take a few minutes... please be patient"
ddev import-db < performance.sql
rm performance.sql
ddev wp fill-content
echo "Importing done, starting the site..."

ddev launch /wp/wp-login.php
