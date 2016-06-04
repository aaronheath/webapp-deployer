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
    protected $repo;
    protected $deployment;
    protected $events;
    protected $constructorArgs;

    public function setUp()
    {
        parent::setUp();

        $this->examplePayload = file_get_contents(storage_path('tests/example-payload.json'));
    }

    public function testHandle()
    {
        $this->setupTestsForClass();

        $this->events->expects($this->once())->method('fire')->with($this->isInstanceOf(ReleaseDeployed::class));

        $stubbed = $this->createStubbed(['exec']);

        $stubbed->method('exec')->willReturn($this->successfulExecReturn());

        $stubbed->handle();

        $this->refresh($this->deployment);

        $this->assertEquals('success', $this->deployment->status);
        $this->assertEquals($this->successfulExecReturn()[1], $this->deployment->return_value);
        $this->assertEquals($this->successfulExecReturn()[0], $this->deployment->output);
    }

    public function testHandleCheckForInProgress()
    {
        $this->setupTestsForClass();

        $stubbed = $this->createStubbed(['seekDeployment']);

        $stubbed->handle();

        $this->refresh($this->deployment);

        $this->assertEquals('in-progress', $this->deployment->status);
    }

    public function testHandleExec()
    {
        $this->setupTestsForClass();

        $this->events->expects($this->once())->method('fire')->with($this->isInstanceOf(ReleaseDeployed::class));

        $stubbed = $this->createStubbed(['exec']);

        $stubbed->expects($this->once())->method('exec')->will($this->returnCallback(function($cmd) {
            $this->assertEquals('cd /var/www/com_aaronheath_afl ; git pull origin master ; npm install ; npm update', $cmd);

            return $this->successfulExecReturn();
        }));

        $stubbed->handle();

        $this->refresh($this->deployment);

        $this->assertEquals('success', $this->deployment->status);
    }

    public function testHandleWithFailedDeploy()
    {
        $this->setupTestsForClass();

        $this->events->expects($this->never())->method('fire')->with($this->isInstanceOf(ReleaseDeployed::class));

        $stubbed = $this->createStubbed(['exec']);

        $deployResponse = ['failure message', 2];

        $stubbed->method('exec')->willReturn($deployResponse);

        $stubbed->handle();

        $this->refresh($this->deployment);

        $this->assertEquals('failed', $this->deployment->status);
        $this->assertEquals($deployResponse[1], $this->deployment->return_value);
        $this->assertEquals($deployResponse[0], $this->deployment->output);
    }
    
    protected function setupTestsForClass()
    {
        // Create Repository
        $this->repo = Repository::create([
            'name' => 'testuser/testrepo',
            'branch' => 'master',
            'token' => 'qwerty',
            'job' => 'DeployAFL2016WebApp',
        ]);

        // Create Deployment
        $this->deployment = Deployment::create([
            'repository' => $this->repo->id,
            'request' => $this->examplePayload,
        ]);

        // Mock Event Dispatcher
        $this->events = $this->getMockBuilder('Illuminate\Events\Dispatcher')->getMock();

        $this->constructorArgs = [$this->events, $this->deployment];
    }

    protected function createStubbed(array $setMethods)
    {
        return $this->getMockBuilder('App\Jobs\DeployAFL2016WebApp')
            ->setConstructorArgs($this->constructorArgs)
            ->setMethods($setMethods)
            ->getMock();
    }

    protected function successfulExecReturn()
    {
        return ['response text from cmds', 0];
    }
}
