# Fakturoid PHP lib

PHP library for [Fakturoid.cz](https://www.fakturoid.cz/). Please see [API](https://www.fakturoid.cz/api/v3) for more documentation.
New account just for testing API and using separate user (created via "Nastavení > Uživatelé a oprávnění") for production usage is highly recommended.

![Tests](https://github.com/fakturoid/fakturoid-php/actions/workflows/tests.yml/badge.svg)

## Installation
The recommended way to install is through Composer:

```
composer require fakturoid/fakturoid-php
```

Library requires PHP 8.1 (or later) and `ext-curl` and `ext-json` extensions.

## Authorization by OAuth 2.0 - Client Credentials Flow

```php
$fManager = new \Fakturoid\FakturoidManager(
    new ClientInterface(), // PSR-18 client
    '{fakturoid-account-slug}',
    '{fakturoid-client-id}',
    '{fakturoid-client-secret}',
    'PHPlib <your@email.cz>'
);
$fManager->authClientCredentials();
```


## Authorization by OAuth 2.0 - Authorization Code Flow

Authorization using OAuth takes place in several steps. We use data obtained from the developer portal as client ID and client secret (_Settings → Connect other apps → OAuth 2 for app developers_).

First, we offer the user a URL address where he enters his login information. We obtain this using the following method:
```php
$fManager = new \Fakturoid\FakturoidManager(
    new ClientInterface(), // PSR-18 client
    '{fakturoid-account-slug}',
    '{fakturoid-client-id}',
    '{fakturoid-client-secret}',
    'PHPlib <your@email.cz>'
);
echo '<a href="'.$fManager->getAuthenticationUrl().'">Link</a>';
```
After entering the login data, the user is redirected to the specified redirect URI and with the code with which we obtain his credentials. We process the code as follows:
```php
$fManager->requestCredentials($_GET['code']);
```
Credentials are now established in the object instance and we can send queries to the Fakturoid api. Credentials can be obtained in 2 ways. Obtaining credentials directly from the object:
```php
$credentials = $fManager->getCredentials();
echo $credentials->toJson();
```

### Processing credentials using the credentials callback:
The way callback works is that the library calls the callback function whenever the credentials are changed. This is useful because the token is automatically refreshed after its expiration.
```php
$fManager->setCredentialsCallback(new class implements \Fakturoid\Auth\CredentialCallback {
    public function __invoke(?\Fakturoid\Auth\Credentials $credentials = null): void
    {
        // Save credentials to database or another storage
    }
});
```
### Upload credentials to the Fakturoid object
```php
$fManager = new \Fakturoid\FakturoidManager(
    new ClientInterface(), // PSR-18 client
    '{fakturoid-account-slug}',
    '{fakturoid-client-id}',
    '{fakturoid-client-secret}',
    'PHPlib <your@email.cz>'
);
$fManager->getAuthProvider()->setCredentials($credentials);
```

### Switch account

```php

$fManager = new \Fakturoid\FakturoidManager(
    new ClientInterface(), // PSR-18 client
    '{fakturoid-account-slug}',
    '{fakturoid-client-id}',
    '{fakturoid-client-secret}',
    'PHPlib <your@email.cz>'
);
$fManager->authClientCredentials();
$fManager->getSettingProvider()->listBankAccounts();

// switch account and company    
$fManager->switchCompany('{fakturoid-account-slug-another}', null);
$fManager->authClientCredentials();
$fManager->getSettingProvider()->listBankAccounts();
```


## Usage

```php
require __DIR__ . '/vendor/autoload.php';
$fManager = new \Fakturoid\FakturoidManager(
    new ClientInterface(), // PSR-18 client
    '{fakturoid-account-slug}',
    '{fakturoid-client-id}',
    '{fakturoid-client-secret}',
    'PHPlib <your@email.cz>'
);
$fManager->authClientCredentials();
// create subject
$response = $fManager->getSubjectProvider()->create(['name' => 'Firma s.r.o.', 'email' => 'aloha@pokus.cz']);
$subject  = $response->getBody();

// create invoice with lines
$lines    = [['name' => 'Big sale', 'quantity' => 1, 'unit_price' => 1000]];
$response = $fManager->getInvoiceProvider()->create(['subject_id' => $subject->id, 'lines' => $lines]);
$invoice  = $response->getBody();

// send created invoice
$fManager->getInvoiceProvider->fireAction($invoice->id, 'deliver');

// send by mail
$fManager->getInvoiceProvider()->createMessage($invoice->id, ['email' => 'aloha@pokus.cz']);

// to mark invoice as paid and send thank you email
$fManager->getInvoiceProvider()->createPayment($invoice->id, ['paid_on' => (new \DateTime())->format('Y-m-d'), 'send_thank_you_email' => true]);

```

## Downloading an invoice PDF

```php
$invoiceId = 123;
$response = $fManager->getInvoiceProvider()->getPdf($invoiceId);
$data = $response->getBody();
file_put_contents("{$invoiceId}.pdf", $data);
```

If you call `$fManager->getInvoiceProvider()->getPdf()` right after creating an invoice, you'll get
a status code `204` (`No Content`) with empty body, this means the invoice PDF
hasn't yet been generated and you should try again a second or two later.

More info in [API docs](https://www.fakturoid.cz/api/v3/invoices#download-invoice-pdf).

```php
$invoiceId = 123;

// This is just an example, you may want to do this in a background job and be more defensive.
while (true) {
    $response = $fManager->getInvoiceProvider()->getPdf($invoiceId);

    if ($response->getStatusCode() == 200) {
        $data = $response->getBody();
        file_put_contents("{$invoiceId}.pdf", $data);
        break;
    }

    sleep(1);
}
```

## Using `custom_id`

You can use `custom_id` attribute to store your application record ID into our record.
Invoices and subjects can be filtered to find a particular record:

```php
$response = $fManager->getSubjectProvider()->list(['custom_id' => '10']);
$subjects = $response->getBody();
$subject  = null;

if (count($subjects) > 0) {
    $subject = $subjects[0];
}
```

As for subjects, Fakturoid won't let you create two records with the same `custom_id` so you don't have to worry about multiple results.
Also note that the field always returns a string.

## InventoryItem resource

To get all inventory items:

```php
$fManager->getInventoryItemsProvider()->list();
```

To filter inventory items by certain SKU code or article number:

```php
$fManager->getInventoryItemsProvider()->list(['sku' => 'SKU1234']);
$fManager->getInventoryItemsProvider()->list(['article_number' => 'IAN321']);
```

To search inventory items (searches in `name`, `article_number` and `sku`):

```php
$fManager->getInventoryItemsProvider()->listArchived(['query' => 'Item name']);
```

To get all archived inventory items:

```php
$fManager->getInventoryItemsProvider()->listArchived();
```

To get a single inventory item:

```php
$fManager->getInventoryItemsProvider()->get($inventoryItemId);
```

To create an inventory item:

```php
$data = [
    'name' => 'Item name',
    'sku' => 'SKU12345',
    'track_quantity' => true,
    'quantity' => 100,
    'native_purchase_price' => 500,
    'native_retail_price' => 1000
];
$fManager->getInventoryItemsProvider()->create($data)
```

To update an inventory item:

```php
$fManager->getInventoryItemsProvider()->update($inventoryItemId, ['name' => 'Another name']);
```

To archive an inventory item:

```php
$fManager->getInventoryItemsProvider()->archive($inventoryItemId);
```

To unarchive an inventory item:

```php
$fManager->getInventoryItemsProvider()->unArchive($inventoryItemId);
```

To delete an inventory item:

```php
$fManager->getInventoryItemsProvider()->delete($inventoryItemId);
```

## InventoryMove resource

To get get all inventory moves across all inventory items:

```php
$fManager->getInventoryItemsProvider()->listMoves()
```

To get inventory moves for a single inventory item:

```php
$fManager->getInventoryItemsProvider()->listMoves(['inventory_item_id' => $inventoryItemId]);
```

To get a single inventory move:

```php
$fManager->getInventoryItemsProvider()->getMove($inventoryItemId, $inventoryMoveId);
```

To create a stock-in inventory move:

```php
$fManager->getInventoryItemsProvider()->createMove(
    $inventoryItemId,
    [
        'direction' => 'in',
        'moved_on' => '2023-01-12',
        'quantity_change' => 5,
        'purchase_price' => '249.99',
        'purchase_currency' => 'CZK',
        'private_note' => 'Bought with discount'
    ]
)
```

To create a stock-out inventory move:

```php
$fManager->getInventoryItemsProvider()->createMove(
    $inventoryItemId,
    [
        'direction' => 'out',
        'moved_on' => '2023-01-12',
        'quantity_change' => '1.5',
        'retail_price' => 50,
        'retail_currency' => 'EUR',
        'native_retail_price' => '1250'
    ]
);
```

To update an inventory move:

```php
$fManager->getInventoryItemsProvider()->updateMove($inventoryItemId, $inventoryMoveId, ['moved_on' => '2023-01-11']);
```

To delete an inventory move:

```php
$fManager->getInventoryItemsProvider()->updateMove($inventoryItemId, $inventoryMoveId);
```

## Handling errors

Library raises `Fakturoid\Exception\ClientErrorException` for `4xxx` and `Fakturoid\Exception\ServerErrorException` for `5xx` status. You can get response code and response body by calling `getCode()` or `getResponse()->getBody()`.

```php
try {
    $subject = $fManager->getSubjectProvider()->create(['name' => '', 'email' => 'aloha@pokus.cz']);
} catch (\Fakturoid\Exception\ClientErrorException $e) {
    $e->getCode(); // 422
    $e->getMessage(); // Unprocessable entity
    $e->getResponse()->getBody(); // '{"errors":{"name":["je povinná položka","je příliš krátký/á/é (min. 2 znaků)"]}}'
} catch (\Fakturoid\Exception\ServerErrorException $e) {
    $e->getCode(); // 503
    $e->getMessage(); // Fakturoid is in read only state
}

```

### Common problems

- In case of problem please contact our invoicing robot on podpora@fakturoid.cz.

## Development

- To run tests, PHPUnit requires `ext-dom` extension (typically a `php-xml` package on Debian) and `ext-mbstring` extension (`php-mbstring` package).
- If you wish to generate code coverage (and have more intelligent stack traces), you will need [Xdebug](https://xdebug.org/)
  (`php-xdebug` package), it will hook itself into PHPUnit automatically.

### Docker
```shell
docker-compose up -d
docker-compose exec php bash
```

### Testing

Both commands do the same but the second version is a bit faster.

```sh
$ docker-compose exec php composer test:phpunit
$ docker-compose exec php composer coverage:phpunit
# or locally
$ composer test:phpunit
$ composer coverage:phpunit
```

### Code-Style Check

Both commands do the same but the second version seems to have a more intelligent output.

```sh
$ docker-compose exec php composer check:cs
# or locally
$ composer check:cs
```

### Check all requires for PR

```sh
$ docker-compose exec php composer check:all
# or locally
$ composer check:all
```

Or you can fix CS and Rector issues automatically:


```sh
$ docker-compose exec php composer fix:all
# or locally
$ composer fix:all
```