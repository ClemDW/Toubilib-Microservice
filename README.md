## Projet toubilib

# Fonctionnalités principales :

- Fonctionnalité 1 : ✅
- Fonctionnalité 2 : ✅
- Fonctionnalité 3 : ✅
- Fonctionnalité 4 : ✅
- Fonctionnalité 5 : ✅
- Fonctionnalité 6 : ✅
- Fonctionnalité 7 : ✅
- Fonctionnalité 8 : ✅

# Fonctionnalités additionnelles :

- Fonctionnalité 9 : ✅
- Fonctionnalité 10 : ✅
- Fonctionnalité 11 : ✅
- Fonctionnalité 12 : ✅
- Fonctionnalité 13 : ✅

# Toubilib version microservice ✅

# Développeur :

Besançon Marcelin DW1

## Lancer le projet

1. Clonez le dépôt si ce n'est pas déjà fait :

   ```bash
   git clone <url-du-repo>
   cd FORK_toubilib
   ```

2. Démarrez tous les services avec Docker Compose :

   ```bash
   docker-compose up --build
   ```

## Structure du projet

Le projet est découpé en plusieurs dossiers correspondant à chaque microservice :

- `app/`, `app-auth/`, `app-mail/`, `app-praticiens/`, `app-rdv/`.
- Un dossier `gateway/` pour la passerelle d'API.
- Un dossier `sql/` pour les scripts de base de données.
