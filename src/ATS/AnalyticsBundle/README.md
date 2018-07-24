### General:
---
- Single Analytics events tracker

### Usage:
---
1- Create a new Tracking Event (Must inherit from the ***BaseTrackingEvent*** class)
2- Create a new Handler Service (Must implement ***EventHandlerInterface***)
3- Register the event listener as you would normally do. example:

```yml
app.listener.my_awesome_listener:
        class: TrackingBundle\Listener\TrackingEventListener
        arguments: ["@ID_OF_THE_HANDLER_SERVICE@"]
        tags:
            - { name: kernel.event_listener, event: "@EVENT_NAME@", method: "onTrackingEventTriggered" }
```
