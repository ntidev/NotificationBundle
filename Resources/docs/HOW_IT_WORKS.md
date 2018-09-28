How It Works
===
The communication between application is established using rest methods and token authentication by application.
In order to accomplish this the bundle must be installed in the destination (to) application and grant read or write access.

Depending on the application origin the notifications can be:

1. Internal Notifications.

    This notifications are created by the default application. In other words is a notification created
    from the same destination application. 

    ![Internal Notifications]( /Resources/docs/assets/internal_notification.png?raw=true "internal notification")

2. Internal to External Notification.

    In this scenario the default application wants to create or modify a notification in other application.
    
    ![Internal To External Notifications]( /Resources/docs/assets/internal_external.png?raw=true "internal to external notification")
    
3. External to Internal Notification.

    In this scenario the default application receives create or modify notifications request from other applications.
    
    ![Internal To External Notifications]( /Resources/docs/assets/external_internal.png?raw=true "external to internal notification")
    
#### Next step.

Now that you understand how the bundle works it is time to you go to the api section and see how
to send requests.
