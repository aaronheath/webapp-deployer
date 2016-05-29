<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Repository;
use Log;

class TravisCIPostRequest extends Request
{
    protected $payload;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->payload = collect(json_decode($this->input('payload'), true));
        
        if(!$this->verifyAuthCode()) {
            Log::info('Travis CI POST - Invalid authorization code');

            return false;
        }

        if(!$this->verifyStatusMessage()) {
            Log::info('Travis CI POST - Invalid status message');

            return false;
        }

        if(!$this->verifyBranch()) {
            Log::info('Travis CI POST - Invalid branch');

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

        if(!$this->generateAuthCodes()->contains($authHeader)) {
            return false;
        }
        
        return true;
    }
    
    protected function generateAuthCodes()
    {
        $authCodes = Repository::all()->map(function($item, $key) {
            return hash('sha256', $item->name . $item->token);
        });

        return collect($authCodes);
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

        return $this->payload->get('branch') == 'master';
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
