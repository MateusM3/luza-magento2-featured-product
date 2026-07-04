# Luza Group — Magento 2 Technical Assessment

Technical assessment built on Magento Open Source 2.4.6, running in a Docker-based development environment.

It ships a custom module — **`Luza_FeaturedProduct`** — that renders a full-width featured-product box at the top of the homepage (Luma theme) with **real-time stock updates** (no full page reload). See [Featured Product module](#featured-product-module).

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

Generate a key pair in the [Adobe Commerce Marketplace](https://commercemarketplace.adobe.com/) (**My Profile → Marketplace → Access Keys → Create A New Access Key**) and fill in `auth.json` — the **public key** is the `username` and the **private key** is the `password`:

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

`auth.json` is already listed in `.gitignore` — keep your private key out of version control.

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
docker compose up -d
```

> **Note:** use `docker compose` (Compose V2, the current plugin), not the legacy `docker-compose` binary.

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

`setup:install` generates **`app/etc/env.php`** (encryption key, DB credentials, cache config). This file is git-ignored because it holds the crypt key. A reference copy is provided at **`app/etc/env.php.example`**, documenting the expected shape (DB host/name/user already match the Docker defaults). To reuse it instead of reinstalling, copy it, replace the `CHANGE_ME_*` placeholders (the crypt key **must** be unique), then run `bin/magento setup:upgrade`.

Finish the setup:

```bash
php bin/magento deploy:mode:set developer
php bin/magento indexer:reindex
php bin/magento cache:flush
```

Disable two-factor authentication so you can log in to the admin without an authenticator app. **Development only — never disable 2FA in production:**

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

---

## Featured Product module

`Luza_FeaturedProduct` displays a single featured product as the first element of the homepage main content, occupying the full `content main` width. The box shows the **title, price, base image and salable quantity**, links to the product page when clicked, and refreshes the **available stock in real time** (periodic AJAX, no full reload).

### What it does

- Renders a featured-product card at the top of the homepage (Luma theme), full width.
- Shows title, final price, base image and salable stock; the whole card links to the product page.
- Refreshes only the stock number periodically, without reloading the page.
- Fully configurable from the admin panel; **no theme files are edited** — everything lives in the module.

### Structure

```text
app/code/Luza/FeaturedProduct/
├── Api/                                  # Service contracts (interfaces)
│   ├── FeaturedProductResolverInterface.php   # resolves the configured product
│   └── FeaturedProductStockInterface.php      # salable qty (MSI)
├── Block/
│   └── FeaturedProduct.php               # merges dynamic data into jsLayout
├── Controller/
│   └── Stock/Index.php                   # thin JSON endpoint for the AJAX poll
├── Model/
│   ├── Config.php                        # typed, scope-aware config reader
│   ├── Config/Backend/ProductSku.php     # validates the SKU on save
│   ├── Config/Source/SelectionType.php   # SKU | Product options
│   ├── Config/Source/ProductList.php     # product dropdown
│   ├── FeaturedProductResolver.php       # resolver implementation
│   └── FeaturedProductStock.php          # MSI salable-qty implementation
├── Observer/
│   └── SyncFeaturedSku.php               # keeps config in sync on SKU rename
├── ViewModel/
│   └── FeaturedProductData.php           # presentation data for the template
├── etc/
│   ├── acl.xml                           # admin ACL resource
│   ├── di.xml                            # interface → implementation bindings
│   ├── events.xml                        # catalog_product_save_after observer
│   ├── module.xml
│   ├── frontend/routes.xml               # frontName "featuredproduct"
│   └── adminhtml/
│       ├── menu.xml                      # admin menu shortcut
│       └── system.xml                    # Stores → Configuration fields
├── i18n/pt_BR.csv                        # translations
└── view/frontend/
    ├── layout/cms_index_index.xml        # reference block + jsLayout argument
    ├── templates/featured_product.phtml  # card markup + x-magento-init
    └── web/
        ├── css/source/_module.less       # styling (auto-collected by the theme)
        ├── js/view/stock.js              # Knockout uiComponent (polling)
        └── template/stock.html           # Knockout template for the stock line
```

### Architecture & applied patterns

- **Service contracts (interfaces + DI preferences).** Business logic sits behind `Api/FeaturedProductResolverInterface` and `Api/FeaturedProductStockInterface`, bound to their implementations in `etc/di.xml`. This keeps consumers decoupled and lets the **ViewModel** (storefront render) and the **AJAX controller** reuse the exact same logic with zero duplication.
- **ViewModel over block/template logic.** `ViewModel/FeaturedProductData` (implements `ArgumentInterface`) exposes everything the template needs (name, price, image, stock, url). The `.phtml` stays logic-free.
- **Thin controller.** `Controller/Stock/Index` only resolves the product and returns `{ "qty": N }` as JSON — all logic is delegated to the services.
- **Multi-Source Inventory (MSI).** Stock uses `GetProductSalableQtyInterface` (salable quantity), **not** the deprecated `CatalogInventory\StockRegistry`.
- **Observer to prevent broken references.** `Observer/SyncFeaturedSku` listens to `catalog_product_save_after`: if the product configured by SKU is renamed, the stored SKU is updated automatically, so the featured box never points to a missing SKU. It runs on `save_after` (not `before`) so the config is only updated once the product save actually commits.
- **Backend-model validation.** `Model/Config/Backend/ProductSku` validates the SKU when the config is saved (see below).
- **Real-time stock with jsLayout + Knockout.** The stock line is a Knockout `uiComponent` initialised through `jsLayout` (block argument) + `Magento_Ui/js/core/app`, following the core minicart pattern.
- **No theme edits.** Styles live in `web/css/source/_module.less` (auto-collected by the Luma/blank theme) and all markup is in the module.

### How the featured product is selected (and validated)

The product shown is driven entirely by configuration. There are two strategies (**Selection Type**):

- **By SKU** — the resolver loads the product via `ProductRepository::get($sku)`.
  The **Product SKU** field is guarded by a backend model that runs on save:
  - empty value → *"The SKU field is empty."*
  - no product with that SKU → *"No product found with SKU '…'. Please enter a valid SKU."*

  This blocks the admin from saving a featured SKU that doesn't exist. Additionally, the [SyncFeaturedSku observer](#architecture--applied-patterns) keeps this value correct if the product's SKU is later renamed.
- **By Product** — the resolver loads the product via `ProductRepository::getById($id)`, chosen from a dropdown.

In all cases the resolver **fails soft**: if the feature is disabled, nothing is configured, or the product no longer exists, it returns `null` and the box is simply not rendered (a warning is logged) — the storefront never breaks.

### Admin configuration

**Stores → Configuration → General → Featured Product**
(there is also a shortcut under **Content → Featured Product** in the admin menu).

#### General Settings

| Field | Type | Description |
| ----- | ---- | ----------- |
| **Enabled** | Yes/No | Master switch. When off, the box is not rendered. |
| **Selection Type** | SKU / Product | How the featured product is chosen. Shown when *Enabled*. |
| **Product SKU** | Text | The SKU to feature. Validated on save (must exist). Shown when *Selection Type = SKU*. |
| **Product** | Dropdown | Pick the product from a list. Shown when *Selection Type = Product*. |

#### Real-Time Stock

| Field | Type | Description |
| ----- | ---- | ----------- |
| **Enable Real-Time Update** | Yes/No | Turns the periodic stock refresh on/off. When off, the stock is shown once (server-rendered value). |
| **Update Interval (seconds)** | Text (digits > 0) | How often the stock is refreshed. Default `15`. Shown when the update is enabled. |

All fields are scope-aware (default / website / store view).

### Real-time stock — how it works

```text
Homepage render
  → Block.getJsLayout() injects { qty, updateUrl, interval, enabled }
  → Knockout uiComponent (stock.js) starts an observable `qty` + polling
  → every N seconds: GET /featuredproduct/stock
        → thin controller → resolver + MSI stock service → { "qty": N }
  → the observable updates → Knockout re-renders ONLY the stock number
```

No full page reload; only the stock value changes.
