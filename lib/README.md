# Lib Directory

**You do not need run a Composer to use this plugin.**

This directory allows the plugin to be distributed with other packages included, but without the requirement 
for end users to use Composer. 

Note that each included package is prefixed with the namespace `Eighteen73\Orbit\Vendor` to avoid conflicts with other plugins/packages. 

## Adding Packages

1. Add you package in this directory using `composer require package/name --dev`
2. List your package in composer.json under "extra > mozart > packages"
3. Run `composer install` to trigger the post-install script

## Updating Packages

Run `composer update` within this directory.
