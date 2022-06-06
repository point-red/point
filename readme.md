<p align="center"><img src="https://point-red.github.io/point/_media/logo.svg"></p>

<p align="center">
<a href="https://packagist.org/packages/point-red/point"><img src="https://poser.pugx.org/point-red/point/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/point-red/point"><img src="https://poser.pugx.org/point-red/point/v/unstable.svg" alt="Unstable Version"></a>
<a class="badge-align" href="https://www.codacy.com/app/martiendt/point?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=point-red/point&amp;utm_campaign=Badge_Grade"><img src="https://api.codacy.com/project/badge/Grade/0beb7ac9c0f04d7484b7159e45ae3414"/></a>
<a href="https://styleci.io/repos/108611909"><img src="https://styleci.io/repos/108611909/shield?branch=master" alt="StyleCI"></a>
</p>

> This package still in active development and not ready for production yet.

## Using Docker

Install Docker Desktop https://www.docker.com/products/docker-desktop/
## Quick Start

1. Clone Project and run Docker
```bash
# clone project or download from github https://github.com/point-red/point/archive/refs/heads/alpha1.zip
git clone git@github.com:point-red/point.git
# go to your project directory
cd point
# copy `.env.example` to `.env`
cp .env.example .env
# run docker
docker compose up
```

2. Open point_app cli from Docker Desktop and run this command
```bash
# run `composer install`
composer install
# run `php artisan key:generate`
php artisan key:generate
# generate database for new development
php artisan dev:new
# seed database
php artisan tenant:seed:first point_dev
```

## Testing

```
php artisan test
```

## Security Vulnerabilities

If you discover a security vulnerability within Point, please send an e-mail to martien@point.red. All security vulnerabilities will be promptly addressed.
