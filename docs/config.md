## Install And Configure

Install using composer (`composer require kleijnweb/swagger-bundle`). You want to check out the [release page](https://github.com/kleijnweb/swagger-bundle/releases) to ensure you are getting what you want and optionally verify your download.

Add Swagger-based routing to your app, for example:
 
```yml
test:
  resource: "config/yourapp.yml"
  type: swagger
```

The path here is relative to the `swagger.document.base_path` configuration option. The above example would require something like this in your config:

```yml
swagger:
  document: 
    base_path: "%kernel.root_dir%"
```