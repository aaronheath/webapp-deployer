<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Repository;
use App\Deployment;

class TravisCIControllerTest extends TestCase
{
    use DatabaseMigrations;

    protected $examplePayload;

    public function setUp()
    {
        parent::setUp();

        $this->examplePayload = file_get_contents(storage_path('tests/example-payload.json'));
    }

    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testValid()
    {
        $this->expectsJobs(App\Jobs\ExampleJob::class);

        Repository::create([
            'name' => 'testuser/testrepo',
            'branch' => 'master',
            'token' => 'qwerty',
            'job' => 'ExampleJob',
        ]);

        $authCode = hash('sha256', 'testuser/testrepoqwerty');

        $response = $this->call(
            'POST',
            '/webhook/travisci',
            ['payload' => $this->examplePayload],
            [],
            [],
            ['HTTP_Authorization' => $authCode]
        );

        $this->assertEquals(200, $response->status());

        $deployment = Deployment::all()->first();

        $this->assertEquals(1, $deployment->repository);

        $deploymentPayload = json_decode($deployment->request, true);
        $expectedPayload = json_decode($this->examplePayload, true);
        
        $this->assertEquals($expectedPayload['commit'], $deploymentPayload['commit']);
    }

    public function testWithInvalidAuthCode()
    {
        $this->doesntExpectJobs(App\Jobs\ExampleJob::class);

        Repository::create([
            'name' => 'testuser/testrepo',
            'branch' => 'master',
            'token' => 'qwerty',
            'job' => 'ExampleJob',
        ]);

        $authCode = hash('sha256', 'WillBeInvalid');

        $response = $this->call(
            'POST',
            '/webhook/travisci',
            ['payload' => $this->examplePayload],
            [],
            [],
            ['HTTP_Authorization' => $authCode]
        );

        $this->assertEquals(403, $response->status());
    }

    public function testWithInvalidStatusMessage()
    {
        $this->doesntExpectJobs(App\Jobs\ExampleJob::class);

        Repository::create([
            'name' => 'testuser/testrepo',
            'branch' => 'master',
            'token' => 'qwerty',
            'job' => 'ExampleJob',
        ]);

        $authCode = hash('sha256', 'testuser/testrepoqwerty');

        $payload = json_decode($this->examplePayload, true);
        $payload['status_message'] = 'Broken';

        $response = $this->call(
            'POST',
            '/webhook/travisci',
            ['payload' => collect($payload)->toJson()],
            [],
            [],
            ['HTTP_Authorization' => $authCode]
        );

        $this->assertEquals(403, $response->status());
    }

    public function testWithInvalidBranch()
    {
        $this->doesntExpectJobs(App\Jobs\ExampleJob::class);

        Repository::create([
            'name' => 'testuser/testrepo',
            'branch' => 'master',
            'token' => 'qwerty',
            'job' => 'ExampleJob',
        ]);

        $authCode = hash('sha256', 'testuser/testrepoqwerty');

        $payload = json_decode($this->examplePayload, true);
        $payload['branch'] = 'task/issue';

        $response = $this->call(
            'POST',
            '/webhook/travisci',
            ['payload' => collect($payload)->toJson()],
            [],
            [],
            ['HTTP_Authorization' => $authCode]
        );

        $this->assertEquals(403, $response->status());
    }
}
