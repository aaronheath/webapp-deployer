<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repository;

class ViewRepo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repo:view';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prints the current repositories table.';

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
        $repositories = Repository::all();

        $body = $repositories->map(function($item, $i) {
            return [
                $item->id,
                $item->name,
                $item->branch,
                $item->token,
                $item->job,
            ];
        });

        $this->table(
            [
                'Id',
                'Name',
                'Branch',
                'Token',
                'Job',
            ],
            $body
        );
    }
}
