<?php

class Comet {
    private $ORIGIN        = 'x3.chatforyoursite.com';
    private $LIMIT         = 1800;
    private $PUBLISH_KEY   = '';
    private $SUBSCRIBE_KEY = '';
    private $SECRET_KEY    = false;
    private $SSL           = false;

    function __construct(
        $publish_key,
        $subscribe_key,
        $secret_key = false,
        $ssl = false
    ) {
        $this->PUBLISH_KEY   = $publish_key;
        $this->SUBSCRIBE_KEY = $subscribe_key;
        $this->SECRET_KEY    = $secret_key;
        $this->SSL           = $ssl;

        if ($ssl) $this->ORIGIN = 'https://' . $this->ORIGIN;
        else      $this->ORIGIN = 'http://'  . $this->ORIGIN;
    }

    function publish($args) {
        if (empty($args['channel']) || empty($args['message'])) {
            echo('Missing Channel or Message');
            return false;
        }

        $channel = $args['channel'];
        $message = json_encode($args['message']);

        $string_to_sign = implode( '/', array(
            $this->PUBLISH_KEY,
            $this->SUBSCRIBE_KEY,
            $this->SECRET_KEY,
            $channel,
            $message
        ) );

        $signature = $this->SECRET_KEY ? md5($string_to_sign) : '0';

        if (strlen($message) > $this->LIMIT) {
            echo('Message TOO LONG (' . $this->LIMIT . ' LIMIT)');
            return array( 0, 'Message Too Long.' );
        }

        return $this->_request(array(
            'publish',
            $this->PUBLISH_KEY,
            $this->SUBSCRIBE_KEY,
            $signature,
            $channel,
            '0',
            $message
        ));
    }


    function history($args) {
        $limit   = isset($args['limit']) ? (int)$args['limit'] : 10;
        $channel = isset($args['channel']) ? $args['channel'] : null;

        if (empty($channel)) {
            echo('Missing Channel');
            return false;
        }

        return $this->_request(array(
            'history',
            $this->SUBSCRIBE_KEY,
            $channel,
            '0',
            $limit
        ));
    }

    function time() {
        $response = $this->_request(array(
            'time',
            '0'
        ));

        return $response[0];
    }

    private function _request($request) {
        $request = array_map( 'Comet::_encode', $request );
        array_unshift( $request, $this->ORIGIN );

        $ctx = stream_context_create(array(
            'http' => array( 'timeout' => 200 ) 
        ));

        return json_decode( @file_get_contents(
            implode( '/', $request ), 0, $ctx
        ), true );
    }

    private static function _encode($part) {
        return implode( '', array_map(
            'Comet::_encode_char', str_split($part)
        ) );
    }

    private static function _encode_char($char) {
        if (strpos( ' ~`!@#$%^&*()+=[]\\{}|;\':",./<>?', $char ) === false)
            return $char;
        return rawurlencode($char);
    }
}