NotificationBundle
===
This bundle provide capability to register and send notifications throw multiple applications.

## Installation


#### Step 1: Download the Bundle


Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require nti/notification-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new NTI\NotificationBundle\NotificationBundle(),
        );
    }
}
```
#### Step 3: Configuration
 Fill the bundle configuration in the `config.yml` file.
 
 ```yaml
 nti_notification:
     user_destination_get_method: getEmail
     user_authentication_roles: [ROLE_USER]
 ```
 
 The bundle use your User class entity as a notification destination so you
 can easily set your security authentication role and unique property get method that
 you want to use as destination identifier.
 
 #### Documentation:
 
 * [How it works](/Resources/docs/HOW_IT_WORKS.md)
 * [API](/Resources/docs/API_NOTIFICATION.md)
    * [Notification](/Resources/docs/API_NOTIFICATION.md)
    * [Destination](/Resources/docs/API_DESTINATION.md)
 * [Commands and cron-jobs](/Resources/docs/COMMANDS_AND_CRON_JOBS.md)