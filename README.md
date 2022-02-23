# Sparxpres Websale module for Magento 2

## Install with composer

The commands must be run from your [Magento directory].

To add Sparxpres git repository to your Magento composer, run the command:

```
composer config repositories.sparxpres-magento2 vcs "git@github.com:sparxpres/magento-2.git"
```

To install Sparxpres Websale module, run the following commands:

```
composer require sparxpres/module-websale
php bin/magento module:enable Sparxpres_Websale
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```

## Update with composer

To update the module to the latest available version (depending on your `composer.json`), run these commands (from your [Magento directory]):

```
composer update sparxpres/module-websale --with-dependencies
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```

##  Install Manually

Paste the folder into:
- [Magento directory]/app/code/Sparxpres/Websale

Then run the following commands from your [Magento directory]:
```
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```
