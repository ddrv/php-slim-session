[![Latest Stable Version](https://img.shields.io/packagist/v/ddrv/slim-session.svg?style=flat-square)](https://packagist.org/packages/ddrv/slim-session)
[![Total Downloads](https://img.shields.io/packagist/dt/ddrv/slim-session.svg?style=flat-square)](https://packagist.org/packages/ddrv/slim-session/stats)
[![License](https://img.shields.io/packagist/l/ddrv/slim-session.svg?style=flat-square)](https://github.com/ddrv/slim-session/blob/master/LICENSE)
[![PHP](https://img.shields.io/packagist/php-v/ddrv/slim-session.svg?style=flat-square)](https://php.net)

# ddrv/slim-session

PHP Library for work with sessions.

## install

1. Run in console:
    ```text
    composer require ddrv/slim-session:^1.0
    ```
1. Include autoload file
    ```php
    require_once('vendor/autoload.php');
    ```

## Integration

> For example, session cookie name used as `sess_id`.

1. Init handler (for example, `\Ddrv\Slim\Session\Handler\FileHandler`, but it may be any implementation of `\Ddrv\Slim\Session\Handler` interface).
    ```php
    $handler = new Ddrv\Slim\Session\Handler\FileHandler('/path/to/sessions', 'sess_id');
    ```

1. Init session instance
    ```php
    /** @var Ddrv\Slim\Session\Handler $handler */
    $session = new Ddrv\Slim\Session\Session($handler);
    ```
   
1. Define session ID and start the session
    ```php
    /** @var Ddrv\Slim\Session\Session $session */
    $sessionId = array_key_exists('sess_id', $_COOKIE) ? $_COOKIE['sess_id'] : null; 
    $session->start($sessionId);
    // some logic
    $session->write(); // store data to storage and close session
    ```

1. Add session cookie to response

    ```php
    /** @var Ddrv\Slim\Session\Session $session */
    setcookie('sess_id', $session->id(), time() + 86400, '/', '.example.com', false, true);
    ```

1. When you need to update session ID do

    ```php
    /** @var Ddrv\Slim\Session\Session $session */
    $session->regenerate();
    $newSessionId = $session->id();
    ```

1. When you need to destroy the session do

    ```php
    /** @var Ddrv\Slim\Session\Session $session */
    $session->destroy();
    ```


## Integration in PRS Frameworks

This package contains the `Psr\Http\Server\MiddlewareInterface` (`PSR-15`) implementation. See `Ddrv\Slim\Session\Middleware\SessionMiddleware` class.

## Using

### Use session as key-value storage

```php
/** @var Ddrv\Slim\Session\Session $session */
$session->set('key1', 'value');
$session->set('key2', ['a', 'b', 'c']);
$value = $session->get('key1'); // 'value'
$value = $session->get('key1', 'default'); // 'value'
$value = $session->get('nonexistent-key'); // null
$value = $session->get('nonexistent-key', 'default'); // 'default'
```

### Use a flash messages

```php
/** @var Ddrv\Slim\Session\Session $session */
$session->flash('key1', 'value'); // 'key1' will be stored only for the current and the next request

// Current request
$session->has('key1');          // true
$value = $session->get('key1'); // 'value'

// Next request
$session->has('key1');          // true
$value = $session->get('key1'); // 'value'

// Other request
$session->has('key1');          // false
$value = $session->get('key1'); // null
```

### Use a counters

```php
/** @var Ddrv\Slim\Session\Session $session */
$session->increment('counter_1'); // 1
$session->increment('counter_1'); // 2
$session->increment('counter_1'); // 3
$session->increment('counter_1'); // 4
$session->increment('counter_1'); // 5
$session->decrement('counter_1'); // 4
$session->decrement('counter_1'); // 3
$session->decrement('counter_1'); // 2
$session->counter('counter_1');   // 2
$session->reset('counter_1');     // 0
$session->counter('counter_1');   // 0\
```

## Removing old sessions

Remove old sessions from storage from time to time.

```php
/** @var Ddrv\Slim\Session\Handler $handler */
$handler->garbageCollect(time() - 86400); // Delete sessions not used during the day  
```

## Encryption

You can use encryption for session data. Use `Ddrv\Slim\Session\Handler\EncryptionHandlerDecorator` for it (required `openssl` PHP extension).

```php
/** @var Ddrv\Slim\Session\Handler $handler */
$session = new Ddrv\Slim\Session\Session(new Ddrv\Slim\Session\Handler\EncryptedHandlerDecorator($handler, 'secret-key'));
``` 
