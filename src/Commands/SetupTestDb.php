<?php namespace SocialEngine\TestDbSetup\Commands;

use Illuminate\Console\Command;

class SetupTestDb extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'test:setup-db';

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

        $defaultConn =  $this->laravel['config']->get('database.default');

        if ($defaultConn !== 'sqlite') {
            $this->info("Non-file based db detected: <comment>$defaultConn</comment>");
        } else {
            $dbPath = $this->laravel['config']->get('database.connections.' . $defaultConn . '.database');
            $this->createDb($dbPath);
        }

        $this->laravel['artisan']->call('migrate');

        $options = [
            '--class' => $this->laravel['config']->get("setup-test-db::seedClass"),
        ];
        $this->laravel['artisan']->call('db:seed', $options);
        $this->line("<question>[{$this->name}]</question> db seeded!");
    }

    private function createDb($dbPath)
    {
        passthru('rm ' . $dbPath . ' 2>/dev/null');
        passthru('touch ' . $dbPath);
    }
}
