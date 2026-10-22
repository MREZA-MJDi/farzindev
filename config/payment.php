<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | Used when the application needs a default external
    | installment provider.
    |
    */

    'default' => env(
        'PAYMENT_DEFAULT_GATEWAY',
        'digipay'
    ),

    /*
    |--------------------------------------------------------------------------
    | DigiPay
    |--------------------------------------------------------------------------
    */

    'digipay' => [
        'enabled' => (bool) env(
            'DIGIPAY_ENABLED',
            false
        ),

        // True only after mapping the official merchant API contract.
        'api_contract_confirmed' => (bool) env(
            'DIGIPAY_API_CONTRACT_CONFIRMED',
            false
        ),

        'merchant_id' => env(
            'DIGIPAY_MERCHANT_ID'
        ),

        'client_id' => env(
            'DIGIPAY_CLIENT_ID'
        ),

        'client_secret' => env(
            'DIGIPAY_CLIENT_SECRET'
        ),

        'api_key' => env(
            'DIGIPAY_API_KEY'
        ),

        'access_token' => env(
            'DIGIPAY_ACCESS_TOKEN'
        ),

        'base_url' => env(
            'DIGIPAY_BASE_URL',
            ''
        ),

        'callback_url' => env(
            'DIGIPAY_CALLBACK_URL'
        ),

        'timeout' => (int) env(
            'DIGIPAY_TIMEOUT',
            30
        ),

        'endpoints' => [
            'create' => env(
                'DIGIPAY_CREATE_ENDPOINT',
                ''
            ),

            'verify' => env(
                'DIGIPAY_VERIFY_ENDPOINT',
                ''
            ),

            'status' => env(
                'DIGIPAY_STATUS_ENDPOINT',
                ''
            ),

            'cancel' => env(
                'DIGIPAY_CANCEL_ENDPOINT',
                ''
            ),

            'refund' => env(
                'DIGIPAY_REFUND_ENDPOINT',
                ''
            ),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SnapPay
    |--------------------------------------------------------------------------
    */

    'snappay' => [
        'enabled' => (bool) env(
            'SNAPPAY_ENABLED',
            false
        ),

        // True only after mapping the official merchant API contract.
        'api_contract_confirmed' => (bool) env(
            'SNAPPAY_API_CONTRACT_CONFIRMED',
            false
        ),

        'merchant_id' => env(
            'SNAPPAY_MERCHANT_ID'
        ),

        'client_id' => env(
            'SNAPPAY_CLIENT_ID'
        ),

        'client_secret' => env(
            'SNAPPAY_CLIENT_SECRET'
        ),

        'api_key' => env(
            'SNAPPAY_API_KEY'
        ),

        'access_token' => env(
            'SNAPPAY_ACCESS_TOKEN'
        ),

        'base_url' => env(
            'SNAPPAY_BASE_URL',
            ''
        ),

        'callback_url' => env(
            'SNAPPAY_CALLBACK_URL'
        ),

        'timeout' => (int) env(
            'SNAPPAY_TIMEOUT',
            30
        ),

        'endpoints' => [
            'create' => env(
                'SNAPPAY_CREATE_ENDPOINT',
                ''
            ),

            'verify' => env(
                'SNAPPAY_VERIFY_ENDPOINT',
                ''
            ),

            'status' => env(
                'SNAPPAY_STATUS_ENDPOINT',
                ''
            ),

            'cancel' => env(
                'SNAPPAY_CANCEL_ENDPOINT',
                ''
            ),

            'refund' => env(
                'SNAPPAY_REFUND_ENDPOINT',
                ''
            ),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | TorobPay
    |--------------------------------------------------------------------------
    */

    'torobpay' => [
        'enabled' => (bool) env(
            'TOROBPAY_ENABLED',
            false
        ),

        // True only after mapping the official merchant API contract.
        'api_contract_confirmed' => (bool) env(
            'TOROBPAY_API_CONTRACT_CONFIRMED',
            false
        ),

        'merchant_id' => env(
            'TOROBPAY_MERCHANT_ID'
        ),

        'client_id' => env(
            'TOROBPAY_CLIENT_ID'
        ),

        'client_secret' => env(
            'TOROBPAY_CLIENT_SECRET'
        ),

        'api_key' => env(
            'TOROBPAY_API_KEY'
        ),

        'access_token' => env(
            'TOROBPAY_ACCESS_TOKEN'
        ),

        'base_url' => env(
            'TOROBPAY_BASE_URL',
            ''
        ),

        'callback_url' => env(
            'TOROBPAY_CALLBACK_URL'
        ),

        'timeout' => (int) env(
            'TOROBPAY_TIMEOUT',
            30
        ),

        'endpoints' => [
            'create' => env(
                'TOROBPAY_CREATE_ENDPOINT',
                ''
            ),

            'verify' => env(
                'TOROBPAY_VERIFY_ENDPOINT',
                ''
            ),

            'status' => env(
                'TOROBPAY_STATUS_ENDPOINT',
                ''
            ),

            'cancel' => env(
                'TOROBPAY_CANCEL_ENDPOINT',
                ''
            ),

            'refund' => env(
                'TOROBPAY_REFUND_ENDPOINT',
                ''
            ),
        ],
    ],

];
