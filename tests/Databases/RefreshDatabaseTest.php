<?php

namespace Orchestra\Testbench\Tests\Databases;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\Attributes\DefineDatabase;
use Orchestra\Testbench\Attributes\ResetRefreshDatabaseState;
use Orchestra\Testbench\Attributes\WithConfig;
use Orchestra\Testbench\Concerns\WithLaravelMigrations;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;

#[ResetRefreshDatabaseState]
#[WithConfig('database.default', 'testing')]
class RefreshDatabaseTest extends TestCase
{
    use RefreshDatabase;
    use WithLaravelMigrations;
    use WithWorkbench;

    #[Test]
    public function it_runs_the_migrations()
    {
        $users = DB::table('testbench_users')->where('id', '=', 1)->first();

        $this->assertEquals('crynobone@gmail.com', $users->email);
        $this->assertTrue(Hash::check('123', $users->password));

        $this->assertEquals([
            'id',
            'email',
            'password',
            'created_at',
            'updated_at',
        ], Schema::getColumnListing('testbench_users'));
    }

    #[Test]
    #[DefineDatabase('addAdditionalTableAtRuntime')]
    public function it_can_modify_migrations_at_runtime()
    {
        $this->assertTrue(Schema::hasTable('testbench_users'));
        $this->assertTrue(Schema::hasTable('testbench_auths'));

        $this->assertEquals([
            'id',
            'email',
            'password',
            'created_at',
            'updated_at',
        ], Schema::getColumnListing('testbench_users'));

        $this->assertEquals([
            'id',
            'two_factor_secret',
        ], Schema::getColumnListing('testbench_auths'));
    }

    public function addAdditionalTableAtRuntime()
    {
        Schema::create('testbench_auths', function (Blueprint $table) {
            $table->id();
            $table->text('two_factor_secret')->nullable();
        });

        $this->beforeApplicationDestroyed(function () {
            Schema::drop('testbench_auths');
        });
    }
}
