# Projet 6 - Développez de A à Z le site communautaire SnowTricks

Ce projet a été réalisé dans le cadre de ma formation de développeur PHP/Symfony chez Openclassrooms.

## Installation

<br/>Clone this project on your computer
```bash
git clone https://github.com/davy-beauzil/p6-snowtricks.git
```

<br/>Go to the project
```bash
cd /path/to/project
```

<br/>Make `.env.local` file at the root path and fill variables
```bash
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7&charset=utf8mb4"
MAILER_DSN=
SCALEWAY_KEY=
SCALEWAY_SECRET=
```

<br/>Install Composer dependances
```bash
composer install
```

<br/>Install Yarn dependances and build
```bash
yarn install
yarn build
```

<br/>Make database
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

<br/>Fill database with fixtures
```bash
php bin/console doctrine:fixtures:load
```

<br/>Then, open 2 terminals and run these command in each one
```bash
# Messenger is used to send email in asynchrone
php bin/console messenger:consume async -vv

# To run the server
symfony serve
```

<br/>You can connect you with these identifiants
```bash
username : admin
password : admin
```