<?php

use Illuminate\Support\Facades\Artisan;
use Docker\Docker;
use Docker\API\Model\ContainerConfig;
use App\Jar;
use Docker\API\Model\HostConfig;
use App\Docker\Commands\DiscoverJarEntrypoints;

Artisan::command('entry-points {artifact}', function () {
    $jar = Jar::with('media')
            ->find($this->argument('artifact'))
            ->media
            ->first();

    dd((new DiscoverJarEntrypoints($jar))->run());
});
