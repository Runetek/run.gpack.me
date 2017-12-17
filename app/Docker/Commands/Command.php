<?php

namespace App\Docker\Commands;

use Docker\Docker;
use Docker\Manager\ContainerManager;
use Docker\API\Model\ContainerConfig;

abstract class Command
{
    /** @var Docker */
    private $docker;

    /** @var string|null */
    private $containerId;

    public function __construct(Docker $docker)
    {
        $this->docker = $docker;
    }

    public function run()
    {
        $this->createContainer();
        $stream = $this->attachStreams();
        $this->startContainer();

        $stream->wait();
    }

    protected function manager() : ContainerManager
    {
        return $this->docker->getContainerManager();
    }

    protected abstract function config() : ContainerConfig;

    public function containerId()
    {
        return $this->containerId;
    }

    private function createContainer()
    {
        $manager = $this->manager();

        $config = $this->config();
        // $config->setNetworkDisabled(true);
        $config->setAttachStdin(true);
        $config->setAttachStdout(true);

        $result = $manager->create($config);
        $this->containerId = $result->getId();
    }

    private function startContainer()
    {
        $this->manager()->start($this->containerId());
    }

    private function attachStreams()
    {
        $stream = $this->manager()->attach($this->containerId(), [
            'stream' => true,
            'stdout' => true,
            'stderr' => true,
        ]);

        $stream->onStdout(function ($line) {
            if ($line) {
                $this->onStdout($line);
            }
        });

        $stream->onStderr(function ($line) {
            if ($line) {
                $this->onStderr($line);
            }
        });

        return $stream;
    }

    protected abstract function onStdout(string $line);
    protected abstract function onStderr(string $line);
}
