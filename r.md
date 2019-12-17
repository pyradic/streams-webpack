# Pyro Webpack

This package provides the tools and logic to build a frontend application for the PyroCMS admin control panel.

This package does **not** provide a frontend application.
This package does **not** change anything PyroCMS is doing

It provides a way to properly create a modular admin control panel frontend application.

### Overview
- You create modular javascript code. Just like you do with your addons PHP code.
- Works with yarn package manager. And it does so in a similar way as composer.
- Utilizes the (great) things webpack provides
  - Tree shaking
  - Dynamic imports (async loading)
  - HOT reloading
- The backend (PHP/PyroCMS) manages on which pages/routes your code should be included **or** included and executed


### Introduction
Lets pretend you have a module **pyro/foo**


> ------
**1:** We add some build information to our package.json saying that our code resides in the `lib` directory and what to use as `entrypoint`. Optionally specifying a name of a class that is exported by our lib.

*addons/shared/**pyro/foo**/package.json*
```json
{
    "name": "@pyro/foo",
    "pyro": {
        "srcPath": "lib",
        "entrypoints": [
            {"path": "index.ts", "provider": "FooModuleServiceProvider"}
        ]
    }
}
```


> ------
**2:** We create the `lib` dir and put a `index.js/ts` in it with the class.

*addons/shared/**pyro/foo**/lib/index.ts*
```typescript
class FooModuleServiceProvider {
    register(){}
    boot(){}
}
export {FooModuleServiceProvider}
```


> ------
**3:** To actually include this in the view, we'd have to enable the entrypoint. This can be done from anywhere at any time.

*addons/shared/**pyro/foo**/src/FooModuleServiceProvider*
```php
class FooModuleServiceProvider extends \Anomaly\Streams\Platform\Addon\AddonServiceProvider {
    public function register(){
        $this->app->webpack->enableEntry('@pyro/foo');
    }
}
```

- Shares some architecture concepts with the backend which provides consistency and clarity
  - The `Application`. A IoC container and handles booting the system
  - The `ServiceProvider`. For bootstrapping the services
  - 

asdfsdf
sdf

- root
    - [package.json](#packagejson)
    - composer.json
    - [webpack.config.js](#webpackconfigjs)
    - addons
    - core
        - anomaly
            - [navigation-module](#navigation-module)
                - lib
                    - [NavigationModuleServiceProvider.js](#navigation-modulelibnavigationmoduleserviceproviderjs)
                    - [index.scss](#navigation-modulelibindexscss)
                    - [index.js](#navigation-modulelibindexjs)
                - resources
                    - js
                      - admin.js
                    - css
                      - admin.css
                    - src
                      - NavigationModuleServiceProvider.php
                    - [package.json](#navigation-modulepackagejson) = @anomaly/navigation-module
        - pyrocms
            - accelerant-theme
                - lib
                    - scss
                        - _variables.scss
                    - index.js
                - resources
                    - js
                    - css
                - src
                - package.json = @pyrocms/accelerant-theme
    - vendor
        - anomaly/streams-platform
            - lib
                - Application.js
                - ServiceProvider.js
                - index.js
            - resources
                - js
                - css
            - src
            - package.json = @anomaly/streams-platform

#### root
###### package.json
```json
{
    "private": true,
    "workspaces": {
        "packages": [
            "addons/shared/*/*",
            "core/*/*"
        ]
    },
    "dependencies": {
        "webpack": "*"
    }
}  
```

###### webpack.config.js
```js
// a lot of code
```

####  `navigation-module`

###### navigation-module/package.json
```json
{
    "name": "@anomaly/navigation-module",
    "main": "lib/index.js",
    "dependencies": {
        "@anomaly/stream-platform": "^1.0.0"
    }
}
```

###### navigation-module/lib/NavigationModuleServiceProvider.js
```js
import {ServiceProvider} from '@anomaly/streams-platform'    
import {Menu} from './Menu'
    
export class NavigationModuleServiceProvider extends ServiceProvider {
    register(){
        this.singleton('anomaly.module.navigation::menu', Menu)
    }
    
    boot(){
        
    }
}
```

###### navigation-module/lib/index.scss
```scss
@import "@pyrocms/accelerant-theme/lib/scss/_variables.scss";

$navigation-text-color: $color-grey;
```

###### navigation-module/lib/index.js
```js
import './index.scss'
import {NavigationModuleServiceProvider} from './NavigationModuleServiceProvider'
export {NavigationModuleServiceProvider}
```


###### navigation-module/src/NavigationModuleServiceProvider.php
```php
class NavigationModuleServiceProvider extends \Anomaly\Streams\Platform\Addon\AddonServiceProvider {
    protected $jsProviders = [
        'PYROCMS_EXPORTS.anomaly.navigation_module.NavigationModuleServiceProvider'
    ];
    
    public function boot(\Anomaly\Streams\Platform\Asset\Asset $assets, \Anomaly\Streams\Platform\View\ViewTemplate $template){
        $assets->add('admin.js', 'anomaly.module.navigation::js/admin.js');
        $assets->add('admin.css', 'anomaly.module.navigation::js/admin.css');
        $template->set('js_providers', array_merge($template->get('js_providers',[]), $this->jsProviders));
    }
}
```

#### `stream-platform`
###### streams-platform/package.json
```json
{
    "name": "@anomaly/stream-platform",
    "main": "lib/index.js",
    "dependencies": {
        "inversify": "^5.0.0"
    }
}
```
###### streams-platform/lib/Application.js
```js
import {Container} from 'inversify'

export class Application extends Container {
    providers = [];
    loadedProviders = {}
    bootstrapped=false
    booted=false
    async bootstrap(ProviderClasses){
        this.providers=ProviderClasses;
        for(const ProviderClass of ProviderClasses){
            await this.register(ProviderClass)
        }
        this.bootstrapped=true
        return this;
    }
    async register(ProviderClass){
        if(ProviderClass.__registered){
            return;
        }
        ProviderClass.__registered = true;
        const provider = new ProviderClass(this);
        if('register' in provider){
            await provider.register()
        }
        this.loadedProviders[ProviderClass] = provider;
        if(this.booted){
            this.bootProvider(ProviderClass)
        }
        return this;
    }
    async bootProvider(ProviderClass){
        if(ProviderClass.__booted){
            return
        }
        ProviderClass.__booted = true;
        const provider = this.loadedProviders[ProviderClass];
        if('boot' in provider){
            await provider.boot();
        }
    }
    async boot(){
        for(const ProviderClass in this.loadedProviders){
            await this.bootProvider(ProviderClass)
        }
        return this;
    }
    
    async start(){
        // an example for vue
        const root = new Vue({
            //...
        })
        root.$mount('#root')
        return this;        
    }
}  
```

###### streams-platform/lib/ServiceProvider.js
```js
export class ServiceProvider {}  
```

#### `accelerant-theme`
###### accelerant-theme/resources/views/partials/assets.twig
```html
{% asset_script('admin.js') %}
{% asset_style('admin.css') %}
<script>
    app
        .bootstrap([
            {% for provider in template.js_providers %}
            '{{ provider }}',
            {% endfor %}
        ])
        .then(app.boot())
        .then(app.start())    
</script>
```
