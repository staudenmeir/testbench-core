<?php

namespace Orchestra\Testbench\Foundation\Console\Actions;

use Illuminate\Console\View\Components\Factory as ComponentsFactory;
use Illuminate\Filesystem\Filesystem;

class GeneratesFile extends Action
{
    /**
     * Construct a new action instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     * @param  \Illuminate\Console\View\Components\Factory|null  $components
     * @param  bool  $force
     * @param  string|null  $workingPath
     */
    public function __construct(
        public readonly Filesystem $filesystem,
        public readonly ?ComponentsFactory $components = null,
        public readonly bool $force = false,
        ?string $workingPath = null
    ) {
        $this->workingPath = $workingPath;
    }

    /**
     * Handle the action.
     *
     * @param  string  $from
     * @param  string  $to
     * @return void
     */
    public function handle(string $from, string $to): void
    {
        if (! $this->filesystem->exists($from)) {
            $this->components?->twoColumnDetail(
                sprintf('Source file [%s] doesn\'t exists', $this->pathLocation($from)),
                '<fg=yellow;options=bold>SKIPPED</>'
            );

            return;
        }

        if ($this->force || ! $this->filesystem->exists($to)) {
            $this->filesystem->copy($from, $to);

            $this->components?->task(
                sprintf('File [%s] generated', $this->pathLocation($to))
            );
        } else {
            $this->components?->twoColumnDetail(
                sprintf('File [%s] already exists', $this->pathLocation($to)),
                '<fg=yellow;options=bold>SKIPPED</>'
            );
        }
    }
}
