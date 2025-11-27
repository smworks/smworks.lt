# smworks.lt
Personal website

## Configuration

The application expects a `core/config.php` file that defines database and
authentication credentials. The file is not committed to version control because
it contains secrets, so you need to create it manually before running the site.

1. Copy the template below into `core/config.php`.
2. Replace the placeholder values with credentials for your environment.
3. Keep the file private (e.g. never commit it, store it securely).

```
<?php

if (!defined('PATH')) exit('Direct access to script is not allowed.');

class Config
{
    const WEB_DB_SERVER = 'localhost';      // Database host
    const WEB_DB_DATABASE = 'database';     // Database name
    const WEB_DB_USERNAME = 'username';     // Database user
    const WEB_DB_PASSWORD = 'password';     // Database password
    const REALM = 'smworks';                // Auth realm identifier
    const USERNAME = 'admin';               // Auth username
    const PASSWORD = 'change_me';           // Auth password
}
```

Make sure the constants match whatever credentials your local or production
environment uses. The `Config` class is loaded by `core/ini.php` during startup,
so the site will fail to boot if the file is missing or misconfigured.