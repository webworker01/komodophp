# komodophp

A library for manipulating Komodo addresses and keys and interacting with Electrum servers

# Usage

`composer require webworker01/komodophp`

Working with Addressess and Keys
----------

For manipulating Komodo addresses and keys please see docs in https://github.com/Bit-Wasp/bitcoin-php

You will pass in the Komodo object 

```php
use webworker01\Komodo;
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
use webworker01\Komodo;

$electrum = new Electrum();

$electrum->connect($electrumhost, $electrumport);
$unspent = $electrum->blockchainAddressListunspent($address);
```

# Todo

* Integrate class to interact with local full node RPC e.g. https://github.com/webworker01/notarystats/blob/master/komodo.php
