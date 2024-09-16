

# CinePS API - README

## database management

    update schema:
        php bin/console doctrine:schema:update --complete --force

    load test data set:
        php bin/console doctrine:fixtures:load