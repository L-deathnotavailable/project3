# Documentation d'architecture de Renote

## 1. Présentation du projet

Renote est une application de prise de notes. Un utilisateur peut créer des notes,
leur associer un tag et consulter ses propres notes.

Au départ, toute l'application fonctionnait dans Laravel : PHP gérait les données,
les actions utilisateur et la génération des pages HTML. Le but du projet est de
séparer progressivement ces responsabilités :

- Laravel reste responsable des données, de la sécurité et des règles métier ;
- une API REST permet d'accéder aux fonctionnalités ;
- un front React pourra utiliser cette API depuis le web et, plus tard, depuis
  d'autres types d'appareils.

## 2. Architecture de départ

### 2.1 Fonctionnement

Les fonctionnalités Notes et Tags étaient principalement regroupées dans deux
composants Livewire :

- `App\Livewire\Notes` gérait la liste, la création et la suppression des notes ;
- `App\Livewire\TagForm` gérait la création des tags.

Le chemin principal était le suivant :

```text
Navigateur
  → routes/web.php
  → dashboard.blade.php
  → composants Livewire Notes et TagForm
  → modèles Note et Tag
  → base de données
```

Les composants Livewire contenaient à la fois l'état de l'interface, les règles de
validation et les appels aux modèles. Les URL `/dashboard`, `/notes` et `/tags`
affichaient également la même page.

### 2.2 Avantages

- Peu de fichiers étaient nécessaires pour obtenir une application fonctionnelle.
- Livewire permettait une interface assez dynamique sans écrire beaucoup de
  JavaScript.
- Laravel gérait déjà les sessions, la validation et la protection CSRF.
- Cette solution convenait à une petite application uniquement destinée au web.

### 2.3 Limites

- La présentation et les traitements étaient regroupés dans les composants
  Livewire.
- Les pages Notes et Tags n'étaient pas réellement séparées.
- Il n'existait pas de contrôleurs métier dédiés à ces fonctionnalités.
- Les règles d'autorisation étaient difficiles à réutiliser ailleurs.
- Aucun client mobile ou front indépendant ne pouvait utiliser l'application.
- Il n'existait ni API versionnée ni format de réponse JSON commun.

## 3. Back-end après la refactorisation

Le back-end suit maintenant une structure MVC plus classique :

```text
Route
  → validation de la requête
  → contrôleur
  → modèle Eloquent
  → base de données
  → vue Blade ou réponse JSON
```

### 3.1 Organisation principale

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php
│   │   ├── NoteController.php
│   │   ├── TagController.php
│   │   └── Api/V1/
│   │       ├── AuthController.php
│   │       ├── NoteController.php
│   │       └── TagController.php
│   ├── Requests/
│   ├── Resources/
│   └── Responses/ApiResponse.php
├── Models/
└── Policies/NotePolicy.php

resources/views/
├── dashboard.blade.php
├── notes/index.blade.php
└── tags/index.blade.php

routes/
├── web.php
└── api.php
```

### 3.2 Rôle des couches

| Élément | Rôle |
|---|---|
| Routes | Associer une URL et une méthode HTTP à un contrôleur |
| Contrôleurs web | Récupérer les données puis renvoyer une vue ou une redirection |
| Contrôleurs API | Traiter les demandes REST et renvoyer du JSON |
| Form Requests | Valider et filtrer les données reçues |
| Policy | Vérifier qu'une note appartient bien à l'utilisateur connecté |
| Modèles | Représenter les données et les relations Eloquent |
| Resources | Choisir les champs exposés par l'API |
| ApiResponse | Conserver le même format de réponse JSON |
| Vues Blade | Générer le HTML du front Laravel actuel |

Les contrôleurs web et API sont séparés parce qu'ils ne produisent pas le même type
de réponse. Ils réutilisent cependant les mêmes modèles, validations et règles
d'autorisation.

Il n'y a pas de repository dédié. Les requêtes sont encore simples et Eloquent joue
déjà ce rôle d'accès aux données. Une couche supplémentaire serait utile seulement
si les requêtes ou les sources de données devenaient plus complexes.

La cible technique mentionne aussi des services métier. Ils devront être ajoutés
lorsque les cas d'usage seront complétés, afin d'éviter de faire grossir les
contrôleurs.

### 3.3 Données principales

- Un utilisateur peut posséder plusieurs notes.
- Une note appartient à un seul utilisateur.
- Une note appartient à un tag.
- Un tag peut être utilisé par plusieurs notes.
- Les notes sont privées.
- Les tags forment pour l'instant un catalogue partagé.
- Sanctum stocke les tokens dans `personal_access_tokens`.

## 4. API REST

L'API locale utilise l'adresse suivante :

```text
http://project3.test/api/v1
```

Toutes les réponses suivent ce format :

```json
{
  "status": "success",
  "message": "Note créée.",
  "data": {}
}
```

En cas d'erreur, `status` vaut `error`. Les erreurs de validation sont placées dans
`data.errors`.

### 4.1 Routes disponibles

| Méthode | Route | Protection | Rôle |
|---|---|---|---|
| `POST` | `/register` | Publique | Créer un compte et recevoir un token |
| `POST` | `/login` | Publique | Se connecter et recevoir un token |
| `POST` | `/logout` | Bearer | Révoquer le token courant |
| `GET` | `/notes` | Bearer | Lister ses notes |
| `POST` | `/notes` | Bearer | Créer une note |
| `GET` | `/notes/{id}` | Bearer | Consulter une note |
| `PUT/PATCH` | `/notes/{id}` | Bearer | Modifier une note |
| `DELETE` | `/notes/{id}` | Bearer | Supprimer une note |
| `GET` | `/tags` | Bearer | Lister les tags |
| `POST` | `/tags` | Bearer | Créer un tag |
| `GET` | `/tags/{id}` | Bearer | Consulter un tag |
| `PUT/PATCH` | `/tags/{id}` | Bearer | Modifier un tag |
| `DELETE` | `/tags/{id}` | Bearer | Supprimer un tag inutilisé |

La documentation détaillée avec des exemples `curl` se trouve dans `docs/API.md`.

### 4.2 Authentification et sécurité

Après une inscription ou une connexion, l'API renvoie un token Sanctum. Le client le
transmet ensuite dans l'en-tête suivant :

```text
Authorization: Bearer <token>
```

Quelques règles déjà en place :

- les mots de passe sont hachés ;
- ils ne sont jamais renvoyés dans les réponses ;
- un utilisateur ne peut pas consulter ou modifier les notes d'un autre compte ;
- le serveur déduit le propriétaire d'une note à partir du token ;
- les données sont validées avant leur enregistrement ;
- les tentatives de connexion et d'inscription sont limitées ;
- les erreurs internes ne renvoient pas de trace technique au client.

Les principaux codes utilisés sont `200`, `201`, `401`, `403`, `404`, `409`, `422`,
`429` et `500`.

## 5. Analyse du front actuel

Cette partie correspond à la première étape de l'exercice 2. Aucun composant React
n'existe encore.

Après l'exercice 1, le front est dans un état intermédiaire :

- Notes, Tags et Dashboard utilisent des contrôleurs web et des vues Blade ;
- l'authentification et les paramètres utilisent encore Livewire/Volt ;
- `resources/js/app.js` ne contient pas de logique métier ;
- le navigateur n'appelle pas encore `/api/v1`.

Le fonctionnement reste donc piloté par Laravel :

```text
Navigateur
  → route web Laravel
  → contrôleur ou composant Livewire
  → modèle et base de données
  → page HTML ou mise à jour Livewire
```

### 5.1 Vues dynamiques à remplacer ou adapter

| Zone actuelle | Rôle | Équivalent React envisagé |
|---|---|---|
| `dashboard.blade.php` | Afficher les compteurs | `DashboardPage` |
| `notes/index.blade.php` | Lister, créer et supprimer des notes | `NotesPage`, `NoteForm`, `NoteList`, `NoteItem` |
| `tags/index.blade.php` | Lister et créer des tags | `TagsPage`, `TagForm`, `TagList`, `TagItem` |
| Layouts Blade | Navigation et menu utilisateur | `AppLayout`, `Header`, `Navigation` |
| Vues Livewire Auth | Connexion et inscription | `LoginPage`, `RegisterPage` |
| Vues Livewire Settings | Profil, mot de passe et suppression du compte | Pages React et endpoints API associés |

Les composants purement visuels, comme le logo, ne seront pas forcément convertis
ligne par ligne. Leur rôle sera simplement reproduit dans React.

### 5.2 Exemple : création d'une note aujourd'hui

1. Le navigateur demande `GET /notes` avec le cookie de session.
2. `NoteController@index` charge les notes de l'utilisateur et les tags.
3. Laravel transmet ces données à `notes/index.blade.php`.
4. La vue génère une page HTML complète.
5. L'utilisateur envoie un formulaire contenant `text`, `tag_id` et le jeton CSRF.
6. `StoreNoteRequest` valide les données.
7. `NoteController@store` crée la note avec l'utilisateur de la session.
8. Laravel redirige vers `/notes` et recharge la page.

Dans la cible React, le formulaire enverra du JSON à `POST /api/v1/notes`. Après la
réponse `201`, l'interface mettra à jour les données sans recharger toute la page.

### 5.3 Avantages et inconvénients du front actuel

Avantages :

- mise en place rapide avec Laravel ;
- peu de JavaScript à maintenir ;
- sessions, formulaires et validation déjà intégrés ;
- déploiement simple puisqu'il n'y a qu'une application.

Inconvénients :

- les pages HTML ne sont pas réutilisables sur mobile ;
- la navigation dépend des routes Laravel ;
- les actions utilisent des formulaires et des redirections ;
- il n'existe pas d'état client partagé ou de cache ;
- les écrans Livewire mélangent encore PHP et HTML ;
- certaines fonctionnalités web ne possèdent pas encore d'endpoint API.

### 5.4 Écart avec la cible React

| Actuellement | Cible |
|---|---|
| Pages Blade et Livewire | Composants React en JSX |
| Routes Laravel | Routeur côté client |
| Session et cookie Laravel | Token Sanctum envoyé à l'API |
| Données injectées dans le HTML | Réponses JSON |
| Formulaires et redirections | Appels API sans rechargement complet |
| Pas de store client | Redux Toolkit et RTK Query |
| Erreurs affichées par Blade | Erreurs API affichées par React |
| Pas de cache client | Cache RTK Query |

### 5.5 Éléments à conserver ou remplacer

À conserver côté Laravel :

- les routes et contrôleurs API ;
- les Form Requests et la Policy ;
- les modèles Eloquent ;
- les Resources et `ApiResponse` ;
- Sanctum, les migrations et les tests API.

À compléter avant la suppression du front actuel :

- vérifier CORS avec l'adresse du futur front ;
- décider si le profil, le mot de passe, la vérification d'e-mail et la suppression
  du compte doivent être exposés par l'API ;
- ajouter les services métier demandés par l'architecture cible ;
- définir précisément le stockage et la révocation du token.

À retirer seulement quand React proposera les mêmes fonctionnalités :

- les pages Blade remplacées ;
- les composants Livewire/Volt remplacés ;
- les contrôleurs et routes web devenus inutiles ;
- les dépendances Livewire/Flux si aucun écran ne les utilise encore.

## 6. Architecture front cible

Le front React sera un projet séparé qui communiquera avec Laravel en HTTP JSON.

```text
Composants React
  → state management et effets
  → API REST Laravel
  → services métier et modèles
  → base de données
```

Laravel garde la logique métier. React gère l'affichage, l'état de l'interface et les
appels vers l'API.

Une organisation par fonctionnalité a été retenue :

```text
frontend/
├── src/
│   ├── app/                 store, routeur et composant principal
│   ├── components/          composants communs
│   ├── features/
│   │   ├── auth/            connexion et inscription
│   │   ├── notes/           pages et composants Notes
│   │   └── tags/            pages et composants Tags
│   ├── routes/              routes publiques et protégées
│   ├── services/            configuration de l'API
│   ├── styles/
│   └── main.jsx
└── tests/
```

## 7. Choix du state management

### 7.1 Solution choisie

La solution retenue est :

```text
Redux Toolkit + React-Redux + RTK Query
Pattern : Flux
```

Redux Toolkit gérera l'état global. React-Redux reliera les composants au store. RTK
Query prendra en charge les appels REST, le chargement, les erreurs et le cache.

### 7.2 Comparaison rapide

| Solution | Points forts | Limites pour Renote |
|---|---|---|
| Redux Toolkit | Flux clair, actions traçables, selectors, DevTools et RTK Query | Plus de notions à apprendre |
| Zustand | Très simple et peu de code | Organisation du cache et des effets à définir nous-mêmes |
| MobX | Réactivité automatique et peu de code répétitif | Flux plus implicite et moins proche des consignes de l'exercice |

Redux Toolkit a été choisi parce qu'il correspond directement au découpage demandé :
UI, actions, store, selectors et effets. RTK Query évite aussi de développer
manuellement le cache des notes et des tags.

### 7.3 Répartition de l'état

| État | Emplacement prévu |
|---|---|
| Champs d'un formulaire | `useState` dans le composant |
| Utilisateur et token | `authSlice` |
| Notes et tags venant du serveur | Cache RTK Query |
| Compteurs et filtres calculés | Selectors |
| URL de l'API | Variable d'environnement |

Les notes et les tags ne seront pas copiés dans des slices manuels puisque RTK Query
les stockera déjà. Cela évite d'avoir deux versions différentes des mêmes données.

### 7.4 Circulation de l'information

```text
L'utilisateur agit dans la vue
  → le composant déclenche une action ou une mutation RTK Query
  → RTK Query appelle l'API Laravel
  → Laravel renvoie { status, message, data }
  → le cache ou le store Redux est mis à jour
  → un hook ou un selector lit le nouvel état
  → React réaffiche le composant
```

Les composants d'affichage ne contiendront pas directement les URL ou la logique
HTTP.

### 7.5 Authentification

Après `login` ou `register`, le token et l'utilisateur seront placés dans
`authSlice`. RTK Query lira le token et ajoutera automatiquement l'en-tête Bearer aux
requêtes protégées.

Pour la première version web, le token pourra être conservé dans `sessionStorage`
afin de restaurer la session après un rechargement. Cette écriture sera réalisée par
un effet ou un listener, jamais directement dans le reducer Redux.

`sessionStorage` reste accessible au JavaScript. Il faut donc éviter toute injection
de HTML non contrôlé et ne jamais écrire le token dans les journaux.

### 7.6 Cache et erreurs

RTK Query utilisera des catégories de cache `Note` et `Tag`. Une création,
modification ou suppression invalidera les données concernées. Le cache API sera
réinitialisé à la déconnexion afin de ne pas conserver les données du compte
précédent.

Le front devra gérer les principaux cas suivants :

- chargement en cours ;
- résultat vide ;
- erreurs de validation `422` ;
- authentification absente ou expirée `401` ;
- accès interdit `403` ;
- ressource inexistante `404` ;
- conflit lors de la suppression d'un tag `409` ;
- erreur serveur `500`.

## 8. Validation actuelle et points restants

Le projet utilise maintenant PHP 8.4.24 et exige `PHP ^8.4` dans Composer.

Dernière vérification :

- 46 tests réussis ;
- 180 assertions réussies ;
- compilation Vite réussie ;
- dépendances Composer compatibles avec PHP 8.4 ;
- aucun avis de sécurité Composer.

Points à traiter dans les prochaines étapes :

- créer le projet React séparé ;
- installer Redux Toolkit, React-Redux et le routeur ;
- compléter l'API si toutes les pages de paramètres doivent être conservées ;
- ajouter la couche de services PHP ;
- tester CORS et le parcours complet depuis React ;
- conserver le front Laravel jusqu'à ce que le nouveau front soit fonctionnel ;
- produire le PDF final avec les schémas d'architecture.
