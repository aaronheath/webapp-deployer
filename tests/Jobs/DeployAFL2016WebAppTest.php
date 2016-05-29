<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Repository;
use App\Deployment;
use App\Jobs\DeployAFL2016WebApp;

class DeployAFL2016WebAppTest extends TestCase
{
    use DatabaseMigrations;

    public function testValid()
    {
        Deployment::create();
    }

    public function testWithInvalidAuthCode()
    {
        $this->doesntExpectJobs(App\Jobs\ExampleJob::class);

        Repository::create([
            'name' => 'testuser/testrepo',
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
