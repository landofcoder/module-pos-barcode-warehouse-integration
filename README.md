Mage2 Module Lof BarcodeWarehouseIntegraion
``lof/module-BarcodeWarehouseIntegraion``
Main Functionalities
Installation
Configuration
Specifications
Attributes
Main Functionalities
Installation
* = in production please use the --keep-generated option

Type 1: Zip file
Unzip the zip file in app/code/Lof
Enable the module by running php bin/magento module:enable Lof_BarcodeWarehouseIntegraion
Apply database updates by running php bin/magento setup:upgrade*
Flush the cache by running php bin/magento cache:flush
Type 2: Composer
Make the module available in a composer repository for example:
private repository repo.magento.com
public repository packagist.org
public github repository as vcs
Add the composer repository to the configuration by running composer config repositories.repo.magento.com composer https://repo.magento.com/
Install the module composer by running composer require lof/module-BarcodeWarehouseIntegraion
enable the module by running php bin/magento module:enable Lof_BarcodeWarehouseIntegraion
apply database updates by running php bin/magento setup:upgrade*
Flush the cache by running php bin/magento cache:flush
Configuration
Specifications
Attributes