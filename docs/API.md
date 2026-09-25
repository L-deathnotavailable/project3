# API REST Renote

## Principes

- URL de base avec Herd : `http://project3.test/api/v1`
- Format : JSON
- Authentification : jeton Bearer Laravel Sanctum
- Les notes sont privées et limitées à leur propriétaire.
- Les tags constituent un catalogue partagé entre les utilisateurs authentifiés.
- Un tag associé à une note ne peut pas être supprimé.

Toutes les réponses respectent le même format :

```json
{
  "status": "success",
  "message": "Note créée.",
  "data": {}
}
```

En cas d'erreur, `status` vaut `error` et `data` contient éventuellement les erreurs de validation.

## Authentification

### Créer un compte et recevoir un jeton

`POST /register`

```bash
curl -X POST http://project3.test/api/v1/register \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"name":"Lara Croft","email":"lara@example.com","password":"password123","password_confirmation":"password123","device_name":"postman"}'
```

Une inscription réussie renvoie le compte créé et un jeton avec le statut HTTP `201`.

### Créer un jeton

`POST /login`

```bash
curl -X POST http://project3.test/api/v1/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password","device_name":"postman"}'
```

La valeur `data.token` doit ensuite être transmise :

```text
Authorization: Bearer VOTRE_JETON
Accept: application/json
```

### Révoquer le jeton courant

`POST /logout`

## Endpoints Notes

| Méthode | URL | Description | Statut de succès |
|---|---|---|---|
| `GET` | `/notes` | Lister ses notes | `200` |
| `POST` | `/notes` | Créer une note | `201` |
| `GET` | `/notes/{id}` | Consulter une note | `200` |
| `PUT/PATCH` | `/notes/{id}` | Modifier une note | `200` |
| `DELETE` | `/notes/{id}` | Supprimer une note | `200` |

Corps de création ou de modification :

```json
{
  "text": "Préparer la démonstration",
  "tag_id": 1
}
```

Exemple de création :

```bash
curl -X POST http://project3.test/api/v1/notes \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer VOTRE_JETON" \
  -d '{"text":"Préparer la démonstration","tag_id":1}'
```

## Endpoints Tags

| Méthode | URL | Description | Statut de succès |
|---|---|---|---|
| `GET` | `/tags` | Lister les tags | `200` |
| `POST` | `/tags` | Créer un tag | `201` |
| `GET` | `/tags/{id}` | Consulter un tag | `200` |
| `PUT/PATCH` | `/tags/{id}` | Renommer un tag | `200` |
| `DELETE` | `/tags/{id}` | Supprimer un tag inutilisé | `200` |

## Codes d'erreur

| Code | Signification |
|---|---|
| `401` | Jeton absent, expiré ou identifiants invalides |
| `403` | Accès à la note d'un autre utilisateur |
| `404` | Ressource introuvable |
| `409` | Suppression impossible car la ressource est utilisée |
| `422` | Données invalides |
| `429` | Trop de tentatives de connexion |
| `500` | Erreur interne sans exposition de détails sensibles |

Avant le premier appel, exécuter les migrations :

```bash
php artisan migrate
```
