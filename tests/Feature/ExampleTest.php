<?php
 
namespace Tests\Feature;
 
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
 
class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $this->get(route('web.home'))->assertSuccessful();
    }
}
