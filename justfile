build:
    composer install

test:
    composer test

check:
    composer check

publish:
    # tag release and push; Packagist auto-syncs from GitHub

gen-doc:
    docker run --rm --user "$(id -u):$(id -g)" --volume "$PWD:/data" phpdoc/phpdoc:3 --directory src --target docs --title 'Identisure PHP Client'
