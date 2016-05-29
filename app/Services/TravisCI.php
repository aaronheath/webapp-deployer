<?php

namespace App\Http\Services;

class TravisCI
{
    public function __construct()
    {
        
    }

    /**
     * Handle incomming webhook notifications from Travis CI.
     *
     * Steps taken:
     * - Verify authorization header
     * - Verify that 'status_message' of payload is 'Passed' or 'Fixed'
     * - Verify that 'branch' is 'master'
     * - Verify that repository url is expected
     * - Insert DB record to track the webapp update
     * - Update webapp
     * - Update DB record
     * - Send success notification emails
     */
    public function validateAuthHeader(Request $request, $repo)
    {

    }
}
