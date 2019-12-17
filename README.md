# Pyro Webpack

This package provides the tools and logic to build a modular frontend application for the PyroCMS admin control panel.

This package does **not** provide a frontend application.
This package does **not** change anything PyroCMS is doing



### Features
- Create modular javascript for addons (which are already modular).
- Addons can enable javascript module(s) at any given point.
- Uses `yarn workspaces` (provides `yarn` with somewhat similar functionality as the `composer-merge-plugin` does with `composer`)
- Utilizes the (great) things webpack provides
  - Tree shaking
  - Dynamic imports (async loading)
  - HOT reloading


### Basic Example

1. Lets pretend to have a module *addons/shared/**pyro/core-module*** that contains

    - *lib/index.js*
        ```typescript
        export class Application {
            static getInstance(){return new Application()}
            register(provider){}
            boot(){}
            bind(){}
            make(){}
        }
        export class ServiceProvider {
            app = Application.getInstance()
        }
        export class CoreModuleServiceProvider extends ServiceProvider{
            register(){}
        }
        ```
    - *lib/__variables.scss*
        ```scss
        @use "~bootstrap/scss/variables" with (
            $text-color: blue;
        )
        $core-variable-one: #333 !default;
        $core-variable-two: darken($core-variable-one, 5) !default;
        ```

    - *src/CoreModuleServiceProvider.php*
        ```php
        class CoreModuleServiceProvider extends \Anomaly\Streams\Platform\Addon\AddonServiceProvider {
            public function boot(Webpack $webpack){
                $webpack->enableEntry('@pyro/core-module');
            }
        }
        ```


2. Lets pretend to have another module *addons/shared/**pyro/foo-module*** that contains

    - *lib/index.js*
        ```typescript
        import './style.scss';
        import {ServiceProvider} from '@pyro/core-module'
        export function logWithPrefix(string){
            console.log('prefix', string)
        }
        export class FooService {
            hello(){
                logWithPrefix('FooService hello')
            }
        }
        export class FooModuleServiceProvider extends ServiceProvider{
            register(){
                logWithPrefix('registered FooModuleServiceProvider')
                this.app.bind('foo.service', FooService);
            }
        }
        ```

    - *lib/style.scss*-
        ```scss
        @import "@pyro/core-module/lib/variables";
        .foo-module {
            &__item {
                color: $core-variable-one;
                &--disabled {
                    color: $core-variable-two;
                }
            }
        }
        ```

    - *src/FooModuleServiceProvider.php*
        ```php
        class FooModuleServiceProvider extends \Anomaly\Streams\Platform\Addon\AddonServiceProvider {
            public function boot(Webpack $webpack){
                $webpack->enableEntry('@pyro/foo-module'); // can obviously be called anywhere, like controllers actions, route-groups etc
            }
        }
        ```

3. Lets pretend to have another *module addons/shared/**pyro/bar-module*** that contains

    - *lib/index.js*
        ```typescript
        import {ServiceProvider} from '@pyro/core-module'
        import {FooService} from '@pyro/foo-module'; // can import from other modules

        export class BarModuleServiceProvider extends ServiceProvider{
            register(){
                logWithPrefix('registered BarModuleServiceProvider')
            }
            boot(){
                const fooService:FooService = this.app.make('foo.service');
                fooService.hello();
            }
            async loadStyle(){
                return import('./style.scss')
            }
        }
        ```

    - *lib/style.scss*
        ```scss
        $core-variable-one: #999;
        @import "@pyro/core-module/lib/variables";
        .bar-module {
            &__item {
                color: $core-variable-one;
                &--disabled {
                    color: $core-variable-two;
                }
            }
        }
        ```

    - *src/BarModuleServiceProvider.php*
        ```php
        class BarModuleServiceProvider extends \Anomaly\Streams\Platform\Addon\AddonServiceProvider {
            public function boot(Webpack $webpack){
                $webpack->enableEntry('@pyro/bar-module');
            }
        }
        ```

4. Which we can combine and bootstrap in a view

    Like *addons/shared/**pyro/core-module**/resources/view/frontend__application.twig*
    And pretend we called `ViewIncludes->include('cp_scripts', 'pyro.module.core::frontend__application')` in  `CoreModuleServiceProvider::boot`

    ```html
    {% import "webpack::include_webpack" %}

    <script>
        // The namespace is set in webpack.php config (or .env WEBPACK_NAMESPACE)
        var exported = window['$NAMESPACE$'];
        exported.providers; // array of ServiceProvider classes of enabled entries.

        var app = exported.pyro__core_module.Application.getInstance()
        // or
        var app = exported['@pyro/core-module'].Application.getInstance()

        exported.providers.forEach(function(ProviderClass){
            app.register(ProviderClass);
        })

        app.boot().then(function(){
            var fooService = app.make('foo.service');
            fooService.hello()
            // app.$mount() ???
            // app.render() ???
            // implement it yourself
        })

    </script>

    ```



5Run `yarn serve`, `yarn build:dev` or `yarn build:prod`

    **`yarn serve`** Will start a webpack-dev-server. `Pyro\Webpack` is aware of this will render the webpack-dev-server asset paths.
    This will enable HOT Reloading on the scss files we defined. (Vue or React can easily be added with HMR aswell, and is explained further onwards)

    **`yarn build:prod`** Will create production assets and place them by default in the `public/assets` (configurable). Again `Pyro\Webpack` is aware of this and will render the production asset paths.




### Installation

1. composer.json
    ```json
     "pyro/webpack": "^1.0"
    ```

2. app.php
    ```php
    Pyro\Webpack\WebpackServiceProvider::class;
    ```

3. .env
    ```bash
    WEBPACK_ENABLED=true
    WEBPACK_NAMESPACE=pyro
    WEBPACK_OUTPUT_PATH=public/assets
    WEBPACK_PROTOCOL=http
    WEBPACK_HOST=localhost
    WEBPACK_PORT=8079
    WEBPACK_PATH=storage/webpack.json
    ```

4. package.json
    ```json
    {
        "workspaces": {
            "packages": [
                "addons/shared/*/*",
                "core/*/*",
                "vendor/pyro/webpack"
            ]
        },
        "scripts": {
            "build:dev": "NODE_ENV=development webpack --config webpack.config.js",
            "build:prod": "rm -rf public/assets && NODE_ENV=production webpack --production --config webpack.config.js",
            "serve": "NODE_ENV=development webpack-dev-server --inline --hot --config webpack.config.js"
        },
        "dependencies": {
            "@pyro/webpack": "^1.0.0",
            "@radic/webpacker": "^7.2.0",
            "webpack": "^4.40.2",
            "webpack-cli": "^3.3.8",
            "webpack-dev-server": "^3.8.0"
        }
    }
    ```


5. webpack.config.js
    ```typescript
    import { PyroBuilder } from '@pyro/webpack';

    const builder = new PyroBuilder({
        globs    : [
            'addons/shared/*/*',
            'addons/*/*',
            'core/*/*'
        ],
        rootPath : __dirname
    })
    const { wp, env, addons } = builder.init();

    // any custom logic

    const config = wp.toConfig();

    export default config;
    ```

6.

7. Create a view like shown at [Basic Example > **4:** Create a view to..](#)



#### Add a addon to the builder
In order to get a addon included in the builder, a few additional steps are required.

1. Ensure the addon has a `package.json` (next to the composer.json file)
2. It's recommended to set the `name` field in package.json the same as in composer.json, but prefixed with an `@`.
3. Ensure package.json has a the `version` field, preferably `1.0.0`. This version should not be changed.
4. Add the `pyro` configuration. Check the example below

Example of a valid package.json:
```json
{
    "name": "@pyro/foo-module",
    "version": "1.0.0",
    "scripts": {},
    "main": "lib/index.js",
    "typings": "lib/index.js",
    "types": "lib/index.js",
    "pyro": {
        "srcPath": "lib",
        "entrypoints": [
            {"path": "index.js", "provider": "FooModuleServiceProvider"}
        ]
    },
    "dependencies": {
        "@pyro/core-module": "^1.0.0",
        "bootstrap": "^4.3.1",
        "element-ui": "^2.12.0",
        "font-awesome": "^4.7.0",
        "vue-clickaway": "^2.2.2"
    },
    "devDependencies": {}
}

```


### PHP `Pyro\Webpack`

##### `Pyro\Webpack\Webpack`
###### `enableEntry($name, $suffix = null)`


### JS `@pyro/webpack`
- [`webpack-chain`](https://github.com/neutrinojs/webpack-chain/) Provides the chaining webpack configurator API  to generate and simplify the modification of webpack
- [`@radic/webpacker`](#) Extends the chaining API with various useful features. Additionally it also provides a whole range of *pre-configured, but reconfigurable presets*. This aims to make your webpack config small and to the point.
- [`@pyro/webpack`](#) Provides `PyroBuilder` to setup webpack. It locates all PyroCMS javascript addon modules and adds them correctly to the webpack configuration.

```typescript
import { PyroBuilder } from '@pyro/webpack';

const builder = new PyroBuilder({
    globs    : [
        'addons/shared/*/*',
        'addons/*/*',
        'core/*/*'
    ],
    rootPath : __dirname
})

// init()
// 1: will find and register all found addons
// 2: setup webpack configuration, including addons
// 3: returns the Webpacker (webpack-chain) and addon instances array
const { wp, env, addons } = builder.init();

// At this point you can use 'wp' (Webpacker/webpack-chain) to add or alter any configuration made

// change the output dir?
wp.output.path('public/some-other-dir');

// add postcss loader before sass loader in the sass loader use array?
wp.module.rule('scss')
    .use('postcss-loader')
    .loader('postcss-loader')
    .before('sass-loader')
    .options({
        preserve  : true,
        whitespace: false
    })

// modify module rule options?
wp.module.rule('babel').use('babel-loader').tap(options => {
    options.plugins.push(['someplugin'])
    return options
})

// Webpacker also comes with some extra features.
// Example: to use @import "~accelerant-theme/.." in SCSS instead of relative paths.
wp.ensureLink(path('core/pyrocms/accelerant-theme'), path('node_modules/accelerant-theme'));

// At the end of the script transform into a webpack configuration object and export it
const config = wp.toConfig();

export default config;
```
