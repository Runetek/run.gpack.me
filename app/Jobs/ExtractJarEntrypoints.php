<?php

namespace App\Jobs;

use Docker\Docker;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Spatie\MediaLibrary\Media;
use App\Docker\Commands\DiscoverJarEntrypoints;

class ExtractJarEntrypoints implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Media */
    private $jar;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Media $jar)
    {
        $this->jar = $jar;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Docker $docker)
    {
        $command = new DiscoverJarEntrypoints($docker, $this->jar);

        $result = $command->run();

        if (!is_null($result->error)) {
            $this->fail($result->error);
        } else {
            $this->jar->setCustomProperty('entrypoints', $result->response);
            $this->jar->save();
        }
    }
}
