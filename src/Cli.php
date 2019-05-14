<?php
/**
 * @author webworker01
 * @package webworker01/komodophp
 *
 * Interact via komodo-cli to a local full node
 *
 */

namespace webworker01\Komodo;

class Cli
{
    protected $config;
    protected $guzzle;
    protected $ignoreacs = ['BEER', 'PIZZA', 'VOTE2018'];

    public function __construct($config)
    {
        $this->config = $config;
        $this->guzzle = new \GuzzleHttp\Client();
    }

    public function rpc($method, $params=[])
    {
        $host = $this->config['kmd']['host'];
        $port = $this->config['kmd']['port'];
        $user = $this->config['kmd']['username'];
        $pass = $this->config['kmd']['password'];

        $result = $this->guzzle->request(
            'POST',
            $host.':'.$port,
            [
                'debug' => $this->config['debug'],
                'auth' => [$user, $pass],
                'json' => [
                    'method' => $method,
                    'params' => $params
                ],
            ]
        );

        if ($result->getStatusCode() == '200') {
            return json_decode($result->getBody())->result;
        } else {
            return false;
        }
    }

    /**
     * Pass in $rawtx->vout[1]->scriptPubKey->hex
     *
     * @author DeckerSU
     * @author webworker01
     * @see https://secure.php.net/manual/en/function.pack.php
     */
    public function decodeOpReturn($scriptPubKeyHex)
    {
        $scriptPubKeyHex = pack("H*",$scriptPubKeyHex);

        $first_ord = ord($scriptPubKeyHex[1]);

        //This seems to be based on the length of the opreturn (doesn't really say much other than that)
        if ($first_ord<=75) {
            $op_return=substr($scriptPubKeyHex, 2, $first_ord);
        } elseif ($first_ord==0x4c) {  // 76
            $op_return=substr($scriptPubKeyHex, 3, ord($scriptPubKeyHex[2]));
        } elseif ($first_ord==0x4d) { // 77
            $op_return=substr($scriptPubKeyHex, 4, ord($scriptPubKeyHex[2])+256*ord($scriptPubKeyHex[3]));
        } else {
            return false;
        }

        $notarization_data = [];

        $extractName = '';
        //32 expects a 32 character prevhash, then a 32 bit int (4 bytes)
        for ($name_length=0; $op_return[(32+4+$name_length)]!="\x0"; $name_length++) {
            if (isset($op_return[(32+4+$name_length)])) {
                $extractName .= $op_return[(32+4+$name_length)];
            } else {
                return false;
            }
        }

        //@todo get this dynamically
        $shortForm = ['CHIPS', 'GAME', 'HUSH', 'EMC2', 'GIN'];

        //KMD->BTC
        if (substr($op_return,-strlen('KMD')-1) == "KMD\x0") {
            // prevheight V - unsigned long (always 32 bit, little endian order)
            $notarization_data = unpack('a32prevhash/Vprevheight/a32btctxid/a'.(3+1).'name',$op_return);
            $notarization_data['prevhash'] = bin2hex(strrev($notarization_data['prevhash']));
            $notarization_data['prevheight'] = bin2hex(strrev($notarization_data['prevheight']));
            $notarization_data['btctxid'] = bin2hex(strrev($notarization_data['btctxid']));
            $notarization_data['name'] = 'KMD';

        //SHORT FORM (No MOM)
        } elseif (in_array($extractName, $shortForm)) {
            $name_length = strlen($extractName);
            $notarization_data = unpack("a32prevhash/Vprevheight/a".($name_length+1)."name",$op_return);
            $notarization_data["prevhash"] = bin2hex(strrev($notarization_data["prevhash"]));
            $notarization_data['prevheight'] = bin2hex(strrev($notarization_data['prevheight']));
            $notarization_data["name"] = trim($notarization_data["name"]);

        //ASSETS->KMD
        } else {
            for ($name_length=0; $op_return[32+4+$name_length]!="\x0"; $name_length++);
            $notarization_data = unpack("a32prevhash/Vprevheight/a".($name_length+1)."name/a32MoMhash/VMoMdepth",$op_return);
            $notarization_data["prevhash"] = bin2hex(strrev($notarization_data["prevhash"]));
            $notarization_data['prevheight'] = bin2hex(strrev($notarization_data['prevheight']));
            $notarization_data["MoMhash"] = bin2hex(strrev($notarization_data["MoMhash"]));
            $notarization_data["name"] = trim($notarization_data["name"]);
        }

        $notarization_data['extractName'] = $extractName;

        return $notarization_data;
    }
}
