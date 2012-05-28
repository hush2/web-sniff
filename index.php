<?php

// Copyright (C) 2012 hush2 <hushywushy@gmail.com>

require 'vendor/flight/Flight.php';

require 'vendor/formatbytes.php';
require 'vendor/htmlcolorizer.php';

define('CRLF', "\r\n");
define('LF', "\n");

Flight::route('GET /', function() {

    // Set form defaults.
    $request->data->gzip = true;
    $request->data->url  = 'http://www.google.com';
    $request->data->http = '1.1';
    $request->data->type = 'get';

    $data['post'] = $request->data;

    Flight::render('index_view', $data);

});

Flight::route('POST /', function() {

    $post = Flight::request()->data;    // $_POST vars

    $data['post'] = $post;
    $post->url = trim($post->url);
    
    // Strip 'http://' from URL.
    $url = parse_url($post->url);
    $host = $url['host'];
    if (!$host && isset($url['path'])) {
        $host = $url['path'];
    }
    $port = isset($url['port']) ? $url['port'] : '80';

    // 'Connect to' message copied from web-sniffer.net :-)
    $data['conn_msg'] = "Connect to " . gethostbyname($host) . " on port $port ... ";

    // The same User-Agent list from web-sniffer.net.
    $ua_list = array('Web-Sniff v1.33.7',
                     'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)',
                     'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)',
                     'Mozilla/5.0 (Windows; U; Windows NT 5.1; de; rv:1.9) Gecko/2008052906 Firefox/3.0',
                     'Mozilla/5.0 (Macintosh; U; Intel Mac OS X; de-de) AppleWebKit/523.10.3 (KHTML, like Gecko) Version/3.0.4 Safari/523.10',
                     'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4; de-de) AppleWebKit/533.16 (KHTML, like Gecko) Version/5.0 Safari/533.16',
                     'Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1A498b Safari/419.3',
                     'Mozilla/4.8 [en] (Windows NT 6.0; U)',
                     'Opera/9.20 (Windows NT 6.0; U; en)',
                     'Googlebot/2.1 (+http://www.googlebot.com/bot.html)',
                     '',
    );

    $ua_index = intval($post->ua);
    // User Agent not in the list? Use the first user agent.
    if (!isset($ua_list[$ua_index])) {
        $ua_index = 0;
    }

    // Set the option tag 'selected' attribute.
    $ua = array_fill(0, count($ua_list), '');
    $ua[$ua_index] = 'selected ';

    $data['ua'] = $ua;      // Check index_view on how this var is used.

    $ch = curl_init($post->url);

    $curlopts = array(
        CURLINFO_HEADER_OUT    => true,     // Request header
        CURLOPT_HEADER         => true,     // Response header
        CURLOPT_RETURNTRANSFER => true,     //
        CURLOPT_MAXREDIRS      => 1,
        CURLOPT_CONNECTTIMEOUT => 6,        // TCP  timeout
        CURLOPT_TIMEOUT        => 12,       // CURL timeout
        CURLOPT_USERAGENT      => $ua_list[$ua_index],

    );
    curl_setopt_array($ch, $curlopts);

    if ($post->http == '1.0') {
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    }

    if ($post->type == 'head') {
        curl_setopt($ch, CURLOPT_NOBODY, true);
    }
    
    // Accept-Encoding: gzip
    if ($post->gzip) {
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
    }

    if ($post->redir) {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    }

    // Set additional headers here.
    $misc_headers = array('Cache-Control: no-cache',
                          //"Referer: http://{$_SERVER['HTTP_HOST']}",
    );

    curl_setopt($ch, CURLOPT_HTTPHEADER, $misc_headers);

    $response  = curl_exec($ch);

    $errno = curl_errno($ch);
    if ($errno) {
        $data['conn_msg'] .= ' failed';
        $data['err_msg'] = "Error $errno: " . curl_error($ch);
        $data['error'] = true;

        Flight::render('index_view', $data);
        return;
    }

    $ci   = curl_getinfo($ch);

    // Show new lines.
    $crlf    = "<span class='crlf'>[CRLF]</span>" ;
    $lf      = "<span class='crlf'>[LF]</span>" ;
    $search  = array(CRLF, LF, '[CRLF]');
    $replace = array($crlf, $lf, "[CRLF]\r\n");

    $request_headers = str_replace($search, $replace ,$ci['request_header']);
    $data['request_headers'] = $request_headers;

    list($header, $body) = explode(CRLF . CRLF, $response, 2);

    // Get the new response header when a page is redirected.
    if (intval($ci['redirect_count'] > 0)) {
        list($header, $body) = explode(CRLF . CRLF, $body, 2);
        $post->url =  $ci['url']; // New URL
    }

    // Content-Length is not returned when chunked. This is also the
    // size of the gunzip'd page.
    $data['content_length'] = formatBytes(strlen($body));

    $headers = explode(CRLF, $header);

    // HTTP status line.
    $data['response_status'] = array_shift($headers);

    // Convert header string to an array. It is easier to iterate
    // and insert them in the table.
    $response_headers = array();
    foreach($headers as $header) {
        list($name, $value) = explode(': ', $header);
        $response_headers[$name] = $value;
    }
    $data['response_headers'] = $response_headers;

    if ($post->raw) {

        $body = escape($body);
        // Show new lines.
        $search  = array(CRLF, LF, '[CRLF]', '[LF]');
        $replace = array($crlf, $lf, "[CRLF]\r\n", "[LF]\n");
        $body    = str_replace($search, $replace, $body);

    } else {
        // Highligher (from php.net comment section) is buggy. It will
        // occasionally truncate text.
        $html = new HTMLcolorizer($body);
        $body = $html->colorize();
    }

    $data['conn_msg'] .= 'ok';
    $data['response_body'] = $body;

    curl_close($ch);

    Flight::render('index_view', $data);

});

function escape($string)
{
    return htmlentities($string, ENT_QUOTES, 'UTF-8');
}

Flight::start();