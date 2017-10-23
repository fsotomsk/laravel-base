<?php

namespace CDeep\Console\Commands\Ssl;


use CDeep\Console\Commands\Command;

class LetsEncrypt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ssl:letsencrypt {--check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ssl certificate issue and renew';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $sslBasePath    = storage_path('etc/ssl');
        if(!is_dir($sslBasePath)) {
            mkdir($sslBasePath, 0755, true);
        }

        $config         = config('ssl');

        $accountKey     = $sslBasePath . '/account.pem';
        $certificate    = $sslBasePath . '/certificate.crt';
        $certificateKey = $sslBasePath . '/certificate.key';
        $exists         = file_exists($accountKey);

        $le = new \CDeep\Helpers\Ssl\LetsEncrypt($accountKey, $this);
        if (!$exists) {
            $le->registerAccount($config['email']);
        }

        $mustIssue = (function() use ($le, $certificate) {
            if (!file_exists($certificate)) {
                return true;
            }
            $info = $le->getCertFileInfo($certificate);
            return ($info['validTo_time_t'] && $info['validTo_time_t'] > (time() - 60*60*24*30));
        });

        if ($mustIssue()) {
            $domains = $config['domains'];
            if ($domains) {
                $domainsWithPath = [];
                foreach ($domains as $d) {
                    $domainsWithPath[$d] = base_path('public') . '/';
                }
                $le->getCertificate($certificateKey, $domainsWithPath, [
                    'fullchain' => $sslBasePath . '/fullchain.pem',
                    'cert'      => $certificate,
                    'csr'       => $sslBasePath . '/certificate.csr',
                ]);
            }
        }
        return 0;
    }
}
