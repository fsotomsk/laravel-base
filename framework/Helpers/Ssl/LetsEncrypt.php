<?php

namespace CDeep\Helpers\Ssl;


use Illuminate\Console\Command;

/**
 * Class LetsEncrypt
 * @package App\Tools
 */
class LetsEncrypt
{
    private
        $directory  = 'https://acme-v01.api.letsencrypt.org/directory', // live 'https://acme-staging.api.letsencrypt.org/directory', // staging
        $resources  = null,
        $nonce      = '',
        $tmp        = '/tmp',
        $header,  // JOSE Header
        $accountKey,
        $logger;

    protected
        $thumbprint,
        $acmePath  = '.well-known/acme-challenge';

    /**
     * LetsEncrypt constructor.
     * @param null $accountKeyPem
     * @param Command|null $logger
     */
    public function __construct($accountKeyPem=null, Command $logger=null)
    {
        
        if ($logger) {
            $this->logger = $logger;
        }
        
        if (!extension_loaded('openssl')) {
            $this->error('PHP OpenSSL Extension is required but not installed/loaded !');
        }

        $details = $this->getAccountKey($accountKeyPem, 2048);

        // JOSE Header - RFC7515
        $this->header = [
            'alg' => 'RS256',
            'jwk' => [ // JSON Web Key
                'e'     => $this->base64url($details['rsa']['e']), // public exponent
                'kty'   => 'RSA',
                'n'     => $this->base64url($details['rsa']['n']) // public modulus
            ]
        ];

        // JSON Web Key (JWK) Thumbprint - RFC7638
        $this->thumbprint = $this->base64url(
            hash(
                'sha256',
                json_encode($this->header['jwk']),
                true
            )
        );
    }

    /**
     * @param $message
     * @param string $context
     */
    private function info($message, $context='')
    {
        if ($this->logger) {
            $this->logger->info($message . ' ' . print_r($context, true));
        }
    }

    /**
     * @param $message
     * @param string $context
     * @throws \Exception
     */
    private function error($message, $context='')
    {
        if ($this->logger) {
            $this->logger->error($message . ' ' . print_r($context, true));
        }
        throw new \Exception($message);
    }

    /**
     *
     */
    public function __destruct()
    {
        if ($this->accountKey) {
            openssl_pkey_free($this->accountKey);
        }
    }

    /**
     *
     */
    private function init()
    {
        $ret = $this->httpRequest($this->directory); // Read ACME Directory
        $this->resources = $ret['body']; // store resources for later use
        $this->nonce = $ret['headers']['replay-nonce']; // capture first replay-nonce
    }

    // Encapsulate $payload into JSON Web Signature (JWS) - RFC7515

    /**
     * @param $payload
     * @return array
     */
    private function jwsEncapsulate($payload)
    {
        $protected = $this->header;
        $protected['nonce'] = $this->nonce; // replay-nonce

        $protected64 = $this->base64url(json_encode($protected));
        $payload64   = $this->base64url(json_encode($payload));
        $signature   = null;

        if (false === openssl_sign(
                $protected64.'.'.$payload64,
                $signature,
                $this->accountKey,
                OPENSSL_ALGO_SHA256
            )){
            
            $this->error(
                'Failed to sign payload !'."\n".
                openssl_error_string()
            );
        }

        return [
            'header'    => $this->header,
            'protected' => $protected64,
            'payload'   => $payload64,
            'signature' => $this->base64url($signature)
        ];
    }

    // RFC7515 - Appendix C

    /**
     * @param $data
     * @return string
     */
    final protected function base64url($data)
    {
        return rtrim(strtr(base64_encode($data),'+/','-_'),'=');
    }

    /**
     * @param $type
     * @param array $payload
     * @param null $url
     * @param bool $raw
     * @param null $accept
     * @return array
     */
    final protected function request($type, $payload=[], $url=null, $raw=false, $accept=null)
    {
        if ($this->resources === null){
            $this->init(); // read AMCE directory and get first replay-nonce
        }

        $data=json_encode(
            $this->jwsEncapsulate(
                array_merge(
                    $payload,
                    ['resource' => $type]
                )
            )
        );

        $ret=$this->httpRequest($url===null ? $this->resources[$type] : $url, $data, $raw, $accept);

        $this->nonce = $ret['headers']['replay-nonce']; // capture replay-nonce
        return $ret;
    }

    /**
     * @param $url
     * @param null $data
     * @param bool $raw
     * @param null $accept
     * @return array
     */
    final protected function httpRequest($url, $data=null, $raw=false, $accept=null)
    {
        $ctx = stream_context_create(
            [
                'http' => [
                    'header'        => $data===null ? '' : 'Content-Type: application/json',
                    'method'        => $data===null ? 'GET' : 'POST',
                    'user_agent'    => pathinfo(__FILE__, PATHINFO_FILENAME),
                    'ignore_errors' => true,
                    'timeout'       => 60,
                    'content'       => $data
                ]
            ]
        );

        $body = @file_get_contents($url, false, $ctx);
        if ($body === false){
            $this->error('Request error: ' . $url);
        }

        list(, $code, $status) = explode(' ', reset($http_response_header),3);
        $headers = array_reduce( // parse http headers into array
            array_slice($http_response_header,1),
            function($carry, $item){
                list($k,$v) = explode(':',$item,2);

                $k = strtolower(trim($k));
                $v = trim($v);

                if ($k==='link'){ // parse Link Headers
                    if (preg_match("/<(.*)>\\s*;\\s*rel=\"(.*)\"/", $v, $matches)){
                        $carry['link'][$matches[2]] = $matches[1];
                    }
                }else{
                    $carry[$k] = $v;
                }

                return $carry;
            },
            []
        );

        if (!$raw) {
            $json = $body ? json_decode($body,true) : '';
        }else{
            $json = null;
        }

        if (is_array($json)){
            if ($accept != '409' && isset($json['detail'])) {
                $this->error($json['detail']);
            }
            if (isset($json['error']) && is_array($json['error']) && isset($json['error']['detail'])) {
                $this->error($json['error']['detail']);
            }
        }

        if ( ($code != $accept) && ($code[0] != '2') ){
            $this->error('Request failed: ' . $code . ' [' . $status . ']: ' . $url);
        }

        if (!$raw) {
            if ($json === null) {
                $this->error('json_decode failed: '.print_r($headers,true) . $body);
            } else {
                $body = $json;
            }
        }

        return [
            'code'      => $code,
            'status'    => $status,
            'headers'   => $headers,
            'body'      => $body
        ];
    }

    /**
     * @param $documentRoot
     * @param $challenge
     */
    final protected function writeChallenge($documentRoot, $challenge)
    {
        if (!is_dir($documentRoot)){
            $this->error('DocRoot does not exist: ' . $documentRoot);
        }

        $acmePath = "{$documentRoot}/{$this->acmePath}";

        @mkdir($acmePath,0755,true);

        if (!is_dir($acmePath)){
            $this->error("Failed to create acme challenge directory: {$acmePath}");
        }

        $keyAuthorization = $challenge['token'] . '.' . $this->thumbprint;

        if (false === @file_put_contents("{$acmePath}/{$challenge['token']}", $keyAuthorization)){
            $this->error("Failed to create challenge file: {$acmePath}/{$challenge['token']}");
        }

        file_put_contents("{$acmePath}/.htaccess", 'RewriteEngine Off' . PHP_EOL . 'Allow from all' . PHP_EOL);

        $this->info("writeChallenge: {$acmePath}/{$challenge['token']}");
    }

    /**
     * @param $documentRoot
     * @param $challenge
     */
    final protected function removeChallenge($documentRoot, $challenge)
    {
        unlink("{$documentRoot}/{$this->acmePath}/{$challenge['token']}");
    }

    /**
     * @param $pem
     * @return bool|string
     */
    final protected function pem2der($pem)
    {
        return base64_decode(
            implode(
                '',
                array_slice(
                    array_map('trim',explode("\n",trim($pem))),
                    1,
                    -1
                )
            )
        );
    }

    /**
     * @param $der
     * @return string
     */
    final protected function der2pem($der)
    {
        return "-----BEGIN CERTIFICATE-----\n".
            chunk_split(base64_encode($der),64,"\n").
            "-----END CERTIFICATE-----\n";
    }

    /**
     * @param $domainKeyPem
     * @param $domains
     * @return mixed
     */
    final protected function generateCsr($domainKeyPem, $domains)
    {
        if (!file_exists($domainKeyPem)) {
            $this->info("Key file is not exists {$domainKeyPem}");
            $domainKeyPem = $this->generateRsaFile($domainKeyPem, 2048);
        }

        if (false === ($domainKey = openssl_pkey_get_private('file://' . $domainKeyPem))){
            $this->error(
                "Could not load domain key: {$domainKeyPem}\n" .
                openssl_error_string()
            );
        }

        if (false === ($fn=tempnam(storage_path("/tmp"), "CNF_"))){
            $this->error('Failed to create temp file !');
        }

        if (false === @file_put_contents($fn,
                'HOME = .'."\n".
                'RANDFILE=$ENV::HOME/.rnd'."\n".
                '[req]'."\n".
                'distinguished_name=req_distinguished_name'."\n".
                '[req_distinguished_name]'."\n".
                '[v3_req]'."\n".
                '[v3_ca]'."\n".
                '[SAN]'."\n".
                'subjectAltName='.
                implode(',',array_map(function($domain){
                    return 'DNS:' . $domain;
                }, $domains)).
                "\n"
            )){
            $this->error('Failed to write tmp file: ' . $fn);
        }

        $dn = ['commonName' => reset($domains)];

        $csr = openssl_csr_new($dn, $domainKey, [
            'config'            => $fn,
            'req_extensions'    => 'SAN',
            'digest_alg'        => 'sha512'
        ]);
        unlink($fn);
        openssl_pkey_free($domainKey);

        if (!$csr) {
            $this->error(
                'Could not generate CSR !'."\n".
                openssl_error_string()
            );
        }

        if (false === openssl_csr_export($csr, $out)){
            $this->error(
                'Could not export CSR !'."\n".
                openssl_error_string()
            );
        }

        return $out;
    }

    /**/

    /**
     * @param null $email
     * @return array
     */
    public function registerAccount($email=null)
    {
        $data = $email
            ? ['contact' => ['mailto:' . $email]]
            : [];

        $ret = $this->request('new-reg',$data,null,false,409);
        $reg = [];

        switch($ret['code']){
            case 409: // account already registered
                $reg = $ret['headers']['location'];
                $ret = $this->request('reg', $data, $reg);
                break;
            case 201: // account created
                $reg = $ret['headers']['location'];
                break;
            default:
                $this->error('register error: ' . $ret['body']['detail']);
                break;
        }

        if ( !isset($ret['body']['agreement']) ){
            $data['agreement'] = $ret['headers']['link']['terms-of-service'];
            $ret['confirmed']  = $this->request('reg', $data, $reg);
        }

        $this->info('Account: ', $ret);
        return $ret;
    }

    /**
     * @param $domains
     * @return array
     */
    private function simulateChallenges($domains){
        $okDomains = [];
        foreach($domains as $domain => $documentRoot){
            $token     = str_random(20);
            $challenge = ['token' => $token];
            $this->writeChallenge($documentRoot, $challenge);
            try {
                $ret = $this->httpRequest("http://{$domain}/{$this->acmePath}/{$challenge['token']}", null, true);
                if ($ret['body'] == "{$token}.{$this->thumbprint}"){
                    $okDomains[$domain] = $documentRoot;
                    $this->info("Domain simulate success: {$domain} / {$documentRoot}");
                }
                usleep(500000);
            } catch(\Exception $e) {
                $this->info("Domain simulate fail: {$domain} / http://{$domain}/{$this->acmePath}/{$challenge['token']}");
            } finally {
                $this->removeChallenge($documentRoot, $challenge);
            }
        }
        return $okDomains;
    }

    /**
     * @param $opts
     */
    private function checkOutputWritable($opts){
        foreach($opts as $type => $fn){
            if (!is_writable(file_exists($fn) ? $fn : dirname($fn))) {
                $this->error("Output file is not writable ({$type}): {$fn}");
            }
        }
    }

    /**
     * @param $domainKeyPem
     * @param $domains
     * @param $opts
     * @return array
     */
    public function getCertificate($domainKeyPem, $domains, $opts)
    {
        $this->checkOutputWritable($opts); // check if output files are writable
        $domains = $this->simulateChallenges($domains);

        if(sizeof($domains) == 0) {
            $this->error('No success simulated domains');
        }

        $this->info('Simulated domains: '. implode(', ', array_keys($domains)));

        $errors             = [];
        $validatedDomains   = [];
        foreach($domains as $domain => $documentRoot)
        {
            try {
                // Validating;
                $ret = $this->request('new-authz', [
                    'identifier' => [
                        'type'  => 'dns',
                        'value' => $domain
                    ]
                ]);

                if ($ret['code'] != 201) {
                    $this->error('Unexpected http status code: ' . $ret['code']);
                }

                // find http-01 in list of challenges
                $tmp = array_filter($ret['body']['challenges'], function($o){
                    return $o['type'] === 'http-01';
                });
                $challenge = reset($tmp);

                if (empty($challenge)) {
                    $this->error('http-01 challenge not found !');
                }

                $this->writeChallenge($documentRoot, $challenge);

                // notify ACME-Server that challenge file has been placed
                $ret = $this->request(
                    'challenge',
                    [
                        'keyAuthorization' => $challenge['token'] . '.' . $this->thumbprint
                    ],
                    $challenge['uri']
                );

                if ($ret['code'] != 202) { // HTTP: Accepted
                    $this->removeChallenge($documentRoot, $challenge);
                    $this->error('Unexpected http status code: ' . $ret['code']);
                }

                sleep(3); // waiting for ACME-Server to verify challenge

                // poll
                $tries=10;
                $delay=2;
                do {
                    $ret=$this->httpRequest($challenge['uri']);
                    if ($ret['body']['status'] === 'valid'){
                        $validatedDomains[$domain] = $documentRoot;
                        $this->info('Domain validated: ' . $domain);
                        break;
                    }
                    sleep($delay); // still waiting..
                    $delay = min($delay*2,32);
                    if (--$tries == 0) {
                        $this->removeChallenge($documentRoot,$challenge);
                        $this->error('Failed to verify challenge after 10 tries !');
                    }
                } while($ret['body']['status'] === 'pending');

                $this->removeChallenge($documentRoot, $challenge);

                if ($ret['body']['status'] !== 'valid') {
                    $this->error('Challenge failed for ' . $domain);
                }
            } catch (\Exception $e) {
                $errors[] = $e;
            }
        }

        if(sizeof($validatedDomains) == 0) {
            $this->error('No validated domains');
        }

        $this->info('Generating Certificate Signing Request (CSR)...');
        $csr = $this->generateCsr($domainKeyPem, array_keys($validatedDomains));

        // Requesting Certificate...
        $ret = $this->request('new-cert', [
            'csr' => $this->base64url($this->pem2der($csr))
        ],null,true);

        if ($ret['code'] != 201) { // HTTP: Created
            $this->error('unexpected http status code: ' . $ret['code']);
        }

        if ($ret['headers']['content-type'] != 'application/pkix-cert') {
            $this->error('Unexpected content-type: ' . $ret['headers']['content-type']);
        }

        $cert = $this->der2pem($ret['body']);

        if (isset($opts['chain']) || isset($opts['fullchain'])) {

            $this->info('Get chains');
            $ret = $this->httpRequest($ret['headers']['link']['up'], null, true);

            if ($ret['code'] != 200){
                $this->error('Unexpected http status code: ' . $ret['code']);
            }

            if ($ret['headers']['content-type'] != 'application/pkix-cert') {
                $this->error('Unexpected content-type: ' . $ret['headers']['content-type']);
            }

            $intermediate = $this->der2pem($ret['body']);
            if (isset($opts['chain'])){
                if (false === @file_put_contents($opts['chain'], $intermediate)){
                    $this->error('Failed to write chain to: ' . $opts['chain']);
                }
            }
            if (isset($opts['fullchain'])) {
                if (false === @file_put_contents($opts['fullchain'],$cert . $intermediate)){
                    $this->error('Failed to write fullchain to: ' . $opts['fullchain']);
                }
            }
        }

        if (isset($opts['cert'])){
            if (false === @file_put_contents($opts['cert'], $cert)){
                $this->error('Failed to write cert to: ' . $opts['cert']);
            }
        }

        if (isset($opts['csr'])){
            if (false === @file_put_contents($opts['csr'], $csr)) {
                $this->error('Failed to write csr to: ' . $opts['csr']);
            }
        }

        return [
            'certInfo'          => $this->getCertInfo($cert),
            'validatedDomains'  => array_keys($validatedDomains),
        ];
    }

    /**
     * @param $cert
     * @return array
     */
    public function getCertInfo($cert)
    {
        return openssl_x509_parse(openssl_x509_read($cert),true);
    }

    /**
     * @param $file
     * @return array
     */
    public function getCertFileInfo($file)
    {
        if (!file_exists($file)) {
            $this->error('File not exists' . $file);
        }
        return $this->getCertInfo(file_get_contents($file));
    }

    /**
     * @param $pem
     * @return bool
     */
    public function revokeCertificate($pem)
    {
        if (false === ($data=@file_get_contents($pem))){
            $this->error('Failed to open cert: ' . $pem);
        }

        if (false === ($x509=@openssl_x509_read($data))){
            $this->error('Failed to parse cert: ' . $pem . "\n" . openssl_error_string());
        }

        if (false === (@openssl_x509_export($x509, $cert))){
            $this->error('Failed to parse cert: ' . $pem . "\n" . openssl_error_string());
        }

        $cert = $this->base64url($this->pem2der($cert));
        $ret = $this->request('revoke-cert', ['certificate' => $cert]);

        return ($ret['code'] == 200);
    }

    /**
     * @param $accountKeyPem
     * @param int $bits
     * @return array
     */
    public function getAccountKey($accountKeyPem, $bits=2048)
    {
        if (!$this->accountKey) {
            // Generate new if not exists
            if (!file_exists($accountKeyPem)) {
                $accountKeyPem = $this->generateRsaFile($accountKeyPem, $bits);
            }
            // load account key
            if (false === ($this->accountKey = openssl_pkey_get_private('file://' . $accountKeyPem))){
                $this->error(
                    'Could not load account key: ' . $accountKeyPem . "\n".
                    openssl_error_string()
                );
            }
        }

        // get account key details
        if (false === ($details=openssl_pkey_get_details($this->accountKey))){
            $this->error(
                'Could not get account key details: ' . $accountKeyPem . "\n".
                openssl_error_string()
            );
        }

        return $details;
    }

    /**
     * @param $file
     * @param int $bits
     * @return mixed
     */
    private function generateRsaFile($file, $bits = 2048)
    {
        $dir = dirname($file);
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            $this->error('Failed to create dir ' . $dir);
        }
        if (false === @file_put_contents($file, $this->generateRsaKey($bits))){
            $this->error('Failed to write rsa file: ' . $file);
        }
        return $file;
    }

    /**
     * @param int $bits
     * @return mixed
     */
    private function generateRsaKey($bits = 2048)
    {
        if (false === ($fn = tempnam($this->tmp, "CNF_"))){
            $this->error('Failed to create temp file !');
        }

        if (false === @file_put_contents($fn,
                'HOME = .' . "\n" .
                'RANDFILE=$ENV::HOME/.rnd' . "\n" .
                '[v3_ca]' . "\n"
            )){
            $this->error('Failed to write tmp file: ' . $fn);
        }

        $key = openssl_pkey_new([
            'config'           => $fn,
            'private_key_bits' => $bits,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        openssl_pkey_export($key, $pem);
        unlink($fn);

        return $pem;
    }
}