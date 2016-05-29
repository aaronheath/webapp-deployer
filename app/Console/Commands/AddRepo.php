<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repository;

class AddRepo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repo:add 
                            {name : Name of the repo (example: aaronheath/afl-2016)}
                            {branch : Name of the branch to deploy}
                            {token : Travis CI account token}
                            {job : Laravel Job to dispatch to deploy webapp}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new remote repository';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->argument('name');
        $branch = $this->argument('branch');
        $token = $this->argument('token');
        $job = $this->argument('job');

        $this->table(
            [
                'Name',
                'Branch',
                'Token',
                'Job',
            ],
            [
                [
                    $name,
                    $branch,
                    $token,
                    $job,
                ],
            ]
        );

        if(!$this->confirm('Is this information correct? [y|N]')) {
            return $this->warn('Okay, in that case we\'ll exit now.');
        }

        $repository = Repository::create([
            'name' => $name,
            'branch' => $branch,
            'token' => $token,
            'job' => $job,
        ]);

        if($repository) {
            $this->info('Created.');
        } else {
            $this->error('Creation failed.');
        }


    }
}
