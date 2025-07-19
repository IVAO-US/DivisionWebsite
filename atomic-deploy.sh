#!/bin/bash

echo "Detecting OS..."
# Detect OS
if [[ "$OSTYPE" == "msys" || "$OSTYPE" == "win32" ]]; then
    OS="windows"
else
    OS="linux"
fi

echo "Deploying new version..."

# Windows deployment (Laragon...)
if [[ "$OS" == "windows" ]]; then
    LARAVEL_APP=$(pwd)
    ATOMIC_DEPLOYMENT_PATH="..\\__atomic-deployment"

    # Making sure there is not a corrupted previous deployment
    if [ -d "$ATOMIC_DEPLOYMENT_PATH" ]; then
        rm -rf "$ATOMIC_DEPLOYMENT_PATH"
    fi
    mkdir -p "$ATOMIC_DEPLOYMENT_PATH"

    echo "Running Windows => using robocopy"
    robocopy "$LARAVEL_APP" "$ATOMIC_DEPLOYMENT_PATH" //E //Z //NFL //NDL //XD ".git"  # Exclude .git folder

    # Compiling Composer dependencies
    cd "$ATOMIC_DEPLOYMENT_PATH"
    echo "Composer install is being run..."
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
    echo "Caching Laravel packages..."
    php artisan package:discover

    # Compiling assets with Vite
    echo "Compiling assets using Vite..."
    npm cache verify
    npm ci
    npm audit fix
    npm run build

    # Necessary downtime
    cd "$LARAVEL_APP"
    php artisan down

    # Moving new files to the active production folder
    echo "Files are being deployed... Please wait..."
    robocopy "$ATOMIC_DEPLOYMENT_PATH" "$LARAVEL_APP" //E //Z //NFL //NDL

    # Executing migrations
    echo "Performing migrations..."
    php artisan migrate --force # Add the --seed flag if seeding is necessary or manually run it after deployment.

    # Removing maintenance mode
    echo "Laravel App is now UP AND RUNNING."
    php artisan up

    # Clear cached app data
    echo "Clearing Laravel caches..."
    php artisan optimize:clear
    php artisan route:clear
    php artisan view:clear
    php artisan cache:clear

    echo "Clearing auth tokens.."
    php artisan auth:clear-resets
    
    # Caching new app data
    echo "Caching Laravel data..."
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
    php artisan icons:cache
    
    echo "Final Laravel optimization..."
    php artisan optimize
    php artisan config:clear

    echo "Optimization complete!"

    # Removing old version to save up disk space
    echo "Removing temporary deployment folder..."
    rm -rf "$ATOMIC_DEPLOYMENT_PATH"

    echo "The application has been deployed SUCCESSFULLY!"
fi

# Linux deployment (Plesk...)
if [[ "$OS" == "linux" ]]; then
    LARAVEL_APP=$(pwd)
    ATOMIC_DEPLOYMENT_PATH="../__atomic-deployment"

    # Making sure there is not a corrupted previous deployment
    if [ -d "$ATOMIC_DEPLOYMENT_PATH" ]; then
        rm -rf "$ATOMIC_DEPLOYMENT_PATH"
    fi
    mkdir -p "$ATOMIC_DEPLOYMENT_PATH"

    # Copying files
    echo "Running Linux => using rsync"
    rsync -avp --exclude='.git' "$LARAVEL_APP/" "$ATOMIC_DEPLOYMENT_PATH/"

    # Compiling Composer dependencies
    cd "$ATOMIC_DEPLOYMENT_PATH"
    echo "Composer install is being run..."
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
    echo "Caching Laravel packages..."
    php artisan package:discover

    # Compiling assets with Vite
    echo "Compiling assets using Vite..."
    npm cache verify
    npm ci
    npm audit fix
    npm run build

    # Necessary downtime
    cd "$LARAVEL_APP"
    php artisan down

    # Moving new files to the active production folder
    echo "Files are being deployed... Please wait..."
    rsync -avp "$ATOMIC_DEPLOYMENT_PATH/" "$LARAVEL_APP/"

    # Executing migrations
    echo "Performing migrations..."
    php artisan migrate --force # Add the --seed flag if seeding is necessary or manually run it after deployment.

    # Removing maintenance mode
    echo "Laravel App is now UP AND RUNNING."
    php artisan up

    # Clear cached app data
    echo "Clearing Laravel caches..."
    php artisan optimize:clear
    php artisan route:clear
    php artisan view:clear
    php artisan cache:clear

    echo "Clearing auth tokens.."
    php artisan auth:clear-resets
    
    # Caching new app data
    echo "Caching Laravel data..."
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
    php artisan icons:cache
    
    echo "Final Laravel optimization..."
    php artisan optimize
    php artisan config:clear

    echo "Optimization complete!"

    # Removing old version to save up disk space
    echo "Removing temporary deployment folder..."
    rm -rf "$ATOMIC_DEPLOYMENT_PATH"

    echo "The application has been deployed SUCCESSFULLY!"
fi