# Test with latest PHP

From https://hub.docker.com/r/tommymuehle/docker-alpine-php-nightly/

```bash
docker build -t php-latest .
docker run -it --rm --name my-running-script -v "$PWD":/usr/src/myapp -w /usr/src/myapp php-latest php your-script.php
```


To run the different tests: 

```bash
docker run -it --rm --name my-running-script -v "$PWD":/usr/src/myapp -w /usr/src/myapp php-latest php simple-curl.php
docker run -it --rm --name my-running-script -v "$PWD":/usr/src/myapp -w /usr/src/myapp php-latest php test3744.php
docker run -it --rm --name my-running-script -v "$PWD":/usr/src/myapp -w /usr/src/myapp php-latest php plain-push.php
```

## Current issue

There is a problem with Buzz and/or PHP. To reproduce: 

1. Clone the repo
1. `composer install`
1. Uncomment 178 in Buzz\Client\MultiCurl. (The row that sets the `CURLMOPT_PUSHFUNCTION`)
1. `docker build -t php-latest .`
1. docker run -it --rm --name my-running-script -v "$PWD":/usr/src/myapp -w /usr/src/myapp php-latest php buzz.php

Expected output (similar to): 

```
bool(true)
First: 0.9092459678649
Other: 0.0082689857483
```

I suspect this error be the same as https://bugs.php.net/bug.php?id=77535. But Im not sure. 