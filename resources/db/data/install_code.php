<?php

$datas = [
    [
        'title' => 'Mercado Page',
        'orginal_title' => 'Mercado Page',
        'model' => 'WalletMercadopagePS_Model_PaymentMethodsMercadopage',
        'type' => 'url',
        'state_name' => 't',
        'url' => 'walletmercadopageps/mobile_walletmercadopage/find',
        'code' => 'WalletMercadopagePS',
    ]
];

foreach ($datas as $data) {
    $method = new Wallet_Model_PaymentSystems();
    $method
        ->setData($data)
        ->insertOnce(['code']);
}
