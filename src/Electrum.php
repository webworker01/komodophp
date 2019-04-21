<?php
/**
 * @author webworker01
 * @package webworker01/komodophp
 * 
 * @see https://github.com/pbca26/electrum-proxy/blob/master/routes/electrum/electrumjs.core.js
 * @see http://docs.electrum.org/en/latest/protocol.html
 * @see https://github.com/jl777/coins/tree/master/electrums
 * 
 * @todo handle errors here instead of forcing client code to do so
 */

namespace webworker01\Komodo;

class Electrum
{
    private $socket;

    /**
     * Create the connection to the electrumX server
     * @param $url String The IP or hostname of the elctrum server
     * @param $port int The port to connect to on the electrum server
     */
    public function connect($url, $port)
    {
        $factory = new \Socket\Raw\Factory();

        $this->socket = $factory->createClient($url.':'.$port, 15);
        // echo 'Connected to ' . $this->socket->getPeerName() . PHP_EOL;
    }

    /**
     * Close the connection to the electrum server
     */
    public function close()
    {
        $this->socket->close();
    }

    /**
     * Formats and sends a request to the electrum server
     */
    public function request($method, $params = [], $id = null)
    {
        $request = json_encode([
            'id' => 0,
            'method' => $method,
            'params' => $params
        ]);

        $this->socket->write($request."\n");

        $response = '';
        while ($buffer = $this->socket->read(1024)) {
            $response .= $buffer;
            if (strpos($buffer, "\n") !== false) break;
        }

        return json_decode($response);
    }

    public function serverVersion()
    {
        return $this->request('server.version');
    }

    public function serverBanner()
    {
        return $this->request('server.banner');
    }

    public function serverDonationAddress()
    {
        return $this->request('server.donation_address');
    }

    public function serverPeersSubscribe()
    {
        return $this->request('server.peers.subscribe', []);
    }

    public function blockchainNumblocksSubscribe()
    {
        return $this->request('blockchain.numblocks.subscribe');
    }

    public function blockchainHeadersSubscribe()
    {
        return $this->request('blockchain.headers.subscribe');
    }

    public function blockchainAddressSubscribe($address)
    {
        return $this->request('blockchain.address.subscribe', [$address]);
    }

    public function blockchainAddressGetHistory($address)
    {
        return $this->request('blockchain.address.get_history', [$address]);
    }

    public function blockchainAddressGetMempool($address)
    {
        return $this->request('blockchain.address.get_mempool', [$address]);
    }

    public function blockchainAddressGetBalance($address)
    {
        return $this->request('blockchain.address.get_balance', [$address]);
    }

    public function blockchainAddressListunspent($address)
    {
        return $this->request('blockchain.address.listunspent', [$address]);
    }
    
    public function blockchainBlockGetHeader($height)
    {
        return $this->request('blockchain.block.get_header', [height]);
    }
    
    public function blockchainBlockGetChunk($index)
    {
        return $this->request('blockchain.block.get_chunk', [index]);
    }
    
    public function blockchainEstimatefee($number)
    {
        return $this->request('blockchain.estimatefee', [$number]);
    }

    public function blockchainRelayfee()
    {
        return $this->request('blockchain.relayfee');
    }
    
    public function blockchainTransactionBroadcast($rawtx)
    {
        return $this->request('blockchain.transaction.broadcast', [$rawtx]);
    }
    
    public function blockchainTransactionGet($tx_hash)
    {
        return $this->request('blockchain.transaction.get', [$tx_hash]);
    }
    
    public function blockchainTransactionGetMerkle($tx_hash, $height)
    {
        return $this->request('blockchain.transaction.get_merkle', [$tx_hash, $height]);
    }
}
