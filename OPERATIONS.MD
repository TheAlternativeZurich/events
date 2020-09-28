# Operations
useful information for operations.

## Backup

you only need to backup the files in the `/var/persistent` folder.
Using rsync, do something like this: 
```
rsync -azP ~/www/events.thealternative.ch/production/shared/var/persistent/ user@IP:~/backup/
```

## Impersonation

you may impersonate another user if you an admin (must be set directly in the database) with 
```
http://example.com/somewhere?_switch_user=git@mangel.io
```

finish impersonation with
```
http://example.com/somewhere?_switch_user=_exit
```

