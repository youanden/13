<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportDataFromGoogle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import from Google Sheet';

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
     * @return int
     */
    public function handle()
    {
        https://docs.google.com/spreadsheets/d/1on0wFpfZ4dqV63bcfb6Xx1klFAV6P9LXqh-BI7G9luM/edit?usp=sharing
        $url = 'https://sheets.googleapis.com/v4/spreadsheets/1on0wFpfZ4dqV63bcfb6Xx1klFAV6P9LXqh-BI7G9luM/values/Main Sheet?key={KEY}';
        $json = json_decode(file_get_contents($url));
        $rows = $json->values;

        foreach($rows as $row) {
            var_dump($row);
        }
        return 0;
    }
}
