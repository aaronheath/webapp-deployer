<?php

namespace App\Http\Controllers\Webhook;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\TravisCIPostRequest;
use App\Repository;
use App\Deployment;

class TravisCIController extends Controller
{
    /**
     * Handle incomming webhook notifications from Travis CI.
     * 
     * Steps taken:
     * - Verify authorization header
     * - Verify that 'status_message' of payload is 'Passed' or 'Fixed'
     * - Verify that 'branch' is 'master'
     * - Insert DB record to track the webapp update
     * - Update webapp
     * - Update DB record
     * - Send success notification emails
     */
    public function handle(TravisCIPostRequest $request)
    {
        $payload = json_decode($request->input('payload'), true);
        
        $repoName = $payload['repository']['owner_name'] . '/' . $payload['repository']['name'];
        
        $repo = Repository::where('name', $repoName)->first();
        
        $deployment = Deployment::create([
            'repository' => $repo->id,
            'request' => collect($payload)->toJson(),
        ]);

        $class = 'App\Jobs\\' . $repo->job;
        
        $this->dispatch(new $class($deployment));
    }
}
