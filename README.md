# Renote

Renote permet de créer des notes et de les classer à l'aide de tags.

Le projet est maintenant séparé en deux parties :

- un back-end Laravel 12 et PHP 8.4 qui expose une API REST ;
- un front React dans `frontend/`, construit avec Redux Toolkit et RTK Query.

Les écrans métier Dashboard, Notes et Tags sont entièrement gérés par React. Seuls
les écrans de compte qui n'ont pas encore d'équivalent REST restent temporairement
en Livewire.

## Prérequis

- PHP 8.4 ;
- Composer ;
- Node.js 22 ou plus récent ;
- Herd sous Windows pour servir `http://project3.test`.

Vérifiez notamment que la commande `php --version` utilise bien PHP 8.4.

## Installation du back-end

```bash
composer install
```

Copiez `.env.example` vers `.env`, puis configurez la base de données et lancez :

```bash
php artisan key:generate
php artisan migrate
```

Avec Herd, le back-end et son API sont accessibles à l'adresse :

```text
http://project3.test
http://project3.test/api/v1
```

Pour conserver le style des écrans Livewire de compte encore présents :

```bash
npm install
npm run dev
```

## Installation du front React

Depuis le dossier `frontend/` :

```bash
npm install
```

Copiez ensuite `frontend/.env.example` vers `frontend/.env.local` et adaptez si
nécessaire l'adresse de l'API :

```env
VITE_API_BASE_URL=http://project3.test/api/v1
```

Dans le `.env` Laravel, l'origine du front autorisée par CORS doit correspondre à
l'adresse affichée par Vite :

```env
FRONTEND_URL=http://localhost:5173
```

Lancez enfin le front :

```bash
npm run dev
```

Puis ouvrez `http://localhost:5173`.

## Fonctionnalités du front React

- inscription, connexion et déconnexion par token Sanctum ;
- routes publiques et protégées ;
- liste, création, modification et suppression des notes ;
- liste, création, modification et suppression des tags ;
- affichage des erreurs API et des erreurs de validation ;
- cache et actualisation des données avec RTK Query ;
- conservation de la session dans `sessionStorage`.

L'adresse de l'API est centralisée dans une variable d'environnement. Les composants
React ne contiennent pas d'URL d'endpoint en dur.

## Tests et compilation

Back-end :

```bash
php artisan test
```

Front-end :

```bash
cd frontend
npm test
npm run build
```

## Architecture

Le back-end conserve les données, la validation, les autorisations et les règles
métier. Le front React gère l'affichage et appelle uniquement l'API REST.

```text
React
  → Redux Toolkit / RTK Query
  → API REST Laravel
  → Form Requests, Policies et contrôleurs
  → services métier
  → modèles Eloquent
  → base de données
```

- Documentation de l'API : [`docs/API.md`](docs/API.md)
- Documentation d'architecture : [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md)
