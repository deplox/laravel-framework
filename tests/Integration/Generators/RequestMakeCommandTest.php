<?php

namespace Illuminate\Tests\Integration\Generators;

class RequestMakeCommandTest extends TestCase
{
    protected $files = [
        'app/Requests/FooRequest.php',
    ];

    public function testItCanGenerateRequestFile()
    {
        $this->artisan('make:request', ['name' => 'FooRequest'])
            ->assertExitCode(0);

        $this->assertFileContains([
            'namespace App\Requests;',
            'use Illuminate\Foundation\Http\FormRequest;',
            'class FooRequest extends FormRequest',
        ], 'app/Requests/FooRequest.php');
    }
}
