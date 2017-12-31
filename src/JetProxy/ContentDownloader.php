<?php

namespace JetProxy;

use SplFileObject;

class ContentDownloader
{
    /**
     * @var \SplFileObject
     */
    protected $file;

    /**
     * @var string
     */
    protected $oriPath;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var bool
     */
    protected $isFinished = false;

    /**
     * @var resource
     */
    protected $hashContext;

    /**
     * @var string|null
     */
    protected $hash;

    /**
     * @var bool
     */
    protected $autoDeletion;

    /**
     * ContentDownloder constructor.
     * @param \SplFileObject  $file
     * @param string          $hashMethod
     * @param bool            $autoDeletion
     */
    public function __construct(SplFileObject $file, $hashMethod = 'md5', $autoDeletion = true)
    {
        $this->file         = $file;
        $this->oriPath      = $this->path = $file->getRealPath();
        $this->hashContext  = hash_init($hashMethod);
        $this->autoDeletion = $autoDeletion;
    }

    /**
     * @param  string  $content
     * @return $this
     */
    public function write($content)
    {
        $this->file->fwrite($content);
        hash_update($this->hashContext, $content);
        return $this;
    }

    /**
     * @return string
     */
    public function path()
    {
        return $this->path;
    }

    /**
     * @return string|null
     */
    public function hash()
    {
        return $this->hash;
    }

    /**
     * @return $this
     */
    public function finish()
    {
        if (! $this->isFinished) {
            $this->isFinished = true;
            unset($this->file);
            $this->hash = hash_final($this->hashContext);
        }
        return $this;
    }

    /**
     * @param  string  $path
     * @param  bool    $override
     * @return bool
     */
    public function saveTo($path, $override = false)
    {
        $this->finish();
        if (file_exists($path)) {
            if ($override) {
                unlink($path);
            } else {
                return false;
            }
        }

        rename($this->path, $path);
        $this->path = $path;

        return true;
    }

    /**
     * @return string
     */
    public function content()
    {
        $this->finish();
        return file_get_contents($this->path);
    }

    public function __destruct()
    {
        if ($this->autoDeletion && file_exists($this->oriPath)) {
            unset($this->file);
            unlink($this->oriPath);
        }
    }
}