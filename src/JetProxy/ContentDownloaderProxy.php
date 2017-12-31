<?php

namespace JetProxy;

class ContentDownloaderProxy implements ClientInterface
{
    /**
     * @var \JetProxy\ClientInterface
     */
    protected $client;

    /**
     * @var string
     */
    protected $hashAlgo;

    /**
     * @var string
     */
    protected $tmpDir;

    /**
     * @var \JetProxy\ContentDownloader|null
     */
    protected $downloader = null;

    /**
     * ContentDownloaderProxy constructor.
     * @param \JetProxy\ClientInterface  $client
     * @param string                     $hashAlgo
     * @param string|null                $tmpDir
     */
    public function __construct(ClientInterface $client, $hashAlgo = 'md5', $tmpDir = null)
    {
        $this->client   = $client;
        $this->hashAlgo = $hashAlgo;
        $this->tmpDir   = $tmpDir ?: sys_get_temp_dir();
    }

    /**
     * @param  string  $method
     * @param  string  $uri
     * @return \JetProxy\Request
     */
    public function request($method, $uri)
    {
        $request = $this->client->request($method, $uri);
        $this->downloader = $this->createContentDownloader();

        $request->getHttpReceiver()->addBufferListener(function ($content) {
            $this->downloader->write($content);
        })->addEndListener(function () {
            $this->downloader->finish();
        });

        return $request;
    }

    /**
     * @return \JetProxy\ContentDownloader|null
     */
    public function downloader()
    {
        return $this->downloader;
    }

    /**
     * @return \JetProxy\ContentDownloader
     */
    protected function createContentDownloader()
    {
        $path = tempnam($this->tmpDir, 'STO');
        $file = new \SplFileObject($path, 'w');
        return new ContentDownloader($file, $this->hashAlgo);
    }
}