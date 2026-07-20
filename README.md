# Vite & Gourmand — ECF

Application web de commande en ligne pour le traiteur bordelais **Vite & Gourmand**.

## Liens jury

| Livrable | URL |
|----------|-----|
| **Application déployée** | https://ecf-vite-et-gourmand-self.vercel.app |
| **Dépôt GitHub** | https://github.com/OFF-8/ecf-vite-et-gourmand |
| **Documentation / PDFs** | dossier [`docs/`](docs/README.md) |
| **Gestion de projet** | [Trello — Vite & Gourmand ECF](https://trello.com/invite/b/6a5e2f00640b0f31282bfed2/ATTIb8688758d04ecbbb412ef234c94f18d51CA01475/vite-gourmand-ecf) |

## Stack technique

- **Front** : HTML5, CSS, Bootstrap 5, JavaScript
- **Back** : PHP 8 + PDO
- **Base relationnelle** : MySQL (`vite_et_gourmand`)
- **Base NoSQL** : MongoDB (statistiques admin)
- **Emails** : PHPMailer (SMTP)
- **Dépôt** : [OFF-8/ecf-vite-et-gourmand](https://github.com/OFF-8/ecf-vite-et-gourmand)

## Prérequis

- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP 8+)
- [MongoDB Community Server](https://www.mongodb.com/try/download/community)
- Extension PHP `mongodb` activée dans `php.ini` :
  ```ini
  extension=mongodb
  ```
- Git

## Installation locale

### 1. Cloner le projet

```bash
git clone https://github.com/OFF-8/ecf-vite-et-gourmand.git
cd ecf-vite-et-gourmand
```

### 2. Installer PHPMailer

**Avec Composer** (recommandé) :

```bash
composer install
```

**Sans Composer** :

```bash
git clone --depth 1 https://github.com/PHPMailer/PHPMailer.git vendor/phpmailer/phpmailer
```

### 3. Lier le projet à Apache (junction Windows)

```powershell
New-Item -ItemType Junction -Path "C:\Env\xamp\htdocs\ecf" -Target "C:\Env\Worksapce\ECF"
```

> Adapter les chemins si ton XAMPP ou ton workspace est ailleurs.

### 4. Base MySQL

1. Démarrer Apache et MySQL dans XAMPP.
2. Ouvrir phpMyAdmin : `http://localhost/phpmyadmin`
3. Exécuter dans l'ordre :
   - `sql/creation_bdd.sql`
   - `sql/fixtures.sql`

### 5. MongoDB

1. Démarrer le service MongoDB (`mongod`).
2. Vérifier la connexion dans `config/mongodb.php` :
   - URI : `mongodb://localhost:27017`
   - Base : `vite_et_gourmand`
   - Collection : `commandes_stats`

Les stats sont alimentées à chaque **création de commande**.

### 6. Configuration PHP

Fichier `config/database.php` (valeurs XAMPP par défaut) :

| Paramètre | Valeur |
|-----------|--------|
| Host      | `localhost` |
| Base      | `vite_et_gourmand` |
| User      | `root` |
| Password  | *(vide)* |

### 7. Configuration email

1. Copier `config/mail.local.php.example` vers `config/mail.local.php`
2. Renseigner les identifiants SMTP (ex. [Mailtrap](https://mailtrap.io) en développement)
3. Sans `mail.local.php`, l'application utilise la fonction PHP `mail()` en secours

### 8. Accès à l'application

URL : **http://localhost/ecf/**

## Comptes de test

| Rôle        | Email                         | Mot de passe         |
|-------------|-------------------------------|----------------------|
| Admin       | `jose@vite-et-gourmand.fr`    | `Admin#Jose2026!`    |
| Employé     | `julie@vite-et-gourmand.fr`   | `Employe#Julie2026!` |
| Client      | `client@test.fr`              | `Client#Test2026!`   |

> Le compte admin est créé uniquement en base (exigence du sujet).

## Structure du projet

```
├── admin/          # Espace administrateur (employés, stats MongoDB)
├── employe/        # Espace employé (commandes, menus, plats, avis)
├── api/            # Endpoints JSON (menus)
├── asset/          # CSS, JS
├── config/         # database.php, mongodb.php, mail.php, app.php
├── includes/       # header, footer, auth, stats MongoDB
├── sql/            # Schéma + données de test
└── *.php           # Pages publiques et utilisateur
```

## Fonctionnalités principales

- **Public** : accueil, menus filtrables, détail menu, contact, CGV, mentions légales
- **Utilisateur** : inscription, connexion, profil, commande, suivi, avis
- **Employé** : gestion commandes/statuts, menus, plats, horaires, modération avis
- **Admin** : gestion employés, statistiques (Chart.js + MongoDB)

## Emails envoyés

| Événement | Fichier |
|-----------|---------|
| Bienvenue (inscription) | `inscription.php` |
| Confirmation de commande | `commande.php` |
| Réinitialisation mot de passe | `mot-de-passe-oublie.php` |
| Formulaire de contact | `contact.php` |
| Notification employé | `admin/employes.php` |
| Retour matériel / invitation avis | `employe/commandes.php` |

## Workflow Git

- `main` : production
- `develop` : intégration
- `feature/*` : développement par fonctionnalité

## Déploiement (obligatoire ECF)

> **Production actuelle :** https://ecf-vite-et-gourmand-self.vercel.app (Vercel + MySQL Railway).

> **Recommandé : [AlwaysData](https://www.alwaysdata.com)** — PHP + MySQL natif, phpMyAdmin, gratuit.  
> Vercel convient au JavaScript (dropshipping), pas au PHP/MySQL.

### AlwaysData — guide rapide

1. Créer un compte sur [alwaysdata.com](https://www.alwaysdata.com)
2. **Web → Sites → Ajouter** (PHP 8.2+)
3. **Bases de données → Ajouter** (MySQL) — noter hôte, base, user, mot de passe
4. **phpMyAdmin** → Importer `sql/creation_bdd.sql` puis `sql/fixtures.sql`
5. **FTP/SFTP** → envoyer les fichiers du projet dans `www/`
6. Créer `config/local.php` sur le serveur (copier depuis `config/local.php.example`) :

```php
<?php
putenv('DB_HOST=mysql-xxx.alwaysdata.net');
putenv('DB_PORT=3306');
putenv('DB_NAME=ton_compte_vite_et_gourmand');
putenv('DB_PASSWORD=...');
putenv('DB_USER=ton_compte');
putenv('DB_SSL=0');
// Optionnel — stats admin :
putenv('MONGO_URI=mongodb+srv://...');
```

7. Installer PHPMailer sur le serveur :
   `git clone --depth 1 https://github.com/PHPMailer/PHPMailer.git vendor/phpmailer/phpmailer`
8. URL à fournir au jury : `https://ton-site.alwaysdata.net`

### Livrables déploiement pour le jury

- [ ] URL publique fonctionnelle
- [ ] Dépôt GitHub à jour
- [ ] README avec instructions d'installation
- [ ] Comptes de test documentés
- [ ] MongoDB Atlas (optionnel, pour stats admin)

---

### Option B — Vercel + MySQL externe (avancé, non recommandé)

> Stack : runtime communautaire [vercel-php](https://github.com/vercel-community/php) (PHP 8.5, PDO MySQL, MongoDB).

#### Prérequis externes (gratuits)

1. **MySQL** — [Railway](https://railway.app), [PlanetScale](https://planetscale.com) ou [Aiven](https://aiven.io)
   - Importer `sql/creation_bdd.sql` puis `sql/fixtures.sql`
2. **MongoDB Atlas** — cluster M0 gratuit sur [mongodb.com/cloud/atlas](https://www.mongodb.com/cloud/atlas)
3. **SMTP** — Mailtrap (dev) ou Gmail (mot de passe d'application)

#### Déployer sur Vercel (comme le dropshipping)

1. **GitHub** → connecter le dépôt à [Vercel](https://vercel.com) (auto-deploy à chaque `git push`)
2. **Railway** → créer MySQL → copier `MYSQL_PUBLIC_URL`
3. **Vercel** → Settings → Environment Variables :

| Variable | Valeur |
|----------|--------|
| `DATABASE_URL` | `MYSQL_PUBLIC_URL` de Railway |
| `DB_NAME` | `vite_et_gourmand` |
| `DB_SSL` | `1` |

4. **Redeploy** sur Vercel
5. Ouvrir **une seule fois** : `https://ton-site.vercel.app/install.php?key=vitegourmand2026`  
   → crée les tables et les données automatiquement (plus besoin de DBeaver)

#### Déployer sur Vercel (détail)

| Variable | Description |
|----------|-------------|
| `DB_HOST` | Hôte MySQL distant |
| `DB_PORT` | `3306` |
| `DB_NAME` | `vite_et_gourmand` |
| `DB_USER` | Utilisateur MySQL |
| `DB_PASSWORD` | Mot de passe MySQL |
| `MONGO_URI` | `mongodb+srv://USER:PASS@cluster...mongodb.net` |
| `MONGO_DATABASE` | `vite_et_gourmand` |
| `SMTP_HOST` | Serveur SMTP |
| `SMTP_USER` | Identifiant SMTP |
| `SMTP_PASS` | Mot de passe SMTP |

5. Cliquer **Deploy** — URL : `https://ton-projet.vercel.app`

#### CLI (alternative)

```bash
npm i -g vercel
vercel login
vercel
```

#### Fichiers Vercel du projet

| Fichier | Rôle |
|---------|------|
| `vercel.json` | Runtime PHP + routage |
| `api/index.php` | Routeur vers les pages PHP |
| `api/get-menus.php` | API JSON menus |
| `package.json` | Build des assets statiques |
| `.vercelignore` | Fichiers exclus du déploiement |

#### Limitation sessions

Vercel utilise des fonctions serverless : les sessions PHP peuvent être instables entre requêtes. Pour un usage production, prévoir Redis (Upstash) ou sessions en base. Pour la démo ECF, cela fonctionne en général sur instances « warm ».

---

### Option B — Docker local (test prod)

Prérequis : [Docker Desktop](https://www.docker.com/products/docker-desktop/)

```bash
docker compose up --build
```

Application accessible sur **http://localhost:8080**

Les scripts SQL sont importés automatiquement au premier démarrage MySQL.

### Option C — Render + MongoDB Atlas + MySQL externe

#### 1. MongoDB Atlas (gratuit M0)

1. Créer un cluster sur [MongoDB Atlas](https://www.mongodb.com/cloud/atlas)
2. **Database Access** : créer un utilisateur
3. **Network Access** : autoriser `0.0.0.0/0` (ou l'IP Render)
4. Copier l'URI de connexion :
   ```
   mongodb+srv://USER:PASS@cluster.xxxxx.mongodb.net/vite_et_gourmand
   ```

#### 2. MySQL externe

Render ne propose pas MySQL nativement. Options gratuites / low-cost :

- [Railway](https://railway.app) — addon MySQL
- [Aiven](https://aiven.io) — essai gratuit
- Base MySQL de ton hébergeur

Importer `sql/creation_bdd.sql` puis `sql/fixtures.sql` via phpMyAdmin ou client MySQL.

#### 3. Déployer sur Render

1. Pousser le code sur GitHub
2. [Render Dashboard](https://dashboard.render.com) → **New** → **Blueprint**
3. Connecter le dépôt — Render lit `render.yaml`
4. Renseigner les variables d'environnement :

| Variable | Exemple |
|----------|---------|
| `DB_HOST` | `containers-us-west-xxx.railway.app` |
| `DB_PORT` | `3306` |
| `DB_NAME` | `vite_et_gourmand` |
| `DB_USER` | `root` |
| `DB_PASSWORD` | `***` |
| `MONGO_URI` | `mongodb+srv://...` |
| `SMTP_HOST` | `smtp.gmail.com` ou Mailtrap |
| `SMTP_USER` | identifiant SMTP |
| `SMTP_PASS` | mot de passe SMTP |

5. Déployer — URL fournie : `https://vite-et-gourmand.onrender.com`

#### 4. Configuration locale vs prod

| Fichier | Usage |
|---------|-------|
| `config/local.php` | Surcharge locale (copier depuis `local.php.example`) |
| Variables d'env | Prod Render / Docker (prioritaires via `config/env.php`) |
| `config/mail.local.php` | SMTP en dev local (alternative aux variables d'env) |

### Checklist post-déploiement

- [ ] Connexion admin (`jose@vite-et-gourmand.fr`)
- [ ] Création d'une commande test
- [ ] Stats admin MongoDB visibles
- [ ] Email de confirmation reçu
- [ ] Reset mot de passe (lien HTTPS correct)
- [ ] Navigation depuis `admin/` et `employe/`

## Auteurs

Projet réalisé dans le cadre de l'ECF — DWWM.
