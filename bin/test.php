<?php

require_once __DIR__ . '/../vendor/autoload.php';


//$f = new \Fakturoid\Client('symetrodev', 'd794c324f531966e8016fcc11cce3d0d4736101f', '9eb3fbf404d153c4f1c1828b7861cd8da96ed46b','PHPlib <your@email.cz>');

$client = new \GuzzleHttp\Client();
$fManager = new \Fakturoid\FakturoidManager(
    $client,
    'symetrodev',
    'b9c6ec93c830ee0706303b0af7ffe088b8afd16f',
    '45b79e165e77116e409a6e7e0373a5df11a077c7',
    'PHPlib <asdasd@adsasd.cz>',
    'https://fakauth.tomaskulhanek.cz'
);
$fManager = new \Fakturoid\FakturoidManager(
    $client,
    'symetrodev',
    'd794c324f531966e8016fcc11cce3d0d4736101f',
    '9eb3fbf404d153c4f1c1828b7861cd8da96ed46b',
    'PHPlib <asdasd@adsasd.cz>'
);
try {
    $fManager->authClientCredentials();

    var_dump($fManager->getSubjectProvider()->create(['']));
} catch (\Fakturoid\Exception\AuthorizationFailedException $e) {
    var_dump('Error ' . $e->getMessage());
    die;
}catch (Exception $exception){
    var_dump(get_class($exception));
    var_dump($exception->getMessage());
    die;
}
die;
try {
    $fManager->switchCompany('tomaskulhanek2', null);
    $fManager->authClientCredentials();
    var_dump(serialize($fManager->getSettingProvider()->getBankAccounts()->getBody()));
} catch (\Fakturoid\Exception\AuthorizationFailedException $e) {
    var_dump('Error ' . $e->getMessage());
    die;
}

die;
$f->authClientCredentials();

// create subject
$response = $f->createSubject(['name' => 'Firma s.r.o.', 'email' => 'jsem@tomaskulhanek.cz']);
$subject = $response->getBody();

// create invoice with lines
$lines = [['name' => 'Big sale', 'quantity' => 1, 'unit_price' => 1000]];
$response = $f->createInvoice(['subject_id' => $subject->id, 'lines' => $lines]);
$invoice = $response->getBody();

// send created invoice
$f->createMessage($invoice->id, ['email' => 'jsem@tomaskulhanek.cz']);

// to mark invoice as paid
$f->createPayment($invoice->id, ['paid_on' => (new \DateTime())->format('Y-m-d'), 'send_thank_you_email' => true]);
sleep(4);
$response = $f->getInvoicePdf($invoice->id);
$data = $response->getBody();

var_dump($data);