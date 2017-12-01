<?php

namespace App\Docker\Commands;

use stdClass;
use Spatie\MediaLibrary\Media;
use Docker\API\Model\ContainerConfig;
use Docker\API\Model\HostConfig;
use Docker\Docker;


class DiscoverJarEntrypoints
{
    /** @var Model */
    private $jar;

    /** @var string */
    private $containerId;

    public function __construct(Media $jar)
    {
        $this->jar = $jar;
    }

    private function getShareDirectory()
    {
        return collect(explode('/', $this->jar->getPath()))
                ->slice(0, -1)
                ->implode('/');
    }

    private function getJarFileName()
    {
        return collect(explode('/', $this->jar->getPath()))
                ->last();
    }

    private function getDockerConfig()
    {
        $filename = $this->getJarFileName();
        $share_dir = $this->getShareDirectory();

        $config = new ContainerConfig();
        $config->setImage('kylestev/jar-inspector:latest');
        $config->setCmd([sprintf('/jars/%s', $filename)]);
        $config->setVolumes([$share_dir => (object)[]]);

        $hostConfig = new HostConfig();
        $hostConfig->setBinds([
            sprintf('%s:/jars', $share_dir),
        ]);

        $config->setHostConfig($hostConfig);
        $config->setAttachStdout(true);
        $config->setAttachStderr(true);

        return $config;
    }

    private function setup()
    {
        $docker = new Docker();
        $manager = $docker->getContainerManager();

        $result = $manager->create($this->getDockerConfig());
        $this->containerId = $result->getId();
        $stream = $manager->attach($this->containerId, [
            'stream' => true,
            'stdout' => true,
            'stderr' => true,
        ]);

        $manager->start($this->containerId);

        return $stream;
    }

    public function run()
    {
        $result = new stdClass();
        $result->success = false;
        $result->response = null;
        $result->error = null;
        $stream = $this->setup();

        $stream->onStdout(function ($line) use (&$result) {
            try {
                $result->response = json_decode($line, true);
                $this->jar->setCustomProperty('entrypoints', $result->response);
                $this->jar->save();
                $result->success = true;
            } catch (Exception $e) {
                $result->error = $e;
            }
        });

        $stream->onStderr(function ($line) use (&$result) {
            $this->error($line);
            $this->error = new \Exception('STDERR: '.rtrim($line));
        });

        $stream->wait();

        return $result;
    }
}
