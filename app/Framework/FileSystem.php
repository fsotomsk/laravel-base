<?php

namespace App\Tools;


trait FileSystem
{
    /**
     * @param int $count
     * @return \Closure
     */
    protected function progress(int $count)
    {
        return method_exists($this, 'bar')
            ? $this->bar($count)
            : (function(){});
    }

    /**
     * @param string $message
     */
    protected function message($message)
    {
        if(method_exists($this, 'info')) {
            $this->info($message);
        }
    }

    /**
     * @param $dir
     * @param callable|null $callback
     * @return \RecursiveIteratorIterator
     */
    protected function getFilesIterator($dir, Callable $callback=null)
    {
        $dir = $this->sanitizePath($dir);
        $iterator = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $filter = new \RecursiveCallbackFilterIterator($iterator, function ($current) use ($dir, $callback) {
            $current->relativeName = substr($current->getPathname(), strlen($dir . DIRECTORY_SEPARATOR));
            return ($callback === null || $callback($current));
        });

        return new \RecursiveIteratorIterator($filter,\RecursiveIteratorIterator::CHILD_FIRST);
    }

    /**
     * @param $dir
     * @return mixed
     */
    protected function sanitizePath($dir)
    {
        return preg_replace('#([/\/])#', DIRECTORY_SEPARATOR, $dir);
    }

    /**
     * @param $src
     * @param $dst
     * @param callable|null $callback
     * @return $this
     */
    protected function copy($src, $dst, Callable $callback=null)
    {
        $src = $this->sanitizePath($src);
        $dst = $this->sanitizePath($dst);

        $this->message("Coping {$src} to {$dst}");
        $files = $this->getFilesIterator($src, $callback);

        $count = iterator_count($files);
        if ($count > 0) {
            $bar = $this->progress($count);

            foreach($files as $file) {
                if ($file->isDir()){
                    $this->mkdir("{$dst}/{$file->relativeName}");
                } else {
                    if(!file_exists("{$dst}/{$file->relativeName}")) {
                        $this->mkdir(dirname("{$dst}/{$file->relativeName}"));
                        copy($file->getRealPath(), "{$dst}/{$file->relativeName}");
                    }
                }
                $bar();
            }

            $bar(true);
        } else {
            $this->info("Nothing to copy");
        }

        return $this;
    }

    /**
     * @param $dir
     * @return $this
     */
    protected function mkdir($dir)
    {
        $dir = $this->sanitizePath($dir);
        if(!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $this;
    }

    /**
     * @param $dir
     * @param bool $keepSelf
     * @param array $exclude
     * @return $this
     */
    protected function rm($dir, $keepSelf=false, $exclude=[])
    {
        if (is_dir($dir)) {
            return $this->rmdir($dir, $keepSelf, $exclude);
        } elseif (is_file($dir)) {
            unlink($dir);
        }
        return $this;
    }

    /**
     * @param $dir
     * @param bool $keepSelf - удалить только содержимое
     * @param array $exclude - исключения
     * @return $this
     */
    protected function rmdir($dir, $keepSelf=false, $exclude=[])
    {
        $dir = $this->sanitizePath($dir);
        if (is_dir($dir)) {

            $this->message("Removing {$dir}");
            $files = $this->getFilesIterator($dir);

            $count = iterator_count($files);
            if ($count > 0) {
                $bar = $this->progress($count);

                $match = function($file) use ($exclude) {
                    if (!$exclude) {
                        return false;
                    }
                    foreach ($exclude as $item) {
                        if ($file->relativeName == $item
                            || strpos($file->relativeName, $item . DIRECTORY_SEPARATOR) === 0) {
                            return true;
                        }
                    }
                    return false;
                };

                foreach($files as $file) {
                    $bar();

                    if ($match($file)) {
                        continue;
                    }
                    if ($file->isDir()){
                        chmod($file, 0755);
                        rmdir($file->getRealPath());
                    } else {
                        chmod($file, 0644);
                        unlink($file->getRealPath());
                    }
                }

                $bar(true);
            }

            if (!$keepSelf) {
                rmdir($dir);
            }

            $this->message("Removed.");
        }

        return $this;
    }

    /**
     * @param string $src
     * @param string $dst
     * @param callable|null $callback
     * @return $this
     */
    public function pack($src, $dst, Callable $callback=null)
    {
        $this->mkdir(pathinfo($dst, PATHINFO_DIRNAME));
        $this->message("Pack {$src}");

        $files = $this->getFilesIterator($src, $callback);
        $count = iterator_count($files);
        if ($count > 0) {
            $bar = $this->progress($count);

            $zip = new \ZipArchive();
            $zip->open($dst, \ZipArchive::CREATE);
            /**
             * @var \SplFileInfo $file
             */
            foreach ($files as $file) {
                if ($file->isFile()) {
                    $zip->addFile($file->getRealPath(), $file->relativeName);
                }
                $bar();
            }

            $zip->close();
            $bar(true);
        } else {
            $this->message("No files to pack");
        }

        return $this;
    }
}