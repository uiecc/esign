#!/bin/bash

set -e

cd domains/esign.cm/public_html || exit 1

echo "📦 Sauvegarde des modifications locales..."
git stash --include-untracked

echo "🔄 Mise à jour du code..."
git pull --quiet || exit 1

echo "📦 Restauration des modifications locales..."
git stash pop || exit 0  # Ignore les erreurs si rien n'est à restaurer

echo "📦 Mise à jour des dépendances..."
php composer.phar install --no-interaction --no-progress --prefer-dist || exit 1

echo "🔄 Mise à jour du schéma de la base de données..."
php bin/console doctrine:schema:update --force --no-interaction || exit 1

php bin/console tailwind:build --minify
php bin/console assets:install 
php bin/console asset-map:compile
echo "✅ Déploiement terminé"
