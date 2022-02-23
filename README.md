# Sparxpres Websale module for Magento 2

## Installation

Install via Composer:

```
composer require sparxpres/module-websale
php bin/magento module:enable Sparxpres_Websale
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```

## Update

To update the extension to the latest available version (depending on your `composer.json`), run these commands in your terminal:

```
composer update sparxpres/module-websale --with-dependencies
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```

##  Install Manually

Paste the folder into:
- [Magento directory]/app/code/Sparxpres/Websale

Then run the following commands in the [Magento directory]:

```
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```
