# Sortir

## Présentation du projet
La société ENI souhaite développer pour ses stagiaires actifs ainsi que ses anciens stagiaires une plateforme web leur permettant d’organiser des sorties. 

## Lancement de l'application Sortir

### Installer les dépendances
```sh
npm install
```
```sh
composer install
```

### Configurer la base de données

- exemple: DATABASE_URL="mysql://root:@127.0.0.1:3306/sortir?serverVersion=8.0.32&charset=utf8mb4"
```sh
symfony console doctrine:database:create
```
```sh
symfony console do:mi:mi
```
### Configurer le mailer

- exemple: MAILER_DSN=smtp://localhost:1025
ne pas oublier de lancer MailHog
```sh
symfony console messenger:consume async
```

### Lancer l'application

```sh
symfony console serve
```

### En cas de bug, veuillez contacter notre formateur [François](https://github.com/DevFanch)

