<?php

return array(

    /**
     * Rest client environment for selecting services
     * Available: 'production', 'dev'
     */
    'environment' => env('REST_CLIENT_ENV', 'production'),

    /**
     * Debug mode for showing logs
     */
    'debug_mode' => false,

    /**
     * Default Service
     */
    'default_service_name' => 'someline-starter',

    /**
     * Shared config for services
     */
    'shared_service_config' => [

        'headers' => [
            'User-Agent' => 'someline-testing/1.0',
        ],

        'oauth2_credentials' => [
            'client_id' => 'SomelineFvGXRmBv',
            'client_secret' => 'WFYBPbkOBv7hTby8vGL2SPOOq2GKYQdSIDGXcLsS',
        ],

        'oauth2_access_token_url' => 'oauth/access_token',

        'oauth2_grant_types' => [
            'client_credentials' => 'client_credentials',
            'password' => 'password',
        ],

    ],

    /**
     * Services
     */
    'services' => [

        'dev' => [

            // Someline Starter API Service
            'someline-starter' => [

                'base_uri' => 'http://someline-starter.app/api/',

                'headers' => [
                    'Accept' => 'application/x.someline.v1+json',
                ],

            ],

        ],

        'production' => [

            // Someline Starter API Service
            'someline-starter' => [

                'base_uri' => 'http://someline-starter.app/api/',

                'headers' => [
                    'Accept' => 'application/x.someline.v1+json',
                ],

            ],

        ],

    ],

);
