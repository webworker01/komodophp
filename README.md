# komodophp

A library for manipulating Komodo addresses and keys and interacting with Electrum servers

# Usage

`composer require webworker01/komodophp`

Working with Addressess and Keys
----------

For manipulating Komodo addresses and keys please see docs in https://github.com/Bit-Wasp/bitcoin-php

You will pass in the Komodo object

```php
use webworker01\Komodo\Komodo;
use BitWasp\Bitcoin\Address\AddressCreator;

$komodo = new Komodo();
$addrCreator = new AddressCreator();

$addressInput = 'RWEBo1Yp4uGkeXPi1ZGQARfLPkGmoW1MwY';

try {
    $address = $addrCreator->fromString($addressInput, $komodo);
} catch ( \BitWasp\Bitcoin\Exceptions\UnrecognizedAddressException $e) {
    $errormessage = 'The address you entered is not valid, please try again';
    echo $errormessage;
}
```

Working with ElectrumX
----------

```php
use webworker01\Komodo\Electrum;

$electrum = new Electrum();

$electrum->connect($electrumhost, $electrumport);
$unspent = $electrum->blockchainAddressListunspent($address);
```

Working with CLI
----------

Simple CLI/RPC interface to a locally running full node.

This class is not aware of commands in komodod, so any data validation is handled by the daemon.

```php
use webworker01\Komodo\Cli;

$cli = new Cli();

$notarizations = $cli->run('getaddresstxids', [['addresses' => [$notarizationAddress], 'start' => $currentblock, 'end' => $currentendblock]] );

foreach ($notarizations as $tx) {
    $rawtx = $cli->run('getrawtransaction', [$tx, 1]);
}
```


Opreturn
----------

Decode opreturns to search for notarization data

```php
use webworker01\Komodo\Opreturn;

//Currently requires an array of coins that use short form opreturn data
$shortForm = ['CHIPS', 'GAME', 'HUSH', 'EMC2', 'GIN'];

$opreturn = new Opreturn($shortForm);

$notarisationdata = $opreturn->decode($rawtx->vout[1]->scriptPubKey->hex);
```
