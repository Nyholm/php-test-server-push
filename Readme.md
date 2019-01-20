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