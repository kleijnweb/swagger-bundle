# Caching

# Caching Specs

Parsing YAML and resolving JSON Pointers can be slow, especially with larger specs with external references. SwaggerBundle can use a Doctrine cache to mitigate this. Use a DI key to reference the service you want to use:

```yml
swagger:
  document: 
    cache: "some.doctrine.cache.service"
```

## HTTP Caching

SwaggerBundle works well with [E-Tags Based on REST Semantics](https://github.com/kleijnweb/rest-e-tag-bundle).

