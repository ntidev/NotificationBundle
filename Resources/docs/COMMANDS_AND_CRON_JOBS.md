Commands and cron-jobs
===

#### Commands:
The bundle contains two important commands.

1. `nti:notifications:update` : this command read and update the notification status given its
schedule date or expiration date.

2. `nti:notifications:sync`: this command send a post or put request to the notification destination
application.

#### Cron-jobs:
You may want to run the above commands automatically. If you are running your symfony application 
on a unix base operative system you can accomplish this very easily.

Edit the `/etc/crontab` file using your favorite text editor.
```bash
# Example of job definition:
# .---------------- minute (0 - 59)
# |  .------------- hour (0 - 23)
# |  |  .---------- day of month (1 - 31)
# |  |  |  .------- month (1 - 12) OR jan,feb,mar,apr ...
# |  |  |  |  .---- day of week (0 - 6) (Sunday=0 or 7) OR sun,mon,tue,wed,thu,fri,sat
# |  |  |  |  |
# *  *  *  *  * user-name  command to be executed

  * * * * * user php /path/to/your/console nti:notifications:update --env=prod
  * * * * * user php /path/to/your/console nti:notifications:sync --env=prod
```