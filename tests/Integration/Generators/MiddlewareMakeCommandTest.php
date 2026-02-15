<?php

namespace Illuminate\Tests\Integration\Generators;

class MiddlewareMakeCommandTest extends TestCase
{
    protected $files = [
        'app/Middleware/Foo.php',
        'tests/Feature/Middleware/FooTest.php',
    ];

    public function testItCanGenerateMiddlewareFile()
    {
        $this->artisan('make:middleware', ['name' => 'Foo'])
            ->assertExitCode(0);

        $this->assertFileContains([
            'namespace App\Middleware;',
            'use Closure;',
            'use Illuminate\Http\Request;',
            'class Foo',
            'public function handle(Request $request, Closure $next)',
            'return $next($request);',
        ], 'app/Middleware/Foo.php');

        $this->assertFilenameNotExists('tests/Feature/Middleware/FooTest.php');
    }

    public function testItCanGenerateMiddlewareFile_with_tests()
    {
        $this->artisan('make:middleware', ['name' => 'Foo', '--test' => true])
            ->assertExitCode(0);

        $this->assertFilenameExists('app/Middleware/Foo.php');
        $this->assertFilenameExists('tests/Feature/Middleware/FooTest.php');
    }
}
