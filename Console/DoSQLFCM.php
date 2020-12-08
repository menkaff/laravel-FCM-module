<?php

namespace Modules\FCM\Console;

use Illuminate\Console\Command;

class DoSQLFCM extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'command:DoSQLFCM';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

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
        \DB::unprepared(\File::get(base_path('Modules/FCM/Database/Migration.sql')));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
        // return [
        //     ['example', InputArgument::REQUIRED, 'An example argument.'],
        // ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
        // return [
        //     ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        // ];
    }
}
