# Architecture de Renote

## 1. Objet du document

Renote permet à un utilisateur de créer des notes et de les classer avec des tags.
L'application a été séparée en deux projets qui communiquent en JSON :

- un front React piloté par Redux Toolkit et RTK Query ;
- un back-end Laravel 12 sous PHP 8.4, organisé en MVC et exposant une API REST.

Le PDF final reprend ces éléments sous forme de schémas : architecture initiale,
architecture refactorisée actuelle, organisation du front et cheminement d'une
création de note.

## 2. Architecture initiale

Au départ, Laravel gérait les actions, les données et la génération du HTML. Les
composants Livewire `Notes` et `TagForm` mélangeaient l'état de l'interface, la
validation et l'accès aux modèles.

```text
Navigateur
  → routes/web.php
  → vue Blade et composants Livewire
  → modèles Eloquent
  → base de données
  → HTML renvoyé au navigateur
```

Cette organisation avait l'avantage d'être rapide à mettre en place et simple à
déployer. Elle convenait à une petite application web, mais présentait plusieurs
limites :

- le front n'était pas réutilisable par une application mobile ;
- l'interface et les traitements PHP étaient fortement liés ;
- les mêmes données n'étaient pas accessibles par une API versionnée ;
- il n'existait ni état client partagé ni cache côté navigateur ;
- les règles d'autorisation étaient plus difficiles à isoler et à tester.

## 3. Architecture actuelle

Les fonctionnalités Dashboard, Notes, Tags, connexion et inscription sont maintenant
gérées par React. Laravel ne génère plus leur interface : il valide les requêtes,
applique les autorisations, utilise les modèles et renvoie du JSON.

```text
React → Redux / RTK Query → API Laravel → services → Eloquent → base de données
  ↑                                                                  ↓
  └──────────────── réponse JSON et mise à jour du cache ─────────────┘
```

### 3.1 Répartition des responsabilités

| Partie | Responsabilité |
|---|---|
| Composants et pages React | Afficher l'interface et transmettre les actions de l'utilisateur |
| React Router | Gérer les routes publiques, protégées et la navigation |
| Redux Toolkit | Conserver l'utilisateur connecté et le token |
| RTK Query | Exécuter les appels HTTP, gérer le cache, les chargements et les erreurs |
| Routes Laravel | Associer une méthode et une URL à un contrôleur |
| Contrôleurs API | Orchestrer un cas d'usage et construire la réponse HTTP |
| Services PHP | Regrouper les opérations métier et l'accès aux modèles |
| Form Requests | Valider et filtrer les données reçues |
| Policy | Empêcher l'accès aux notes d'un autre utilisateur |
| Modèles Eloquent | Représenter les utilisateurs, notes, tags et leurs relations |
| Resources / ApiResponse | Sélectionner les données exposées et uniformiser le JSON |
| Base de données | Conserver les données et les tokens Sanctum |

### 3.2 View, état et Model

Le front suit le flux unidirectionnel de Redux, donc il ne s'agit pas d'un MVVM
strict. On peut néanmoins rapprocher les responsabilités des trois rôles demandés :

- **View** : pages et composants JSX, par exemple `NotesPage`, `NoteForm` et
  `NoteItem` ;
- **rôle de ViewModel** : hooks RTK Query, `authSlice`, selectors et états locaux de
  formulaire ; cette couche prépare les données et les actions utilisées par la vue ;
- **Model** : modèles Eloquent `User`, `Note` et `Tag` dans Laravel. Les données
  reçues par React sont des représentations JSON mises en cache, pas des modèles
  métier dupliqués côté client.

### 3.3 Organisation des fichiers

```text
frontend/src/
├── app/                 configuration du store et du routeur
├── components/          composants partagés et layouts
├── features/
│   ├── auth/            connexion, inscription et état de session
│   ├── dashboard/       synthèse des notes et tags
│   ├── notes/           vues, formulaires et endpoints Notes
│   └── tags/            vues, formulaires et endpoints Tags
├── routes/              protection des routes
└── services/            configuration HTTP commune

app/
├── Http/
│   ├── Controllers/Api/V1/
│   ├── Requests/
│   ├── Resources/
│   └── Responses/ApiResponse.php
├── Models/
├── Policies/
└── Services/
    ├── AuthService.php
    ├── NoteService.php
    └── TagService.php

routes/
└── api.php
```

## 4. Rôle des principales couches

### Routes

`routes/api.php` expose l'API sous le préfixe `/api/v1`. L'inscription et la
connexion sont publiques. Les autres routes passent par `auth:sanctum`.
`frontend/src/app/router.jsx` définit de son côté les écrans React et protège les
pages privées.

### Controllers

`AuthController`, `NoteController` et `TagController` reçoivent des données déjà
validées, délèguent le traitement au service concerné puis renvoient une réponse
JSON. Ils ne génèrent aucune vue HTML.

### Models

Les modèles `User`, `Note` et `Tag` portent les relations Eloquent : un utilisateur
possède plusieurs notes, une note appartient à un utilisateur et à un tag, et un tag
peut être associé à plusieurs notes.

### Services

`AuthService`, `NoteService` et `TagService` regroupent les opérations sur les
modèles. Par exemple, `NoteService` charge uniquement les notes de l'utilisateur,
crée une note pour ce propriétaire et gère les mises à jour ou suppressions. Ils
sont injectés dans les contrôleurs par le conteneur Laravel.

### Effets côté front

Les services HTTP du front sont regroupés dans `frontend/src/services/api.js` et
dans `authApi.js`, `notesApi.js` et `tagsApi.js`. Ils centralisent l'URL de base,
l'en-tête Bearer, les erreurs `401`, le cache et son invalidation. Les composants
d'affichage ne font donc pas de `fetch` directement.

## 5. Catalogue de l'API REST

URL de base en local : `http://project3.test/api/v1`.

| Méthode | URL | Authentification | Utilisation côté React | Succès |
|---|---|---|---|---|
| `POST` | `/register` | Publique | Créer un compte | `201` |
| `POST` | `/login` | Publique | Ouvrir une session | `200` |
| `POST` | `/logout` | Bearer | Révoquer le token courant | `200` |
| `GET` | `/notes` | Bearer | Afficher les notes et le dashboard | `200` |
| `POST` | `/notes` | Bearer | Créer une note | `201` |
| `GET` | `/notes/{id}` | Bearer | Obtenir une note précise | `200` |
| `PUT/PATCH` | `/notes/{id}` | Bearer | Modifier une note | `200` |
| `DELETE` | `/notes/{id}` | Bearer | Supprimer une note | `200` |
| `GET` | `/tags` | Bearer | Afficher les tags et alimenter les formulaires | `200` |
| `POST` | `/tags` | Bearer | Créer un tag | `201` |
| `GET` | `/tags/{id}` | Bearer | Obtenir un tag précis | `200` |
| `PUT/PATCH` | `/tags/{id}` | Bearer | Renommer un tag | `200` |
| `DELETE` | `/tags/{id}` | Bearer | Supprimer un tag inutilisé | `200` |

Les exemples `curl` et les corps de requête sont détaillés dans
[`API.md`](API.md).

### Exemples de réponses

Connexion réussie :

```json
{
  "status": "success",
  "message": "Authentification réussie.",
  "data": {
    "token": "1|token-sanctum",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "name": "Lara Croft",
      "email": "lara@example.com"
    }
  }
}
```

Création d'une note (`201`) :

```json
{
  "status": "success",
  "message": "Note créée.",
  "data": {
    "id": 12,
    "text": "Préparer la démonstration",
    "tag": {
      "id": 2,
      "name": "Travail",
      "created_at": "2026-10-01T14:00:00.000000Z",
      "updated_at": "2026-10-01T14:00:00.000000Z"
    },
    "created_at": "2026-10-02T08:30:00.000000Z",
    "updated_at": "2026-10-02T08:30:00.000000Z"
  }
}
```

Erreur de validation (`422`) :

```json
{
  "status": "error",
  "message": "Les données fournies sont invalides.",
  "data": {
    "errors": {
      "text": ["The text field is required."]
    }
  }
}
```

## 6. Exemple complet : création d'une note

1. L'utilisateur valide `NoteForm` avec un texte et un `tag_id`.
2. Le composant appelle la mutation `createNote` de RTK Query.
3. `api.js` ajoute `Accept: application/json`, `Content-Type: application/json` et
   le token Sanctum dans `Authorization: Bearer ...`.
4. `POST /api/v1/notes` traverse la route protégée par `auth:sanctum`.
5. `StoreNoteRequest` valide le corps de la requête.
6. `NoteController@store` délègue la création à `NoteService`.
7. Le service utilise la relation de l'utilisateur connecté pour créer la note ;
   l'identifiant du propriétaire ne vient donc jamais du navigateur.
8. Eloquent écrit en base, puis le service renvoie au contrôleur la note avec son tag.
9. `NoteResource` et `ApiResponse` construisent le JSON avec le code `201`.
10. RTK Query invalide les listes `Note` et `Tag`, recharge les données concernées
   puis React réaffiche l'écran sans rechargement complet de la page.

Ce trajet est illustré dans le PDF final.

## 7. Choix techniques

### React et Vite

React permet de séparer l'interface du serveur et de réutiliser la même API pour un
client mobile. Vite fournit le serveur de développement et la compilation de
production. L'adresse de l'API vient de `VITE_API_BASE_URL`, elle n'est pas inscrite
en dur dans les composants.

### Redux Toolkit et RTK Query

Redux Toolkit a été choisi pour son flux prévisible et ses outils de débogage. RTK
Query évite de recopier les notes et les tags dans plusieurs états : les données du
serveur restent dans son cache, tandis que `authSlice` ne conserve que la session.

### API versionnée et Sanctum

Le préfixe `/api/v1` permet de faire évoluer l'API sans casser immédiatement les
clients existants. Sanctum fournit des tokens adaptés à plusieurs clients. Les notes
sont filtrées par l'utilisateur authentifié et `NotePolicy` protège les opérations
sur une note précise.

### Séparation des responsabilités et SOLID

- **Responsabilité unique** : routes, validation, autorisation, services métier,
  persistance, représentation JSON et affichage React sont dans des fichiers
  différents.
- **Ouvert/fermé** : les endpoints RTK Query sont ajoutés par fonctionnalité et une
  nouvelle ressource API peut être ajoutée sans modifier les écrans existants.
- **Substitution et ségrégation des interfaces** : ces principes sont peu visibles
  ici, car le projet utilise peu d'héritage et ne définit pas de grandes interfaces
  métier. Aucune abstraction artificielle n'a été ajoutée uniquement pour les
  illustrer.
- **Inversion des dépendances** : Laravel injecte les services dans les contrôleurs
  via son conteneur et le front dépend d'un service API commun, pas d'appels HTTP
  dispersés dans les vues.

## 8. Sécurité et limites connues

- les mots de passe sont hachés et ne figurent jamais dans les réponses ;
- les entrées sont validées par les Form Requests ;
- les notes d'un autre utilisateur renvoient `403` ;
- une suppression de tag utilisé renvoie `409` ;
- les erreurs internes n'exposent pas de trace technique ;
- la connexion et l'inscription sont limitées par un throttle ;
- le token est conservé dans `sessionStorage` et supprimé après déconnexion ou
  réponse `401`.

Les écrans de profil, mot de passe et vérification d'e-mail restent temporairement en
Livewire car ils ne disposent pas encore d'endpoints REST. Le cœur métier Notes et
Tags est, lui, complètement séparé. En développement, `http://localhost:5173` n'est
accessible que pendant l'exécution de `npm run dev` dans `frontend/`.

## 9. Vérification

Les endpoints sont couverts par les tests Feature Laravel : authentification,
révocation du token, CRUD Notes, CRUD Tags, validation, autorisation, conflit et
CORS. Les appels principaux ont aussi été vérifiés manuellement avec Postman.

Dernière exécution du projet :

- 40 tests Laravel, 166 assertions ;
- 7 tests React ;
- compilation de production React réussie.

Commandes de contrôle :

```bash
php artisan test
cd frontend
npm test
npm run build
```
