<?php
require_once __DIR__.'/../sb_base.php';
require_once __DIR__.'/../lib/stripe/init.php';
require_once __DIR__.'/../lib/stripe/config.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

//declare is json type
header('Content-Type: application/json');

$amount = filter_input(INPUT_POST,'amount',FILTER_VALIDATE_FLOAT);

if(!$amount){
    echo json_encode(['error' => 'Invalid amount']);
    exit;
}

//change to integer
$amountInSen = intval($amount * 100);

try{
    $intent = \Stripe\PaymentIntent::create([
        'amount' => $amountInSen,
        'currency'=> 'myr',
        'payment_method_types' => ['card'],
    ]);

    echo json_encode([
        'clientSecret' => $intent->client_secret
    ]);
}catch(Exception $ex){
    echo json_encode(['error' => $ex->getMessage()]);
}

