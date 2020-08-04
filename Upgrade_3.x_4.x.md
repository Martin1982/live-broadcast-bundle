# Upgrading from 3.x to 4.x

When upgrading your Live Broadcast Bundle from version 3.x to 4.x you'll lose the admin GUI web interface.
This package has been decoupled and moved to https://github.com/Martin1982/live-broadcast-sonata-admin-bundle

To fix this, it is recommended to change to this new bundle package;

``
composer require martin1982/live-broadcast-sonata-admin-bundle
composer remove martin1982/live-broadcast-bundle
``

You should now have the Sonata admin together with the bundle as it is a dependency. All other configurations will
stay the same.
