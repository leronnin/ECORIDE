## Installation SGBD SQL gratuit (Windows) + base `ecoride`

Ce projet est prévu pour fonctionner avec **MariaDB/MySQL** (compatible phpMyAdmin).

### Option recommandée: XAMPP (gratuit) = Apache + MariaDB + phpMyAdmin

1) Installer XAMPP via `winget` (mode silencieux) :

```powershell
winget install --id ApacheFriends.Xampp.8.2 --accept-package-agreements --accept-source-agreements --silent
```

2) Ouvrir **XAMPP Control Panel**, démarrer:
- **Apache**
- **MySQL**

3) Ouvrir phpMyAdmin:
- `http://localhost/phpmyadmin`

4) Importer le schéma:
- onglet **Importer**
- fichier: `db/ecoride_schema.sql`

### Config `data.php`

Par défaut, `data.php` se connecte à:
- host: `localhost`
- db: `ecoride`
- user: `root`
- pass: *(vide)*

Si besoin, tu peux surcharger via variables d’environnement:
- `ECORIDE_DB_HOST`
- `ECORIDE_DB_NAME`
- `ECORIDE_DB_USER`
- `ECORIDE_DB_PASS`

