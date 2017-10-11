# Laravel Reserve / Release Queue Example

Run redis server using the following command-

```
redis-server
```

Start nodejs server using-

```
node server.js
```

Start laravel server 

```
php artisan serve
```

# Usage Example

```
[host]:[port]/reserve/{customer_id}
[host]:[port]/release/{customer_id}

[host]:[port]/show
```