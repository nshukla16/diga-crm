# Create second_db database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `diga-webmail`;
# Grant all privilidges on second_db to org_user
GRANT ALL PRIVILEGES ON `diga-webmail`.* TO 'diga';