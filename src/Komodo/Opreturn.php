<?php
/**
 * @author DeckerSU
 * @author webworker01
 * @package webworker01/komodophp
 *
 * Decode opreturn data
 *
 * @todo dynamic detection of short form opreturn data
 */

namespace webworker01\Komodo;

class Opreturn
{
    /**
     * Currently requires an array of coins that use short form opreturn data
     *
     * @param deprecated $shortForm This parameter is no longer required
     */
    public function __construct($shortForm = '') {}

    /**
     * Pass in $rawtx->vout[1]->scriptPubKey->hex
     *
     * data:
     * 32    prevhash
     * 4     prevheight
     * 32    (optional btc/ltc tx id)
     * x+1   name and end name control character \x0
     * 32    momhash
     * 4     mom depth
     * @see https://secure.php.net/manual/en/function.pack.php
     * @see https://github.com/DeckerSU/komodo_scripts/blob/master/notarizations_count.php#L140
     * @see https://github.com/coinspark/php-OP_RETURN/blob/6814b771b459c3c144f0e1719cb7a6ad0aa7195e/OP_RETURN.php#L766
     * @see https://en.bitcoin.it/wiki/Script#Constants
     *
     * @param String hex from script pubkey
     * @return (Array|false) Notarization data or not
     */
    function decode($scriptPubKeyBinary)
    {
        $length_with_mom = 72;
        $length_no_mom = 36;

        $first_ord = ord($scriptPubKeyBinary[1]);
        $second_ord = ord($scriptPubKeyBinary[2]);

        $notarization_data = [];

        if ($first_ord == 0x4c) {
            //there's MoM data
            $name_length = $second_ord - $length_with_mom;
            $op_return = substr($scriptPubKeyBinary, 3, $second_ord);
            $notarization_data = unpack("a32prevhash/Vprevheight/a" . $name_length . "name/a32MoMhash/VMoMdepth", $op_return);
            $notarization_data["MoMhash"] = bin2hex(strrev($notarization_data["MoMhash"]));

        } elseif ($first_ord <= 75) {
            //everything else should fall under here on KMD blockchain
            $op_return = substr($scriptPubKeyBinary, 2, $first_ord);
            $testkmd = substr($op_return, 68, 3);

            if ($testkmd == 'KMD') {
                $notarization_data = unpack('a32prevhash/Vprevheight/a32btctxid/a4name',$op_return);
                $notarization_data['btctxid'] = bin2hex(strrev($notarization_data['btctxid']));
            } else {
                $name_length = $first_ord - $length_no_mom;
                $notarization_data = unpack("a32prevhash/Vprevheight/a". $name_length ."name", $op_return);
            }

        } else {
            //fallback to when we were pulling BTC/LTC data from explorer without control codes
            $notarization_data = unpack('a32prevhash/Vprevheight/a4name', $scriptPubKeyBinary);
        }

        $notarization_data["prevhash"] = bin2hex(strrev($notarization_data["prevhash"]));
        $notarization_data["name"] = trim($notarization_data["name"]);

        return($notarization_data);
    }

}
