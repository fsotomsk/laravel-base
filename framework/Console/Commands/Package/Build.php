<?php

namespace CDeep\Console\Commands\Package;


use CDeep\Console\Commands\Command;
use CDeep\Helpers\FileSystem\FileSystem;
use CDeep\Helpers\Vcs\Git;

class Build extends Command
{

    use FileSystem;
    use Git;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Building a package for install via composer';

    protected $ignore = [
        '.idea',
        '.env',
        '.env.dev',
        '.env.testing',
        '.env.production',
        '.git',
        '.gitignore',
        '.gitattributes',
        '.gitlab-ci.yml',
        'composer.lock',
        'yarn.lock',
        'webpack.mix.js',
        'phpunit.xml',
        '/etc/deploy/dev.php',
        '/app/Console/Commands/Package',
        '/bootstrap/cache',
        '/tests',
        '/storage',
        '/vendor',
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $src = base_path();
        $dst = storage_path("app/public/build");

        $this
            ->package($src, $dst);

        return 0;
    }

    /**
     * @param string $src
     * @param string $dst
     * @return $this
     */
    protected function package($src, $dst)
    {
        $version = config('app.version');

        $ignore = array_map([$this, 'sanitizePath'], $this->ignore + [$dst, __FILE__]);
        $filter = function(\SplFileInfo $current) use ($ignore) {
            foreach ($ignore as $i) {
                if (
                    "#{$current->getFilename()}#" == "#{$i}#"                       ||
                    strpos("#{$this->sanitizePath("/{$current->relativeName}")}#",  $i) === 1 ||
                    strpos("#{$this->sanitizePath($current->getPathname())}#", $i) === 1
                ) {
                    return false;
                }
            }
            return true;
        };

        $dist = "{$dst}/dist";

        $this->rmdir($dist);

        $repo = "git@github.com:fsotomsk/laravel-base.git";
        $this->clone($repo, $dist);
        $this->rmdir($dist, true, ['.git']);
        $this->copy($src, $dist, $filter);

        $this->add($dist);
        $this->commit($dist, "v{$version}");
        $this->tag($dist, "v{$version}");
        $this->push($dist, '-u origin master');

        $pack = "{$dst}/laravel-base-v{$version}.zip";
        if (!file_exists($pack)) {

            $composerJsonFile = "{$dist}/composer.json";
            $composerPath = "cdeep/laravel-base";
            $composer = $this->loadJson($composerJsonFile);

            foreach ($composer['autoload'] as $schema => $paths) {
                foreach ($paths as $i=>$path) {
                    $composer['autoload'][$schema][$i] = "{$composerPath}/{$path}";
                }
            }

            foreach ($composer['autoload-dev'] as $schema => $paths) {
                foreach ($paths as $i=>$path) {
                    $composer['autoload-dev'][$schema][$i] = "{$composerPath}/{$path}";
                }
            }

            $this->saveJson($composerJsonFile, $composer);
            $this->pack($dist, $pack, $filter);
            $this->reset($dist);
        }

        return $this;
    }

    protected function loadJson($file)
    {
        return json_decode(file_get_contents($file), true);
    }

    protected function saveJson($file, $data)
    {
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT ^ JSON_UNESCAPED_UNICODE));
    }
}
