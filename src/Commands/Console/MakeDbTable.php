<?php

namespace Roycedev\DbCli\Commands\Console;

use Illuminate\Console\Command;

class MakeDbTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roycedev:maketable {tablename? : The name of the table to create.}
            {--no-log : Disables logging successful and unsuccessful imports.}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
