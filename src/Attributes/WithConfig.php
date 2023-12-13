<?php

namespace Orchestra\Testbench\Attributes;

use Attribute;
use Orchestra\Testbench\Contracts\Attributes\Invokable as InvokableContract;
use Orchestra\Testbench\Foundation\Env;
use Orchestra\Testbench\Foundation\UndefinedValue;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class WithConfig implements InvokableContract
{
    /**
     * The target configuration key.
     *
     * @var string
     */
    public $key;

    /**
     * The target configuration value.
     *
     * @var mixed
     */
    public $value;

    /**
     * Construct a new attribute.
     *
     * @param  string  $key
     * @param  mixed  $value
     */
    public function __construct(string $key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * Handle the attribute.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    public function __invoke($app): void
    {
        /** @var \Illuminate\Contracts\Config\Repository $config */
        $config = $app->make('config');

        $config->set($this->key, $this->value);
    }
}
