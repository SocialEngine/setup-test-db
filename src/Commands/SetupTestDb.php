<?php namespace SocialEngine\TestDbSetup\Commands;

use Illuminate\Console\Command;

class SetupTestDb extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:seed-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets up and seeds db for testing once per execution to save on re-seeding';

    /**
     * loads env file (.env.something) based on environment set via --env=something.
     *
     * @return void
     */
	public function reloadEnvironment()
	{
        //$this->info("d1" . env('DB_CONNECTION'));
        $this->info("Reload environment : " . \App::environment());
		putenv('APP_ENV=' . \App::environment());
		$this->laravel->make('Illuminate\Foundation\Bootstrap\DetectEnvironment')->bootstrap($this->laravel);
		$envFile = \App::environmentFile();
		if($envFile != ".env." . \App::environment()) {
			$envFile = ".env." . \App::environment();
		}
		(new \Dotenv\Dotenv(\App::environmentPath(), $envFile ))->overload();
		$this->laravel->make('Illuminate\Foundation\Bootstrap\LoadConfiguration')->bootstrap($this->laravel);
        //$this->info("loaded DB_DATABASE : " . env('DB_DATABASE'));
        //$this->info("d2" . \App::environmentPath());
        //$this->info("d2" . \App::environmentFile());
	}

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line("<question>[{$this->signature}]</question> starting the seeding");

		$this->reloadEnvironment();
		

        $config = $this->config();

        $defaultConn = $config->get('database.default');
        //$this->info("defaultConn :" . $defaultConn);
        $database = $config->get("database.connections.{$defaultConn}.database");
        //$this->info("database :" . $database);

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
        $this->line("<question>[{$this->signature}]</question> db seeded!");
    }

    private function createDb($dbPath)
    {
        //$this->info("Delete: <comment>{$dbPath}</comment>");
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
