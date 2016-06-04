<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Deployment;
use App\Events\ReleaseDeployed;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Log;

class DeployAFL2016WebApp extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    
    protected $deployment;
    protected $event;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(EventDispatcher $event, Deployment $deployment)
    {
        $this->deployment = $deployment;
        $this->event = $event;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->updateStatus('in-progress');

        $this->seekDeployment();
    }

    protected function seekDeployment()
    {
        $this->deploy() ? $this->deploySucceeded() : $this->deployFailed();
    }
    
    protected function deploy()
    {
        list($output, $returnValue) = $this->exec($this->cmd());
        
        $this->updateOutput($output, $returnValue);

        $return = $returnValue == 0;

        Log::info('--- deploy returning', [$return]);
        
        return $return;
    }
    
    protected function cmd()
    {
        $this->deployment = $this->deployment->fresh();
        
        return collect([
            'cd /var/www/com_aaronheath_afl',
            'git pull origin ' . $this->deployment->repo->branch,
            'npm install',
            'npm update',
        ])->implode(' ; ');
    }
    
    protected function exec($cmd)
    {
        Log::info('--- Before exec', [$cmd]);

        exec($cmd, $output, $returnValue);

        Log::info('--- After exec', [$returnValue, $output]);
        
        return [$output, $returnValue];
    }
    
    protected function deploySucceeded()
    {
        Log::info('--- In deploySucceeded');

        $this->updateStatus('success');

        $this->event->fire(new ReleaseDeployed($this->deployment));
    }
    
    protected function deployFailed()
    {
        Log::info('--- In deployFailed');

        $this->updateStatus('failed');
    }

    protected function updateStatus($status)
    {
        $this->deployment->status = $status;
        $this->deployment->save();
    }

    protected function updateOutput($output, $returnValue)
    {
        $this->deployment->return_value = $returnValue;
        $this->deployment->output = $output;
        $this->deployment->save();
    }
}
