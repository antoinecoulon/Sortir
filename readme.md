# Sortir

## Présentation du projet
La société ENI souhaite développer pour ses stagiaires actifs ainsi que ses anciens stagiaires une plateforme web leur permettant d’organiser des sorties.

## Lancement de l'application Sortir

### Installer les dépendances
```
npm install
```
```
npm i -g concurrently (OPTIONNEL) (pour lancer symfony serve & vite en même temps)
```

```
composer install
```

### Configurer la base de données

- exemple: DATABASE_URL="mysql://root:@127.0.0.1:3306/sortir?serverVersion=8.0.32&charset=utf8mb4"
```
symfony console doctrine:database:create
```
```
symfony console do:mi:mi
```
```
symfony console d:f:l
```


### Lancer l'application

```
npm run serve (SI vous avez installé concurrently)
Sinon lancer “symfony serve” sur une fenêtre et “npm run dev” dans une autre

```

### En cas de bug, veuillez contacter notre formateur [François](https://github.com/DevFanch)

