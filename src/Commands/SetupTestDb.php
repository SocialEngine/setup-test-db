<?php namespace SocialEngine\TestDbSetup\Commands;

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

        $config = $this->config();

        $defaultConn = $config->get('database.default');
        $database = $config->get("database.connections.{$defaultConn}.database");
        $driver = $config->get("database.connections.{$defaultConn}.driver");
        if ($driver !== 'sqlite') {
            $this->info("Non-file based db detected: <comment>$driver</comment>");
        } else {
            $this->createDb($database);
        }
        $this->artisan('migrate');

        $truncateMethod = 'truncate' . ucfirst($driver) . 'Db';
        if($config->get('setup-test-db.truncate', false) && method_exists($this, $truncateMethod)) {
            $this->$truncateMethod($database);
        }

        $this->info("Seeding: <comment>{$database}</comment>");

        $options = [
            '--class' => $config->get('setup-test-db.seed-class', 'DatabaseSeeder')
        ];

        $this->artisan('db:seed', $options);
        $this->line("<question>[{$this->name}]</question> db seeded!");
    }

    private function createDb($dbPath)
    {
        $file = $this->fileSystem();
        $file->delete($dbPath);
        $file->put($dbPath, '');
    }

    /**
     * @param $database
     */
    public function truncateMysqlDb($database)
    {
        $db = $this->db();
        $this->info("Truncating: <comment>{$database}</comment>");
        // Truncate all tables, except migrations
        $tables =  $db->select('SHOW TABLES');
        $tablesInDb = "Tables_in_{$database}";

        $migrationsTable = $this->config()->get('database.migrations');
        $db->statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach ($tables as $table) {
            $table = (array) $table;
            if ($table[$tablesInDb] == $migrationsTable) {
                continue;
            }
            $db->table($table[$tablesInDb])->truncate();
        }
        $db->statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * @return \Illuminate\Config\Repository
     */
    private function config()
    {
        return $this->laravel['config'];
    }

    /**
     * Call artisan command and return code.
     *
     * @param string  $command
     * @param array   $parameters
     * @return int
     */
    private function artisan($command, $parameters = [])
    {
        return $this->laravel['Illuminate\Contracts\Console\Kernel']->call($command, $parameters);
    }

    /**
     * @return \Illuminate\Database\Connection
     */
    private function db()
    {
        return $this->laravel['db'];
    }

    /**
     * @return \Illuminate\Filesystem\Filesystem
     */
    private function fileSystem()
    {
        return $this->laravel['files'];
    }
}
