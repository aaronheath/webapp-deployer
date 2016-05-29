<?php

namespace App\Listeners;

use App\Events\ReleaseDeployed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mail;

class EmailDeploymentConfirmation
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ReleaseDeployed  $event
     * @return void
     */
    public function handle(ReleaseDeployed $event)
    {
        Mail::send('emails.deploymentConfirmation', ['event' => $event], function($m) use ($event) {
            $m->from('changi@aaronheath.io', 'Changi');
            $m->to('aaron@aaronheath.com', 'Aaron Heath');
            $m->subject('Successful Deployment - ' . $event->deployment->repo->name);
        });
    }
}
