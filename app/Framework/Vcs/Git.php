<?php

namespace App\Framework\Vcs;


trait Git
{
    /**
     * @param $dir
     * @param $branch
     * @return $this
     */
    protected function checkout($dir, $branch)
    {
        system("cd \"{$dir}\" && git checkout {$branch}");
        return $this;
    }

    /**
     * @param $repo
     * @param $path
     * @return $this
     */
    protected function clone($repo, $path)
    {
        system("git clone \"{$repo}\" \"{$path}\"");
        return $this;
    }

    /**
     * @param $dir
     * @return $this
     */
    protected function add($dir)
    {
        system("cd \"{$dir}\" && git add .");
        return $this;
    }

    /**
     * @param $dir
     * @return $this
     */
    protected function pull($dir)
    {
        system("cd \"{$dir}\" && git pull");
        return $this;
    }

    /**
     * @param $dir
     * @param null $comment
     * @return $this
     */
    protected function commit($dir, $comment=null)
    {
        $comment = $comment ?: date("Y-m-d H:i:s");
        system("cd \"{$dir}\" && git commit -a -m \"{$comment}\"");
        return $this;
    }

    /**
     * @param $dir
     * @param null $branch
     * @return $this
     */
    protected function push($dir, $branch=null)
    {
        system("cd \"{$dir}\" && git push --tags {$branch}");
        return $this;
    }

    /**
     * @param $dir
     * @param string $commit
     * @return $this
     */
    protected function reset($dir, $commit="HEAD")
    {
        system("cd \"{$dir}\" && git reset --hard {$commit}");
        return $this;
    }

    /**
     * @param $dir
     * @param $tag
     * @param null $comment
     * @return $this
     */
    protected function tag($dir, $tag, $comment=null)
    {
        $comment = $comment ?: $tag;
        system("cd \"{$dir}\" && git tag -a {$tag} -m \"{$comment}\"");
        return $this;
    }

    /**
     * @param $dir
     * @param $from
     * @param $to
     * @param bool $pullBefore
     * @param bool $andPushAfter
     * @return $this
     */
    protected function merge($dir, $from, $to, $pullBefore=false, $andPushAfter=false)
    {
        $this->checkout($dir, $to);
        if ($pullBefore) {
            $this->pull($dir);
        }

        system("cd \"{$dir}\" && git merge {$from}");

        if ($andPushAfter) {
            $this->push($dir);
        }
        return $this;
    }
}