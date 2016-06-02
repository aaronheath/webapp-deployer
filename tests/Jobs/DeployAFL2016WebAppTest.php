<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Repository;
use App\Deployment;
use App\Events\ReleaseDeployed;
use App\Jobs\DeployAFL2016WebApp;
use Illuminate\Events\Dispatcher as EventDispatcher;

class DeployAFL2016WebAppTest extends TestCase
{
    use DatabaseMigrations;

    protected $examplePayload;

    public function setUp()
    {
        parent::setUp();

        $this->examplePayload = file_get_contents(storage_path('tests/example-payload.json'));
    }

    public function testHandle()
    {
        // Create Repository
        $repo = Repository::create([
            'name' => 'testuser/testrepo',
            'branch' => 'master',
            'token' => 'qwerty',
            'job' => 'DeployAFL2016WebApp',
        ]);

        // Create Deployment
        $deployment = Deployment::create([
            'repository' => $repo->id,
            'request' => $this->examplePayload,
        ]);

        // Mock Event Dispatcher
        $events = $this->getMockBuilder('Illuminate\Events\Dispatcher')->getMock();

        $events->expects($this->once())->method('fire')->with($this->isInstanceOf(ReleaseDeployed::class));

        // Stub Class Being Testing
        $stubbed = $this->getMockBuilder('App\Jobs\DeployAFL2016WebApp')
            ->setConstructorArgs([$events, $deployment])
            ->setMethods(['exec'])
            ->getMock();

        $stubbed->method('exec')->willReturn(true);

        $stubbed->handle();

        $deployment = $deployment->fresh();

        $this->assertEquals('success', $deployment->status);
    }

    public function testHandleCheckForInProgress()
    {
        // Create Repository
        $repo = Repository::create([
            'name' => 'testuser/testrepo',
            'branch' => 'master',
            'token' => 'qwerty',
            'job' => 'DeployAFL2016WebApp',
        ]);

        // Create Deployment
        $deployment = Deployment::create([
            'repository' => $repo->id,
            'request' => $this->examplePayload,
        ]);

        // Mock Event Dispatcher
        $events = $this->getMockBuilder('Illuminate\Events\Dispatcher')->getMock();

        // Stub Class Being Testing
        $stubbed = $this->getMockBuilder('App\Jobs\DeployAFL2016WebApp')
            ->setConstructorArgs([$events, $deployment])
            ->setMethods(['seekDeployment'])
            ->getMock();

        $stubbed->handle();

        $this->assertEquals('in-progress', $deployment->status);
    }

    public function testHandleExec()
    {
        // Create Repository
        $repo = Repository::create([
            'name' => 'testuser/testrepo',
            'branch' => 'master',
            'token' => 'qwerty',
            'job' => 'DeployAFL2016WebApp',
        ]);

        // Create Deployment
        $deployment = Deployment::create([
            'repository' => $repo->id,
            'request' => $this->examplePayload,
        ]);

        // Mock Event Dispatcher
        $events = $this->getMockBuilder('Illuminate\Events\Dispatcher')->getMock();

        $events->expects($this->once())->method('fire')->with($this->isInstanceOf(ReleaseDeployed::class));

        // Stub Class Being Testing
        $stubbed = $this->getMockBuilder('App\Jobs\DeployAFL2016WebApp')
            ->setConstructorArgs([$events, $deployment])
            ->setMethods(['exec'])
            ->getMock();

        $stubbed->expects($this->once())->method('exec')->will($this->returnCallback(function($cmd) {
            $this->assertEquals('cd /var/www/afl-2016 ; git pull origin master ; npm install ; npm update', $cmd);

            return true;
        }));

        $stubbed->handle();

        $deployment = $deployment->fresh();

        $this->assertEquals('success', $deployment->status);
    }

    public function testHandleWithFailedDeploy()
    {
        // Create Repository
        $repo = Repository::create([
            'name' => 'testuser/testrepo',
            'branch' => 'master',
            'token' => 'qwerty',
            'job' => 'DeployAFL2016WebApp',
        ]);

        // Create Deployment
        $deployment = Deployment::create([
            'repository' => $repo->id,
            'request' => $this->examplePayload,
        ]);

        // Mock Event Dispatcher
        $events = $this->getMockBuilder('Illuminate\Events\Dispatcher')->getMock();

        $events->expects($this->never())->method('fire')->with($this->isInstanceOf(ReleaseDeployed::class));

        // Stub Class Being Testing
        $stubbed = $this->getMockBuilder('App\Jobs\DeployAFL2016WebApp')
            ->setConstructorArgs([$events, $deployment])
            ->setMethods(['deploy'])
            ->getMock();

        $stubbed->method('deploy')->willReturn(false);

        $stubbed->handle();

        $this->assertEquals('failed', $deployment->status);
    }
}
