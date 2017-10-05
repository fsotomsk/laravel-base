

sudo -u postgres psql
sudo -u postgres createuser <username>
sudo -u postgres createdb <dbname>

CREATE DATABASE 'laravel-base';
CREATE USER 'laravel-base' WITH PASSWORD 'password';
GRANT ALL PRIVILEGES ON DATABASE 'laravel-base' to 'laravel-base';
