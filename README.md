# Luza Group — Magento 2 Technical Assessment

Technical assessment built on Magento Open Source 2.4.6, running in a Docker-based development environment.

## Stack

- Magento Open Source 2.4.6
- PHP 8.2 · Apache
- MariaDB 10.3
- Elasticsearch 7
- Redis
- Mailhog
- Docker / Docker Compose

## Getting started

Clone the repository:

```bash
git clone git@github.com:MateusM3/luza-magento2-featured-product.git
cd luza-magento2-featured-product
```

Set up the Marketplace authentication. Composer downloads Magento packages from `repo.magento.com`, which requires Adobe Commerce Marketplace credentials. Create your `auth.json` from the sample:

```bash
cp auth.json.sample auth.json
```

Generate a key pair in the [Adobe Commerce Marketplace](https://commercemarketplace.adobe.com/) (**My Profile → Marketplace -> Access Keys → Create A New Access Key**) and fill in `auth.json` the **public key** is the `username` and the **private key** is the `password`:

```json
{
    "http-basic": {
        "repo.magento.com": {
            "username": "your-public-key",
            "password": "your-private-key"
        }
    }
}
```

`auth.json` is already listed in `.gitignore` keep your private key out of version control.

Install the Composer dependencies:

```bash
composer install
```

If your local Composer/PHP version isn't compatible with Magento 2.4.6 (PHP 8.1 / 8.2), run Composer from a container instead. The `composer:2.7.9` image ships PHP 8.3, so add `--ignore-platform-reqs`:

```bash
docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app composer:2.7.9 install --ignore-platform-reqs
```

Start the containers:

```bash
docker-compose up -d
```

## Installing Magento

Open a shell in the application container:

```bash
docker exec -it luza-assessment-app bash
```

Run the installer:

```bash
php bin/magento setup:install \
  --base-url="http://localhost:8080/" \
  --backend-frontname=admin \
  --db-host=luza-assessment-database \
  --db-name=luza_magento_assessment \
  --db-user=root \
  --db-password=secret \
  --admin-firstname=Luza \
  --admin-lastname=Group \
  --admin-email=assessment@luzagroup.local \
  --admin-user=luza \
  --admin-password='Luza@2026' \
  --language=pt_BR \
  --currency=BRL \
  --timezone=America/Sao_Paulo \
  --use-rewrites=1 \
  --search-engine=elasticsearch7 \
  --elasticsearch-host=luza-assessment-elasticsearch \
  --elasticsearch-port=9200
```

Finish the setup:

```bash
php bin/magento deploy:mode:set developer
php bin/magento indexer:reindex
php bin/magento cache:flush
```

Disable two-factor authentication so you can log in to the admin without an authenticator app. **Development only never disable 2FA in production:**

```bash
php bin/magento module:disable Magento_TwoFactorAuth Magento_AdminAdobeImsTwoFactorAuth
php bin/magento cache:flush
```

## Access

| Service    | URL                             | Credentials          |
| ---------- | ------------------------------- | -------------------- |
| Storefront | <http://localhost:8080>         | —                    |
| Admin      | <http://localhost:8080/admin>   | `luza` / `Luza@2026` |
| Mailhog    | <http://localhost:8025>         | —                    |
| phpMyAdmin | <http://localhost:8302>         | `root` / `secret`    |

Database `luza_magento_assessment` runs on host `luza-assessment-database` (`root` / `secret`).

## Notes

The featured-product module requested in the assessment is developed in the following commits and documented as it progresses.
