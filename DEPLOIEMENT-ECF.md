# Déploiement ECF — plateformes autorisées par le sujet

Le sujet impose : **Fly.io**, **Heroku**, **Azure** ou **Vercel**.

## Recommandation : Fly.io (le plus simple pour PHP + MySQL)

Le projet inclut déjà un `Dockerfile` (PHP 8.2, Apache, MySQL, MongoDB).

### Prérequis

- Compte [Fly.io](https://fly.io)
- [flyctl](https://fly.io/docs/hands-on/install-flyctl/) installé
- MySQL externe (Railway avec **TCP Proxy** activé — obligatoire) ou autre
- [MongoDB Atlas](https://www.mongodb.com/cloud/atlas) (gratuit M0) pour les stats admin

### Déployer sur Fly.io

```bash
cd C:\Env\Worksapce\ECF
fly auth login
fly launch --no-deploy
# Railway : MySQL → Settings → Networking → Enable TCP Proxy
# Copier MYSQL_PUBLIC_URL (ex. mysql://root:xxx@shuttle.proxy.rlwy.net:18432/railway)
fly secrets set DATABASE_URL="mysql://root:TON_MDP@shuttle.proxy.rlwy.net:18432/railway"
fly secrets unset DB_NAME
fly secrets set MONGO_URI="mongodb+srv://USER:PASS@cluster.mongodb.net"
fly secrets set SMTP_HOST=... SMTP_USER=... SMTP_PASS=...
fly deploy
```

### Installer la base (1 visite navigateur)

```
https://ecf-vite-gourmand.fly.dev/install.php?key=vitegourmand2026
```

### URL à fournir au jury

```
https://ecf-vite-gourmand.fly.dev
```

---

## Vercel (possible mais plus complexe)

- Fichiers : `vercel.json`, `api/index.php`
- MySQL externe obligatoire (Railway + TCP Proxy)
- Variables : `DATABASE_URL`, `DB_NAME`, `DB_SSL=0`
- Install : `/install.php?key=vitegourmand2026`

---

## Heroku

- Fichier `Procfile` inclus
- Buildpack PHP Heroku + addon MySQL (JawsDB, ClearDB)
- Extension `mongodb` peut nécessiter une config buildpack avancée
- Moins recommandé pour ce projet (MongoDB + PHP)

---

## Azure

- **App Service** → déployer le `Dockerfile` (Web App for Containers)
- **Azure Database for MySQL** ou MySQL externe
- **MongoDB Atlas** pour les stats
- Variables d'application dans Configuration → Paramètres

---

## Comparaison rapide

| Plateforme | PHP natif | MySQL | MongoDB | Difficulté |
|------------|-----------|-------|---------|------------|
| **Fly.io** | Docker | Externe | Atlas | Moyenne |
| Vercel | Runtime communautaire | Externe | Atlas | Élevée |
| Heroku | Buildpack | Addon | Difficile | Élevée |
| Azure | Docker/App Service | Azure MySQL | Atlas | Élevée |
