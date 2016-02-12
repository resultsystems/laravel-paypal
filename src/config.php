<?php

return [
    /* Credenciais */
    'username' => env('PAYPAL_USERNAME', ''),
    'password' => env('PAYPAL_PASSWORD', ''),
    'signature' => env('PAYPAL_SIGNATURE', ''),

    /* Email usando apenas para transação IPN */
    'email' => env('PAYPAL_EMAIL', ''),

    /* Moeda a trabalhar */
    'currencyCode' => env('PAYPAL_CURRENCYCODE', 'BRL'),

    /* Linguagem de exibição */
    'locadeCode' => env('PAYPAL_LOCALECODE', 'pt_BR'),

    /* Logo */
    /* A URL do cabeçalho da página deve ter 750px de largura por 90px de altura e deve estar armazenada em um servidor seguro. */
    'HDRIMG' => env('PAYPAL_HDRIMG', false), //Obrigatório https

    //'HDRIMG' => 'https://paypal.app/header-image.png', //Obrigatório https

    /* URLs de retorno */
    'notifynurl' => env('PAYPAL_NOTIFYURL', 'http://paypal.app/paypal/ipn/'),
    'returnurl' => env('PAYPAL_RETURNURL', 'http://paypal.app/pedido/'),
    'cancelurl' => env('PAYPAL_CANCELURL', 'http://paypal.app/pedido/cancel/'),
    'buttonsource' => env('PAYPAL_BUTTONSOURCE', 'EMPRESA'),

    /* Prefixo da rota para log de IPN */
    'route_prefix' => '/paypal/',

    /* Versão da API do Paypal a utilizar */
    'version' => env('PAYPAL_VERSION', '108.0'),
];
