<?php
return [
    //Environment=> test/production
    'env' => 'test',
    //Google Ads
    'production' => [
        'developerToken' => "YOUR-DEV-TOKEN",
        'clientCustomerId' => "CLIENT-CUSTOMER-ID",
        'userAgent' => "YOUR-NAME",
        'clientId' => "CLIENT-ID",
        'clientSecret' => "CLIENT-SECRET",
        'refreshToken' => "REFRESH-TOKEN"
    ],
    'test' => [
        'developerToken' => "4bzL4Uv_ogIfcRBPrdUkzA",
        'clientCustomerId' => "431-434-9055",
        'userAgent' => "ERP",
        'clientId' => "1040644275419-lhhln7p9j0vs1g3vqvucnaoqv0u5qett.apps.googleusercontent.com",
        'clientSecret' => "mzmmB_fR-T4yHmwSZyOABpgy",
        'refreshToken' => "1//03_z2atVGWs_dCgYIARAAGAMSNwF-L9IrlvMb4wug6E33S7_YevKjB15qXhUhWNTvU4jBqkZTTrDSGq6CoTTXnFL9rEGGByG9w5U"
    ],
    'oAuth2' => [
        'authorizationUri' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'redirectUri' => 'urn:ietf:wg:oauth:2.0:oob',
        'tokenCredentialUri' => 'https://www.googleapis.com/oauth2/v4/token',
        'scope' => 'https://www.googleapis.com/auth/adwords'
    ]
];