# Sparxpres Websale module for Magento 2

## Install with composer

As with all Magento extensions, it is highly recommended to backup your site before installation, and to 
install and test on a staging environment prior to production deployments.

1. Log in to your server with SSH and go to the Magento 2 root folder.
2. Enter the following commands:

```
composer require sparxpres/module-websale
php bin/magento module:enable Sparxpres_Websale
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```

## Update with composer

Update the module to the latest available version.

1. Log in to your server with SSH and go to the Magento 2 root folder.
2. Enter the following commands

```
composer update sparxpres/module-websale
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```
