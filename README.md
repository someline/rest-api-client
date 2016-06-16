# Someline Starter API Client

[![Latest Version](https://img.shields.io/github/release/libern/someline-starter-api-client.svg?style=flat-square)](https://github.com/libern/someline-starter-api-client/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/libern/someline-starter-api-client.svg?style=flat-square)](https://packagist.org/packages/libern/someline-starter-api-client)

Someline Starter API Client is a client for accessing APIs created using [Someline Starter](https://github.com/libern/someline-starter) framework.

## Install

### Via Composer

Install composer package to your laravel project

``` bash
composer require libern/someline-starter-api-client
```

Add Service Provider to `config/app.php`

``` php
    'providers' => [
        ...
        Libern\Rest\RestClientServiceProvider::class,
        ...
    ],
```

Publishing config file. 

``` bash
php artisan vendor:publish
```

After published, config file for Rest Client is `config/rest-client.php`, you will need to config it to use Rest Client.

## Usage

``` php
$restClient = new \Libern\Rest\RestClient('someline-starter');

$restClient->setOAuthUserCredentials([
    'username' => 'libern@someline.com',
    'password' => 'Abc12345',
]);
$restClient->withOAuthTokenTypeUser();

$response = $restClient->get("users")->getResponse();
if (!$restClient->isResponseStatusCode(200)) {
    $restClient->printResponseOriginContent();
    $responseMessage = $restClient->getResponseMessage();
    print_r($responseMessage);
} else {
    $responseData = $restClient->getResponseData();
    print_r($responseData);
}
```

## Testing

``` bash
phpunit
```

## Contributing

Please see [CONTRIBUTING](https://github.com/libern/someline-starter-api-client/blob/master/CONTRIBUTING.md) for details.

## Credits

- [Libern](https://github.com/libern)
- [All Contributors](https://github.com/libern/someline-starter-api-client/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
