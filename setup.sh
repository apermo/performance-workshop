#!/bin/bash

ddev start
ddev composer install
cp .env.example .env

curl -o performance.sql.gz https://christoph-daum.com/wp-content/uploads/workshop/db_2025-04-24.sql.gz
gunzip performance.sql.gz
ddev import-db < performance.sql
rm performance.sql
ddev wp fill-content

ddev launch /wp/wp-login.php
