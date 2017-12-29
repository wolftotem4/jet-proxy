# Jet Proxy

An PHP-based proxy, you can use it for web debugging purposes.

## Installation

```
git clone https://github.com/wolftotem4/jet-proxy.git
cd jet-proxy
composer install
```

## Usage

### Domain-Routing Forwarding

1. Open and edit `index.php`.

```php
$forwarding = [
    // Edit the forwarding list below.
    'localhost:8080' => ['host' => 'localhost', 'ip' => '127.0.0.1'],
  
    // You can disguise as an actual website.  (Please don't do anything evil.)
    // Don't forget you need to set up `/etc/hosts` (Linux)
    // or '%systemroot%\System32\drivers\etc\hosts' (Windows).
    'example.com' => ['host' => 'example.com', 'ip' => '123.123.123.123'],
];
```

2. Run in Apache or PHP's built-in development server.

```
php -S localhost:8080 index.php
```

### Setting host on command line

```
# php cli-server [host binding] [target host] [target ip address]
php cli-server.php localhost:8080 example.com 123.123.123.123
```