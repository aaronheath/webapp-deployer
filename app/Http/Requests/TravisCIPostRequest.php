<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Repository;
use Log;

class TravisCIPostRequest extends Request
{
    protected $payload;
    protected $repos;
    protected $repo;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->payload = collect(json_decode($this->input('payload'), true));

        Log::info('Travis CI POST - Payload', $this->payload->toArray());
        
        if(!$this->verifyAuthCode()) {
            Log::info('Travis CI POST - Invalid authorization code', [
                'authorization' => $this->header('authorization'),
            ]);

            return false;
        }

        if(!$this->verifyStatusMessage()) {
            Log::info('Travis CI POST - Invalid status message', [
                'status_message' => $this->payload->get('status_message'),
            ]);

            return false;
        }

        if(!$this->verifyBranch()) {
            Log::info('Travis CI POST - Invalid branch', [
                'branch' => $this->payload->get('branch'),
            ]);

            return false;
        }
        
        return true;
    }
    
    protected function verifyAuthCode()
    {
        if(!$this->hasHeader('authorization')) {
            return false;
        }

        $authHeader = $this->header('authorization');

        if(!$this->generateAuthCodes()->has($authHeader)) {
            return false;
        }

        $this->repo = $this->repos->get($authHeader);
        
        return true;
    }
    
    protected function generateAuthCodes()
    {
        $this->repos = collect([]);

        Repository::all()->each(function($item, $key) {
            $this->repos->put(hash('sha256', $item->name . $item->token), $item);
        });

        return $this->repos;
    }
    
    protected function verifyStatusMessage()
    {
        if(!$this->payload->has('status_message')) {
            return false;
        }
        
        return collect(['Passed', 'Fixed'])->contains($this->payload->get('status_message'));
    }
    
    protected function verifyBranch()
    {
        if(!$this->payload->has('branch')) {
            return false;
        }

        return $this->payload->get('branch') == $this->repo->branch;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
