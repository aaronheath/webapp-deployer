<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Deployment;
use App\Events\ReleaseDeployed;
use Event;

class DeployAFL2016WebApp extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    
    protected $deployment;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Deployment $deployment)
    {
        $this->deployment = $deployment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->updateStatus('in-progress');

        $this->deploy() ? $this->deploySucceeded() : $this->deployFailed();
    }
    
    protected function updateStatus($status)
    {
        $this->deployment->status = $status;
        $this->deployment->save();
    }
    
    protected function deploy()
    {
        $cmd = collect([
            'cd /var/www/afl-2016',
            'git pull origin ' . $this->deployment->repo->branch,
            'npm update',
            'npm install',
        ])->implode(' ; ');

        return exec($cmd);
    }
    
    protected function deploySucceeded()
    {
        $this->updateStatus('success');

        Event::fire(new ReleaseDeployed($this->deployment));
    }
    
    protected function deployFailed()
    {
        $this->updateStatus('failed');
    }
}
