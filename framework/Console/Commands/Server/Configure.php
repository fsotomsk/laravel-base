<?php

namespace CDeep\Console\Commands\Server;


use CDeep\Console\Commands\Command;
use CDeep\Helpers\FileSystem\FileSystem;
use Illuminate\Contracts\View\Factory as ViewFactory;

class Configure extends Command
{

    use FileSystem;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:configure {--path=?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure http server';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $domains = config('app.domains');
        $email   = config('app.email');

        $params = [
            'SERVER_NAME'   => array_shift($domains),
            'ALIASES'       => $domains,
            'ADMIN_EMAIL'   => $email,
            'DOCUMENT_ROOT' => $this->option('path') ?: public_path(),
        ];

        $this->makeNginxConfig($params);
        $this->makeApacheConfig($params);

        return 0;
    }

    protected function makeNginxConfig($params)
    {
        $nginxConfig = "/etc/nginx/sites-enabled/{$params['SERVER_NAME']}.conf";
        $this->mkdir(dirname($nginxConfig));

        try {
            $this->info("Writing {$nginxConfig}");
            file_put_contents(
                $nginxConfig,
                $this->render('etc::nginx/vhost', $params)
            );
        } catch (\Exception $e) {
            $this->error("Can't write {$nginxConfig}");
        }
    }

    protected function makeApacheConfig($params)
    {
        $apacheConfig = "/etc/apache2/sites-enabled/{$params['SERVER_NAME']}.conf";
        $this->mkdir(dirname($apacheConfig));

        try {
            $this->info("Writing {$apacheConfig}");
            file_put_contents(
                $apacheConfig,
                $this->render('etc::apache2/vhost', $params)
            );
        } catch (\Exception $e) {
            $this->error("Can't write {$apacheConfig}");
        }
    }

    private function render($file, $data)
    {
        /**
         * @var ViewFactory $view
         */
        $view = app('view');
        $view->addNamespace('etc', base_path('etc'));

        return $view->make($file, $data)->render();
    }
}
