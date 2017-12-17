<?php

namespace App\Docker\Commands;

use stdClass;
use Spatie\MediaLibrary\Media;
use Docker\API\Model\ContainerConfig;
use Docker\API\Model\HostConfig;
use Docker\Docker;

class DiscoverJarEntrypoints extends Command
{
    /** @var Model */
    private $jar;

    /** @var string */
    private $containerId;

    public function __construct(Docker $docker, Media $jar)
    {
        parent::__construct($docker);
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

    protected function config() : ContainerConfig
    {
        $config = new ContainerConfig();

        $filename = $this->getJarFileName();
        $share_dir = $this->getShareDirectory();

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

    public function onStdout(string $line)
    {
        var_dump('[normal line]', $line);
    }

    public function onStderr(string $line)
    {
        var_dump('[error]', $line);
    }
}
