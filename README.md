# Soluti PHP sdk

Its a simple SDK for PHP language that abstract Soluti's digital signature service. In the first moment is possible to sign PDF documents authenticating using _OAuth_ or _Basic Credentials_

## Installation

```bash
composer require "memeddev/soluti-sdk-php=*"
```

## Basic Usage

```php
use Memed\Soluti\Auth\Client;
use Memed\Soluti\Auth\Credentials;
use Memed\Soluti\Auth\Token;
use Memed\Soluti\Config;
use Memed\Soluti\Document;
use Memed\Soluti\Manager;
use Memed\Soluti\Signer;
use GuzzleHttp\Client as GuzzleClient;

// -----------------------
// Authentication strategy
// -----------------------

// Using Credentials
$token = new Credentials(
    new Client('CLIENT_ID', 'CLIENT_SECRET'),
    'USERNAME',
    'PASSWORD',
    60 // How much longer token will be acceptable after authentication (in seconds)
);

// Using OAuth token
$token = new Token('some-secret-oauth-token', 'bearer');

// -----------------------
// Document signature
// -----------------------
 
// Defines base URL's
$config = new Config([
    'url_cess' => 'http://cess:8080',
    'url_vaultid' => 'https://apicloudid.hom.vaultid.com.br/oauth',
]);

// Initializes signer service
$manager = new Manager($config, new Client(new GuzzleClient());
$signer = new Signer($manager);

// Creates a document instance using file which you want to sign.
$document = new Document(__DIR__.'/file_to_sign.pdf');

// Defines directory to save signed documents.
$destinationDir = '/some/directory';

// Signs document
$files = $signer->sign($document, $token, $destinationDir);

// Result
// array(1) {
//   [0] =>
//   string(32) "/some/directory/signed_file.pdf"
// }
```
