# Someline Rest API Client

[![Latest Version](https://img.shields.io/github/release/someline/rest-api-client.svg?style=flat-square)](https://github.com/someline/rest-api-client/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/someline/rest-api-client.svg?style=flat-square)](https://packagist.org/packages/someline/rest-api-client)

Someline Starter API Client is an elegant and smart Rest API Client with OAuth2 authentication support. 

Build for Laravel and [Someline Starter](https://starter.someline.com). 

It can be used for accessing APIs created using [Someline Starter](https://starter.someline.com) framework.

## Install

### Via Composer

Install composer package to your laravel project

``` bash
composer require someline/rest-api-client
```

Add Service Provider to `config/app.php`

``` php
    'providers' => [
        ...
        Someline\Rest\RestClientServiceProvider::class,
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
$restClient = new \Someline\Rest\RestClient('someline-starter');

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

Please see [CONTRIBUTING](https://github.com/someline/rest-api-client/blob/master/CONTRIBUTING.md) for details.

## Credits

- [Someline](https://github.com/libern)
- [All Contributors](https://github.com/someline/rest-api-client/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
