<?php

namespace ccxt;

class bitstamp1 extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'bitstamp1',
            'name' => 'Bitstamp v1',
            'countries' => 'GB',
            'rateLimit' => 1000,
            'version' => 'v1',
            'has' => array (
                'CORS' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/27786377-8c8ab57e-5fe9-11e7-8ea4-2b05b6bcceec.jpg',
                'api' => 'https://www.bitstamp.net/api',
                'www' => 'https://www.bitstamp.net',
                'doc' => 'https://www.bitstamp.net/api',
            ),
            'requiredCredentials' => array (
                'apiKey' => true,
                'secret' => true,
                'uid' => true,
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'ticker',
                        'ticker_hour',
                        'order_book',
                        'transactions',
                        'eur_usd',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'balance',
                        'user_transactions',
                        'open_orders',
                        'order_status',
                        'cancel_order',
                        'cancel_all_orders',
                        'buy',
                        'sell',
                        'bitcoin_deposit_address',
                        'unconfirmed_btc',
                        'ripple_withdrawal',
                        'ripple_address',
                        'withdrawal_requests',
                        'bitcoin_withdrawal',
                    ),
                ),
            ),
            'markets' => array (
                'BTC/USD' => array ( 'id' => 'btcusd', 'symbol' => 'BTC/USD', 'base' => 'BTC', 'quote' => 'USD', 'maker' => 0.0025, 'taker' => 0.0025 ),
                'BTC/EUR' => array ( 'id' => 'btceur', 'symbol' => 'BTC/EUR', 'base' => 'BTC', 'quote' => 'EUR', 'maker' => 0.0025, 'taker' => 0.0025 ),
                'EUR/USD' => array ( 'id' => 'eurusd', 'symbol' => 'EUR/USD', 'base' => 'EUR', 'quote' => 'USD', 'maker' => 0.0025, 'taker' => 0.0025 ),
                'XRP/USD' => array ( 'id' => 'xrpusd', 'symbol' => 'XRP/USD', 'base' => 'XRP', 'quote' => 'USD', 'maker' => 0.0025, 'taker' => 0.0025 ),
                'XRP/EUR' => array ( 'id' => 'xrpeur', 'symbol' => 'XRP/EUR', 'base' => 'XRP', 'quote' => 'EUR', 'maker' => 0.0025, 'taker' => 0.0025 ),
                'XRP/BTC' => array ( 'id' => 'xrpbtc', 'symbol' => 'XRP/BTC', 'base' => 'XRP', 'quote' => 'BTC', 'maker' => 0.0025, 'taker' => 0.0025 ),
                'LTC/USD' => array ( 'id' => 'ltcusd', 'symbol' => 'LTC/USD', 'base' => 'LTC', 'quote' => 'USD', 'maker' => 0.0025, 'taker' => 0.0025 ),
                'LTC/EUR' => array ( 'id' => 'ltceur', 'symbol' => 'LTC/EUR', 'base' => 'LTC', 'quote' => 'EUR', 'maker' => 0.0025, 'taker' => 0.0025 ),
                'LTC/BTC' => array ( 'id' => 'ltcbtc', 'symbol' => 'LTC/BTC', 'base' => 'LTC', 'quote' => 'BTC', 'maker' => 0.0025, 'taker' => 0.0025 ),
                'ETH/USD' => array ( 'id' => 'ethusd', 'symbol' => 'ETH/USD', 'base' => 'ETH', 'quote' => 'USD', 'maker' => 0.0025, 'taker' => 0.0025 ),
                'ETH/EUR' => array ( 'id' => 'etheur', 'symbol' => 'ETH/EUR', 'base' => 'ETH', 'quote' => 'EUR', 'maker' => 0.0025, 'taker' => 0.0025 ),
                'ETH/BTC' => array ( 'id' => 'ethbtc', 'symbol' => 'ETH/BTC', 'base' => 'ETH', 'quote' => 'BTC', 'maker' => 0.0025, 'taker' => 0.0025 ),
            ),
        ));
    }

    public function fetch_order_book ($symbol, $params = array ()) {
        if ($symbol !== 'BTC/USD')
            throw new ExchangeError ($this->id . ' ' . $this->version . " fetchOrderBook doesn't support " . $symbol . ', use it for BTC/USD only');
        $orderbook = $this->publicGetOrderBook ($params);
        $timestamp = intval ($orderbook['timestamp']) * 1000;
        return $this->parse_order_book($orderbook, $timestamp);
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        if ($symbol !== 'BTC/USD')
            throw new ExchangeError ($this->id . ' ' . $this->version . " fetchTicker doesn't support " . $symbol . ', use it for BTC/USD only');
        $ticker = $this->publicGetTicker ($params);
        $timestamp = intval ($ticker['timestamp']) * 1000;
        $vwap = floatval ($ticker['vwap']);
        $baseVolume = floatval ($ticker['volume']);
        $quoteVolume = $baseVolume * $vwap;
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => floatval ($ticker['high']),
            'low' => floatval ($ticker['low']),
            'bid' => floatval ($ticker['bid']),
            'ask' => floatval ($ticker['ask']),
            'vwap' => $vwap,
            'open' => floatval ($ticker['open']),
            'close' => null,
            'first' => null,
            'last' => floatval ($ticker['last']),
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => $baseVolume,
            'quoteVolume' => $quoteVolume,
            'info' => $ticker,
        );
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = null;
        if (is_array ($trade) && array_key_exists ('date', $trade)) {
            $timestamp = intval ($trade['date']) * 1000;
        } else if (is_array ($trade) && array_key_exists ('datetime', $trade)) {
            // $timestamp = $this->parse8601 ($trade['datetime']);
            $timestamp = intval ($trade['datetime']) * 1000;
        }
        $side = ($trade['type'] === 0) ? 'buy' : 'sell';
        $order = null;
        if (is_array ($trade) && array_key_exists ('order_id', $trade))
            $order = (string) $trade['order_id'];
        if (is_array ($trade) && array_key_exists ('currency_pair', $trade)) {
            if (is_array ($this->markets_by_id) && array_key_exists ($trade['currency_pair'], $this->markets_by_id))
                $market = $this->markets_by_id[$trade['currency_pair']];
        }
        return array (
            'id' => (string) $trade['tid'],
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $market['symbol'],
            'order' => $order,
            'type' => null,
            'side' => $side,
            'price' => floatval ($trade['price']),
            'amount' => floatval ($trade['amount']),
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        if ($symbol !== 'BTC/USD')
            throw new ExchangeError ($this->id . ' ' . $this->version . " fetchTrades doesn't support " . $symbol . ', use it for BTC/USD only');
        $market = $this->market ($symbol);
        $response = $this->publicGetTransactions (array_merge (array (
            'time' => 'minute',
        ), $params));
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function fetch_balance ($params = array ()) {
        $balance = $this->privatePostBalance ();
        $result = array ( 'info' => $balance );
        $currencies = is_array ($this->currencies) ? array_keys ($this->currencies) : array ();
        for ($i = 0; $i < count ($currencies); $i++) {
            $currency = $currencies[$i];
            $lowercase = strtolower ($currency);
            $total = $lowercase . '_balance';
            $free = $lowercase . '_available';
            $used = $lowercase . '_reserved';
            $account = $this->account ();
            $account['free'] = $this->safe_float($balance, $free, 0.0);
            $account['used'] = $this->safe_float($balance, $used, 0.0);
            $account['total'] = $this->safe_float($balance, $total, 0.0);
            $result[$currency] = $account;
        }
        return $this->parse_balance($result);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        if ($type !== 'limit')
            throw new ExchangeError ($this->id . ' ' . $this->version . ' accepts limit orders only');
        if ($symbol !== 'BTC/USD')
            throw new ExchangeError ($this->id . ' v1 supports BTC/USD orders only');
        $method = 'privatePost' . $this->capitalize ($side);
        $order = array (
            'amount' => $amount,
            'price' => $price,
        );
        $response = $this->$method (array_merge ($order, $params));
        return array (
            'info' => $response,
            'id' => $response['id'],
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        return $this->privatePostCancelOrder (array ( 'id' => $id ));
    }

    public function parse_order_status ($order) {
        if (($order['status'] === 'Queue') || ($order['status'] === 'Open'))
            return 'open';
        if ($order['status'] === 'Finished')
            return 'closed';
        return $order['status'];
    }

    public function fetch_order_status ($id, $symbol = null) {
        $this->load_markets();
        $response = $this->privatePostOrderStatus (array ( 'id' => $id ));
        return $this->parse_order_status($response);
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = null;
        if ($symbol)
            $market = $this->market ($symbol);
        $pair = $market ? $market['id'] : 'all';
        $request = array_merge (array ( 'id' => $pair ), $params);
        $response = $this->privatePostOpenOrdersId ($request);
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        throw new NotSupported ($this->id . ' fetchOrder is not implemented yet');
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'] . '/' . $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        if ($api === 'public') {
            if ($query)
                $url .= '?' . $this->urlencode ($query);
        } else {
            $this->check_required_credentials();
            $nonce = (string) $this->nonce ();
            $auth = $nonce . $this->uid . $this->apiKey;
            $signature = $this->encode ($this->hmac ($this->encode ($auth), $this->encode ($this->secret)));
            $query = array_merge (array (
                'key' => $this->apiKey,
                'signature' => strtoupper ($signature),
                'nonce' => $nonce,
            ), $query);
            $body = $this->urlencode ($query);
            $headers = array (
                'Content-Type' => 'application/x-www-form-urlencoded',
            );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function request ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $response = $this->fetch2 ($path, $api, $method, $params, $headers, $body);
        if (is_array ($response) && array_key_exists ('status', $response))
            if ($response['status'] === 'error')
                throw new ExchangeError ($this->id . ' ' . $this->json ($response));
        return $response;
    }
}
