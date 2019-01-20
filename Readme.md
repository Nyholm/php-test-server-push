# Test with latest PHP

From https://hub.docker.com/r/tommymuehle/docker-alpine-php-nightly/

```bash
docker build -t php-latest .
docker run -it --rm --name my-running-script -v "$PWD":/usr/src/myapp -w /usr/src/myapp php-latest php your-script.php
```
