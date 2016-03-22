# Responses
 
When a controller action returns `NULL`, SwaggerBundle will return an empty `204` response, provided that one is defined in the specification.
Otherwise, it will default to the first 2xx type response defined in your spec, or if all else fails, simply 200.

You cannot return Symfony responses from your controllers. Any response manipulation (including custom status codes) you want needs to be implemented using "Response Listeners". Example that sets some headers:

```yaml
services:
  your_response_listener:
    class: Some\Namespace\ResponseListener
    tags:
      - { name: kernel.event_listener, event: kernel.response, method: onKernelResponse }
```
```php
class ResponseListener
{
    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $request = $event->getRequest();
        $headers = $event->getResponse()->headers;
        switch ($request->attributes->get('_swagger_path')) {
            case '/user/login':
                $headers->set('X-Rate-Limit', 123456789);
                $headers->set('X-Expires-After', date('Y-m-d\TH:i:s\Z'));
                break;
            default:
                //noop
        }
    }
}

```