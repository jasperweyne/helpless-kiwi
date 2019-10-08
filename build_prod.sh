#!/bin/bash
# Check if currently in git repository
if [ -d ".git" ]; then
    echo Please move this script outside of the repository before running it!
    exit 1
fi

# Delete leftover files
echo Removing files from earlier runs, please wait...
rm -rf kiwi
rm -rf public_html

# Download the contents of the current git repo, and delete git files
git clone --depth=1 https://github.com/jasperweyne/helpless-kiwi kiwi
rm -rf kiwi/.git

# Move kiwi/public to public_html/kiwi
egrep -lRZ 'public/' kiwi | xargs -0 -l sed -i -e 's/public\//..\/public_html\/kiwi\//g'
sed -i -e 's/\/config\/bootstrap.php/\/..\/kiwi\/config\/bootstrap.php/g' kiwi/public/index.php
sed -i -e 's/\"extra\": {/\"extra\": {\n        \"public-dir\": \"..\/public_html\/kiwi\",/g' kiwi/composer.json
mkdir public_html
mv kiwi/public public_html/kiwi

# Download/build dependencies
cd kiwi
export APP_DEBUG=0 APP_ENV=prod
composer install --no-dev --optimize-autoloader
yarn install
yarn build
cd ../

# Remove files redundant for operation 
echo Removing files redundant for operation, please wait...
rm kiwi/* 2> /dev/null
rm -rf kiwi/.github
rm -rf kiwi/.hooks
rm -rf kiwi/assets
rm -rf kiwi/bin
rm -rf kiwi/node_modules
rm -rf kiwi/var

# Create environment variable file
cat > kiwi/.env.local.php << EOL
<?php

return array (
    'APP_DEBUG' => '0',
    'APP_ENV' => 'prod',
    'APP_SECRET' => '6badc0fca270ab84a00a67226f9e2554',
    'USERPROVIDER_KEY' => 'ThisIsNotSoSecret',
    'DATABASE_URL' => 'mysql://db_user:db_pass@127.0.0.1:3306/db',
    'MAILER_URL' => 'null://localhost',
    'DEFAULT_FROM' => 'foo@bar.com',
);
EOL
echo Please edit '.env.local.php' and push the code to your server
