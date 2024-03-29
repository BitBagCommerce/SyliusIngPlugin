## Installation

### Sylius configuration

1. Require our plugin with composer:

    ```bash
    composer require bitbag/sylius-ing-plugin
    ```
    
    **Note.** If you receive an error related to `Twig\Extra\Intl\IntlExtension` class, please go to `config/packages/twig.yaml` file and remove the mentioned service definition. In some cases, multiple dependencies redefine the service, so it results the error message. After removing it, please run the composer command second time, to finish the step.

2. The [Sylius Refund Plugin](https://github.com/Sylius/RefundPlugin) is our plugin dependency. Please complete [its installation steps](https://github.com/Sylius/RefundPlugin/blob/1.4/docs/legacy_installation.md) before continuing.

3. Add plugin dependencies to your `config/bundles.php` file:

    ```php
    return [
        ...
        BitBag\SyliusIngPlugin\BitBagSyliusIngPlugin::class => ['all' => true],
    ];
    ```

4. Import required config in your `config/packages/_sylius.yaml` file:

    ```yaml
    # config/packages/_sylius.yaml

    imports:
        ...
        - { resource: "@SyliusIngPlugin/Resources/config.yaml" }
    ```

5. Import the routing in your `config/routes.yaml` file:

    ```yaml
    # config/routes.yaml

    bitbag_sylius_ing_plugin:
        resource: "@SyliusIngPlugin/Resources/config/routing.yaml"
    ```

6. Add ING as a supported refund gateway in `config/packages/_sylius.yaml`:

    ```yaml
    # config/packages/_sylius.yaml

       parameters:
          sylius_refund.supported_gateways:
              - offline
              - bitbag_imoje
    ``` 

7. Copy Sylius templates overridden by the plug-in to your templates directory (`templates/bundles/`):

    ```
    mkdir -p templates/bundles/SyliusAdminBundle/
    mkdir -p templates/bundles/SyliusShopBundle/

    cp -R vendor/bitbag/sylius-ing-plugin/tests/Application/templates/bundles/SyliusAdminBundle/* templates/bundles/SyliusAdminBundle/
    cp -R vendor/bitbag/sylius-ing-plugin/tests/Application/templates/bundles/SyliusShopBundle/* templates/bundles/SyliusShopBundle/
    ```

    **Note.** If you have overridden at least one template from the directories above, please adjust your code to include our changes.

8. Add logging to your environment by editing your {dev, prod, staging}/monolog.yaml:

    ```yaml
    monolog:
        channels: ['ing']
        handlers:
            ing:
                type: stream
                path: "%kernel.logs_dir%/%kernel.environment%_ing.log"
                level: debug
                channels: [ 'ing' ]
    ```

9. Clear the cache:

    ```bash
    $ bin/console cache:clear
    ```

10. Install assets:

    ```
    $ bin/console assets:install
    ```

    **Note:** If you are running it on production, add the `-e prod` flag to this command.

11. Synchronize the database:

    ```
    $ bin/console doctrine:migrations:migrate
    ```

### Webpack configuration

#### Installing Webpack package

1. Before Webpack installation, please create the `config/packages/webpack_encore.yaml` file with a content of:

    ```yaml
    webpack_encore:
        output_path: '%kernel.project_dir%/public/build/default'
        builds:
            shop: '%kernel.project_dir%/public/build/shop'
            admin: '%kernel.project_dir%/public/build/admin'
            ing_shop: '%kernel.project_dir%/public/build/bitbag/ing/shop'
            ing_admin: '%kernel.project_dir%/public/build/bitbag/ing/admin'
    ```

2. To install Webpack in your application, please run the command below:

    ```bash
    $ composer require "symfony/webpack-encore-bundle"
    ```

3. After installation, please add the line below into `config/bundles.php` file:

    ```php
    return [
        ...
        Symfony\WebpackEncoreBundle\WebpackEncoreBundle::class => ['all' => true],
    ];
    ```

#### Configuring Webpack

By a standard, the `webpack.config.js` file should be available in your repository. If not, please use [the Sylius-Standard one](https://github.com/Sylius/Sylius-Standard/blob/1.11/webpack.config.js).

1. Please setup your `webpack.config.js` file to require the plugin's webpack configuration. To do so, please put the line below somewhere on top of your `webpack.config.js` file:

    ```javascript
    const [bitbagIngShop, bitbagIngAdmin] = require('./vendor/bitbag/sylius-ing-plugin/webpack.config.js');
    ```

2. As next step, please to add the imported consts into final module exports:

    ```javascripts
    module.exports = [shopConfig, adminConfig, bitbagIngShop, bitbagIngAdmin];
    ```

3. Next thing is to add the asset configuration into `config/packages/framework.yaml`:

    ```yaml
    framework:
        assets:
            packages:
                shop:
                    json_manifest_path: '%kernel.project_dir%/public/build/shop/manifest.json'
                admin:
                    json_manifest_path: '%kernel.project_dir%/public/build/admin/manifest.json'
                ing_shop:
                    json_manifest_path: '%kernel.project_dir%/public/build/bitbag/ing/shop/manifest.json'
                ing_admin:
                    json_manifest_path: '%kernel.project_dir%/public/build/bitbag/ing/admin/manifest.json'
    ```

4. Additionally, please add the `"@symfony/webpack-encore": "^1.5.0",` dependency into your `package.json` file.

5. Now you can run the commands:

    ```bash
    $ yarn install
    $ yarn encore dev # or prod, depends on your environment
    ```
