<?php

namespace Roycedev\Roycedb\Console;

use Illuminate\Console\Command;

class MakeDbTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roycedb:maketable {tablename? : The name of the table to create.}
            {--no-log : Disables logging successful and unsuccessful imports.}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a databae migration script and model for a database table';

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
        //
    }
}
