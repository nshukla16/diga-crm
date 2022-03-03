[![pipeline status](http://git.diga.pt/main/ERP/badges/master/pipeline.svg)](http://git.diga.pt/main/ERP/commits/master)

# ERP 2.2.0.55

Laravel 5.6

Use npm >= 5.4.2 -> https://stackoverflow.com/questions/45022048/why-does-npm-install-rewrite-package-lock-json

MySQL < 8 (8th version has a new authentication mechanism)

PhpRedis (if you use GoogleDriveUploader)

## Installation

On localhost you don't need to do PRODUCTION steps

1. Requirements

   1. Works well on Ubuntu 16.04, 18.04, 20.04
   2. Install GIT: sudo apt install git
   3. Install NGINX: sudo apt install nginx
      1. Allow firewall to open NGINX ports: sudo ufw allow 'Nginx HTTP'
   4. Install PHP 7.2:
      1. sudo add-apt-repository ppa:ondrej/php
      2. sudo apt update
      3. sudo apt install php7.2
      4. If you already have other versions of PHP on your computer select version 7.2: sudo update-alternatives --set php /usr/bin/php7.2
      5. Install common php extensions: sudo apt-get install php7.2-cli php7.2-fpm php7.2-curl php7.2-gd php7.2-mysql php7.2-mbstring php7.2-xml php7.2-zip zip unzip
   5. Install node:
      1. If you don't have curl: sudo apt install curl
      2. curl -sL https://deb.nodesource.com/setup_15.x | sudo -E bash -
      3. sudo apt-get install -y nodejs
   6. Install composer globally:
      1. If you don't have php-cli: sudo apt install php-cli unzip
      2. curl -sS https://getcomposer.org/installer -o composer-setup.php
      3. HASH=`curl -sS https://composer.github.io/installer.sig`
      4. echo \$HASH
      5. php -r "if (hash_file('SHA384', 'composer-setup.php') === '\$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
      6. sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
   7. Install mysql 5.7:
      1. sudo apt-get update
      2. sudo apt-cache policy mysql-server
      3. sudo apt install -f mysql-client=5.7.32-1ubuntu18.04 mysql-community-server=5.7.32-1ubuntu18.04 mysql-server=5.7.32-1ubuntu18.04
      4. sudo mysql_secure_installation
      5. During the installation don't forget to select the correct version 5.7. Better leave mysql root password empty
   8. apt-get install libxrender-dev
   9. apt-get install wkhtmltopdf
      - If you get this error `This version of wkhtmltopdf is build against an unpatched version of QT, and does not support more then one input document.` do this:
        1. sudo apt-get remove --purge wkhtmltopdf
        2. wget https://github.com/wkhtmltopdf/wkhtmltopdf/releases/download/0.12.4/wkhtmltox-0.12.4_linux-generic-amd64.tar.xz
        3. tar xvJf wkhtmltox-0.12.4_linux-generic-amd64.tar.xz
        4. sudo chown -R root:root wkhtmltox
        5. sudo cp wkhtmltox/bin/wkhtmlto\* /usr/bin/

2. Set up ssh keys in gitlab:
   1. In terminal: `ssh-keygen`
   2. Copy public key to git.diga.pt (Settings -> Ssh keys)
3. Clone ERP to your folder
   1. git clone git@gitlab.com:diga.pt/erp.git MyERPInstance
   2. sudo chown -R your-username-in-ubuntu:www-data MyERPInstance
   3. check if your current ubuntu user is in the www-data group: grep ^www-data /etc/group
   4. if you are not in the list add your user: sudo adduser adilet www-data
4. Create mysql database for ERP(for example with name `erp`) and Afterlogic Webmail(`erp_webmail`), create user for these two databases and grant access to this user (It is better to use MySQL Workbench for this)
5. PRODUCTION: Create subdomain on dominios.pt(xxx.diga.pt) A: 81.84.253.89
6. nginx: create config file in sites-available

   1. Copy nginx default config: sudo cp /etc/nginx/sites-available/default /etc/nginx/sites-available/erp
   2. If you want to run ERP on 127.0.0.1 then change binding ports in the default site: sudo nano /etc/nginx/sites-enabled/default (for example to 8080)
   3. Open newly created config file for ERP: sudo nano /etc/nginx/sites-available/erp
   4. Fill in the editor the path to your cloned ERP correctly: root /home/adilet/projects/erp/public;
   5. Add index.php to the list of entry files: index index.php index.html index.htm index.nginx-debian.html;
   6. Add php-fpm configuration to file:  
       location ~ \.php\$ {
      include snippets/fastcgi-php.conf;
      fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;
      }
   7. Save and exit the editor
   8. Make symlink: ln -s /etc/nginx/sites-available/erp /etc/nginx/sites-enabled/erp

7. PRODUCTION: Install ssl
   1. sudo -u le -s
   2. echo mekmarine.diga.pt >> /opt/letsencrypt/domains.txt
   3. /opt/letsencrypt/dehydrated --cron
   4. Add to cron `le` user:
      - crontab -e (or from admin: crontab -e -u le)
      - 1 0 \* \* \* /opt/letsencrypt/dehydrated --cron
8. nginx -s reload
9. Copy .env.example to .env and fill .env file
   1. LOCALHOST: change APP_ENV to local
   2. LOCALHOST: change APP_DEBUG to true
10. Copy /packages/Rkesa/Email/vendor/afterlogic_webmail/data/settings.example.xml to settings.xml and fill database config on it
11. PRODUCTION: Create an user on saas.diga.pt, copy client_id and secret to env
12. composer install && composer dump-autoload :
    1. If this command ended up with error "PackageManifest.php line 122: Undefined index: name" try to follow the answers https://stackoverflow.com/questions/61177995/laravel-packagemanifest-php-undefined-index-name
13. php artisan key:generate
14. npm install :
    1. If you have errors with code EACCESS try to follow this guide https://docs.npmjs.com/resolving-eacces-permissions-errors-when-installing-packages-globally
15. php artisan migrate
16. php artisan seed:initial_with_language en (not working, better to import one of working ERP's dumps)
17. npm run lang
18. npm run dev
    1. If you have a lot of errors after this command, try to reinstall node-sass: npm uninstall node-sass , and install again but old version: npm install -g node-sass@4.0.0
19. php artisan passport:install
20. For working widgets publish vendors: php artisan vendor:publish

## Limits

vue-core-image-upload - 30M

Need to set client_max_body_size in nginx to 32M (`nginx.conf` - http section):

```
client_max_body_size 32M; (that for correct upload to server and not receive nginx 403 error, must be greater than upload_max_filesize)
```

if `php.ini` of fpm need to set

```
upload_max_filesize = 30M;
```

## To add external resource for incoming requests

1. Add site to site settings
2. Set token on external site

## Adding a module

1. Create module directory (from root folder): `mkdir ./packages/Rkesa/EstimateFork`
2. Make skeleton(from packages/Rkesa/EstimateFork):
   - mkdir src && mkdir src/Http && mkdir src/Http/Controllers && mkdir src/Http/Helpers && mkdir src/database && mkdir src/database/migrations && mkdir src/database/seeds && mkdir src/Models && mkdir resources && mkdir resources/assets && mkdir resources/assets/js && mkdir resources/lang
   - touch src/routes.php && touch src/`EstimateFork`ServiceProvider.php && touch .gitignore
3. Register service provider in root
   - add to config/app.php: `Rkesa\EstimateFork\EstimateForkServiceProvider::class,`
   - add to composer.json: `"Rkesa\\EstimateFork\\": "packages/Rkesa/EstimateFork/src"`
4. composer dump-autoload
5. add migration that fills modules table with new module
6. edit .gitmodules

## Translation to other languages

1. Add lang file like resources/lang/pt/pt.json
2. Add lang to VueI18n in resources/js/components.js
3. Add lang import to calendar/index.vue
4. `npm run lang` makes publishing of all language files and generates js file
5. Dont forget that LocalizedCarbon has only specific languages

## Test

Test data generation: `php artisan db:seed --class=TestDataSeeder`

To use Webpack Dependences Visualizer, uncomment `new Visualizer()` in webpack.mix.js, and see public/stats.html
https://github.com/chrisbateman/webpack-visualizer

## Developers convention

1. USE 4 SPACES FOR TABULATION.
2. Please follow Laravel and Vuejs conceptions.
3. Please attempt to match the style of surrounding code as much as possible, even if it is not Laravel and Vuejs conceptions :D.
4. Please use ; in JavaScript.
5. To change context in JavaScript use "let \$this = this;"
6. Use trailing commas in i18n files (_/lang/en/_) to prevent merge conflicts
7. Vue blocks order in Single-file components: `<style> -> <template> -> <script>`. Historically reason
8. Specify Jira issue id in commit messages
9. Specify Jira issue id in branch name
10. Use `npm run eslint` before pushing to the repository

Eslint single rule check `./node_modules/.bin/eslint resources/assets/js/ packages/Rkesa/*/resources/assets/js/ --ext .js,.vue --no-eslintrc --rule 'indent: ["error", 4]' --parser-options '{"ecmaVersion": 2018,"sourceType": "module"}' --plugin vue --env browser,es6 --parser vue-eslint-parser`

## Docker instructions:

1. docker-compose up
2. set DB variables in .env file according to docker-compose.yml
3. Open http://localhost:81/

## PDF:

/estimates/id
/plannings/id
/checklists/id
/fills/id
/company/id

## SOME ISSUES

If you get 403 on broadcasting/auth, probably you have incorrect permissions or config is wrong. Try to run php artisan config:clear or remove laravel.log
After changing some Job make sure you restarted queue (php artisan queue:restart)

## Docker

There are 3 configurations:

- docker-compose.prod.yml - production ready
- docker-compose.dev.yml - uses volumes to mount current directory for hot reloading
- docker-compose.remote-dev.yml - has ssh-server for remote managing
