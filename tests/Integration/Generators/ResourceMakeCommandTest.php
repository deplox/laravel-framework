<?php

namespace Illuminate\Tests\Integration\Generators;

class ResourceMakeCommandTest extends TestCase
{
    protected $files = [
        'app/Resources/FooResource.php',
        'app/Resources/FooResourceCollection.php',
    ];

    public function testItCanGenerateResourceFile()
    {
        $this->artisan('make:resource', ['name' => 'FooResource'])
            ->assertExitCode(0);

        $this->assertFileContains([
            'namespace App\Resources;',
            'use Illuminate\Http\Resources\Json\JsonResource;',
            'class FooResource extends JsonResource',
        ], 'app/Resources/FooResource.php');
    }

    public function testItCanGenerateResourceCollectionFile()
    {
        $this->artisan('make:resource', ['name' => 'FooResourceCollection', '--collection' => true])
            ->assertExitCode(0);

        $this->assertFileContains([
            'namespace App\Resources;',
            'use Illuminate\Http\Resources\Json\ResourceCollection;',
            'class FooResourceCollection extends ResourceCollection',
        ], 'app/Resources/FooResourceCollection.php');
    }
}
