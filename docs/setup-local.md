# Installation locale du projet Factu

## Prérequis

- **Laragon** (installé dans `D:/laragon`)
  - PHP 8.3.28
  - MySQL 8.4.3
- **Node.js** (pour Webpack Encore)

## Stack technique

| Composant | Technologie |
|-----------|-------------|
| Backend | Symfony |
| Frontend | Webpack Encore + Tailwind CSS 4 |
| Base de données | MySQL 8.4 |
| UI Components | Flowbite |
| Interactivité | Hotwire (Turbo + Stimulus) |
| Charts | Chart.js (via UX) |

## Configuration

### Base de données

La configuration est dans `.env.local` :

```
DATABASE_URL="mysql://root:@127.0.0.1:3306/factu_db?serverVersion=8.4.3&charset=utf8mb4"
```

### Créer la base de données

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

## Lancer le projet

### 1. Démarrer Laragon

Ouvre Laragon et démarre les services (Apache/Nginx n'est pas nécessaire, seulement MySQL).

### 2. Lancer le serveur PHP

Via le terminal Laragon ou avec le chemin complet :

```bash
# Terminal Laragon
cd D:\ClaudeProjects\Factu\app
php -S localhost:8000 -t public

# Ou avec chemin complet (bash)
/d/laragon/bin/php/php-8.3.28-Win32-vs16-x64/php.exe -S localhost:8000 -t /d/ClaudeProjects/Factu/app/public
```

### 3. Lancer Webpack (compilation des assets)

```bash
cd D:\ClaudeProjects\Factu\app
npm run watch
```

### 4. Accéder à l'application

Ouvrir http://localhost:8000

## Credentials de test

Les credentials de test sont dans le fichier `.credentials.local` (gitignored).

Pour créer un nouvel utilisateur de test :

```bash
# Générer un hash de mot de passe
php bin/console security:hash-password <mot_de_passe>

# Insérer en base (via MySQL/phpMyAdmin)
INSERT INTO `user` (nom, email, roles, password, actif, created_at, updated_at)
VALUES ('Nom', 'email@test.com', '["ROLE_ADMIN"]', '<hash>', 1, NOW(), NOW());
```

## MCP Chrome (optionnel)

Pour permettre à Claude Code d'interagir avec le navigateur, le MCP Puppeteer est configuré dans `~/.claude.json` :

```json
{
  "mcpServers": {
    "puppeteer": {
      "command": "npx",
      "args": ["-y", "puppeteer-mcp-claude"]
    }
  }
}
```

Redémarrer Claude Code après modification pour activer le MCP.

## Structure du projet

```
Factu/
├── app/                    # Application Symfony
│   ├── assets/             # Assets frontend (JS, CSS)
│   ├── config/             # Configuration Symfony
│   ├── migrations/         # Migrations Doctrine
│   ├── public/             # Point d'entrée web
│   ├── src/                # Code source PHP
│   │   ├── Controller/     # Contrôleurs
│   │   ├── Entity/         # Entités Doctrine
│   │   └── Repository/     # Repositories
│   ├── templates/          # Templates Twig
│   ├── .env.local          # Configuration locale (gitignored)
│   └── .credentials.local  # Credentials de test (gitignored)
├── docs/                   # Documentation
└── _bmad/                  # Configuration BMAD
```
