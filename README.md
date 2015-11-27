# yii2-rest-api
Yii2 Basic Rest Api Example


CONFIGURATION
-------------

### Database

Edit the file `config/db.php` with real data, for example:

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=rest-api',
    'username' => 'root',
    'password' => 'pwd',
    'charset' => 'utf8',
];
```


*Issue:* Responds to preflight calls from browser with 404.
