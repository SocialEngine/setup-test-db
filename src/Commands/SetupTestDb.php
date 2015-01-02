<?php namespace SocialEngine\TestDbSetup\Commands;

use Schema, DB, File;
use Illuminate\Console\Command;

class SetupTestDb extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'db:seed-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets up and seeds db for testing once per execution to save on re-seeding';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->line("<question>[{$this->name}]</question> starting the seeding");

        $config = $this->laravel['config'];
        $artisan = $this->laravel['artisan'];

        $defaultConn = $config->get('database.default');
        $database = $config->get("database.connections.{$defaultConn}.database");

        if ($defaultConn !== 'sqlite') {
            $this->info("Non-file based db detected: <comment>$defaultConn</comment>");
        } else {
            $this->createDb($database);
        }
        $artisan->call('migrate');

        if($config->get('setup-test-db::truncate', false) && $defaultConn !== 'sqlite') {
            $this->truncateDb($database);
        }

        $this->info("Seeding: <comment>{$database}</comment>");

        $options = [
            '--class' => $config->get('setup-test-db::seed-class', 'DatabaseSeeder')
        ];

        $artisan->call('db:seed', $options);
        $this->line("<question>[{$this->name}]</question> db seeded!");
    }

    private function createDb($dbPath)
    {
        File::delete($dbPath);
        File::put($dbPath, '');
    }

    /**
     * @param $database
     */
    public function truncateDb($database)
    {
        $this->info("Truncating: <comment>{$database}</comment>");
        // Truncate all tables, except migrations
        $tables = DB::select('SHOW TABLES');
        $tables_in_database = "Tables_in_{$database}";
        foreach ($tables as $table) {
            if ($table->$tables_in_database == 'migrations') {
                continue;
            }
            DB::table($table->$tables_in_database)->truncate();
        }
    }
}
