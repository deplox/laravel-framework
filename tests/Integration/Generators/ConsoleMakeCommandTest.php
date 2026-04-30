<?php

namespace Illuminate\Tests\Integration\Generators;

class ConsoleMakeCommandTest extends TestCase
{
    protected $files = [
        'app/Commands/FooCommand.php',
    ];

    public function testItCanGenerateConsoleFile()
    {
        $this->artisan('make:command', ['name' => 'FooCommand'])
            ->assertExitCode(0);

        $this->assertFileContains([
            'namespace App\Commands;',
            'use Illuminate\Console\Attributes\Description;',
            'use Illuminate\Console\Attributes\Signature;',
            'use Illuminate\Console\Command;',
            "#[Signature('app:foo-command')]",
            "#[Description('Command description')]",
            'class FooCommand extends Command',
        ], 'app/Commands/FooCommand.php');
    }

    public function testItCanGenerateConsoleFileWithCommandOption()
    {
        $this->artisan('make:command', ['name' => 'FooCommand', '--command' => 'foo:bar'])
            ->assertExitCode(0);

        $this->assertFileContains([
            'namespace App\Commands;',
            'use Illuminate\Console\Attributes\Description;',
            'use Illuminate\Console\Attributes\Signature;',
            'use Illuminate\Console\Command;',
            "#[Signature('foo:bar')]",
            "#[Description('Command description')]",
            'class FooCommand extends Command',
        ], 'app/Commands/FooCommand.php');
    }
}
