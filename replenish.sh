#!/bin/sh
export REPOSITORY="GEWIS/gewisdb"
export BRANCH="main"
cd /tmp
wget --no-cache "https://github.com/${REPOSITORY}/archive/refs/heads/${BRANCH}.zip"
unzip "${BRANCH}.zip"
rm "${BRANCH}.zip"
cp -R -u gewisdb-${BRANCH}/public/* /code/public/
chown -R  www-data:www-data /code/public
cp -R -u gewisdb-${BRANCH}/data/* /code/data/
chown -R  www-data:www-data /code/data
rm -R /tmp/gewisdb-${BRANCH}
cd /code
if [ "${APP_ENV}" == 'production' ]
then
    composer dump-autoload -o --no-dev
else
    composer dump-autoload -o
fi
./orm orm:generate-proxies
/bin/sh -c "EM_ALIAS=orm_report ./orm orm:generate-proxies"
rm -Rf /code/data/cache/*
