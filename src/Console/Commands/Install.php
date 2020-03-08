<?php

namespace GTCrais\Ion\Console\Commands;

use Illuminate\Database\Console\Migrations\BaseCommand;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Str;

class Install extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ion:install {--full}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs Ion CMS';

	/**
	 * @var Migrator
	 */
    protected $migrator;

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
		[
			$copyAddRoleToUsersMigration,
			$copyCreateDefaultAdminMigration,
			$runMigrations
		] = $this->getUserInput();

		// Newline for visual clarity
		$this->echoNewline();

		$this->publishConfig($copyAddRoleToUsersMigration);
		$this->publishAssets();
		$this->createUserEntity($copyAddRoleToUsersMigration);

		if ($copyAddRoleToUsersMigration) {
			$this->copyAddRolesToUsersMigration();
		}

		if ($copyCreateDefaultAdminMigration) {
			$this->copyCreateDefaultAdminMigration();
		}

		if ($runMigrations) {
			$this->runMigrations();
		}

		$this->info("Ion installed successfully!");
    }

	protected function getUserInput()
	{
		if (!$this->option('full')) {
			$copyAddRoleToUsersMigration = $this->choice('Do you want to publish the Add Role To Users migration file? (y/n)', ['y', 'n'], 'y');

			if ($copyAddRoleToUsersMigration == 'y') {
				$copyCreateDefaultAdminMigration = $this->choice('Do you want to publish the Create Default Admin migration file? (y/n)', ['y', 'n'], 'y');
				$runMigrations = $this->choice('Do you want to run migrations? (y/n)', ['y', 'n'], 'y');
			} else {
				$copyCreateDefaultAdminMigration = 'n';
				$runMigrations = 'n';
			}

			return [
				$copyAddRoleToUsersMigration == 'y' ? true : false,
				$copyCreateDefaultAdminMigration == 'y' ? true : false,
				$runMigrations == 'y' ? true : false
			];
		}

		return $this->getDefaultUserInput();
    }

	protected function getDefaultUserInput()
	{
		return [true, true, true];
    }

	protected function runMigrations()
	{
		try {
			if ($this->pendingMigrationsExist()) {
				$this->echoNewline();
				$this->info('Running migrations...');
				$this->call('migrate');
				$this->info('Migrations ran successfully.');
				$this->echoNewline();
			} else {
				$this->info('No pending migrations.');
			}
		} catch (\Exception $e) {
			\Log::error($e);

			$this->info('Could not run migrations: ' . $e->getMessage());
			$this->info('Check your application\'s log file for more information.');
		}
    }

	protected function pendingMigrationsExist()
	{
		// https://github.com/erjanmx/laravel-migrate-check/blob/master/src/Commands/MigrateCheckCommand.php

		try {
			$this->migrator = app('migrator');
			$this->migrator->setConnection(config('database.default'));
			$ranMigrations = !$this->migrator->repositoryExists() ? [] : $this->migrator->getRepository()->getRan();

			return !!array_diff(
				array_keys($this->migrator->getMigrationFiles($this->getMigrationPaths())),
				$ranMigrations
			);
		} catch (\Exception $e) {
			return true;
		}
	}

	protected function copyCreateDefaultAdminMigration()
	{
		if ($this->migrationFileExists('create_default_admin.php')) {
			$this->info("Create Default Admin migration file has already been copied. Skipping this step.");

			return;
		}

		\File::copy(
			__DIR__ . '/../../../stubs/migrations/create_default_admin.stub',
			database_path('/migrations/' . now()->addSecond()->format('Y_m_d_His') . '_create_default_admin.php')
		);

		$this->info("Create Default Admin migration file copied.");
	}

	protected function copyAddRolesToUsersMigration()
	{
		if ($this->migrationFileExists('add_role_to_users.php')) {
			$this->info("Add Role To Users migration file has already been copied. Skipping this step.");

			return;
		}

		\File::copy(
			__DIR__ . '/../../../stubs/migrations/add_role_to_users.stub',
			database_path('/migrations/' . now()->format('Y_m_d_His') . '_add_role_to_users.php')
		);

		$this->info("Add Role To Users migration file copied.");
    }

	protected function migrationFileExists($filename)
	{
		foreach (\File::files(database_path('migrations')) as $file) {
			/** @var \SplFileInfo $file */
			if (Str::endsWith($file->getFilename(), $filename)) {
				return true;
			}
		};

		return false;
    }

	protected function publishAssets()
	{
		$this->callSilent('vendor:publish', ['--tag' => 'ion']);

		$this->info("Assets published.");
    }

	protected function publishConfig($userRolesAdded)
	{
		$this->ensureDirectoryExists(config_path('ion/entities'));
		$configPublished = false;

		if (\File::isFile(config_path('ion/entities/user.php'))) {
			$this->info('User entity config file already published. Skipping this step.');
		} else {
			file_put_contents(
				config_path('ion/entities/user.php'),
				Str::replaceFirst(
					'{userRole}',
					$userRolesAdded ? file_get_contents(__DIR__ . '/../../../stubs/config/userRole.stub') : '',
					file_get_contents(__DIR__ . '/../../../stubs/config/user.stub')
				)
			);

			$configPublished = true;
		}

		if (\File::isFile(config_path('ion/navigation.php'))) {
			$this->info('Navigation config file already published. Skipping this step.');
		} else {
			\File::copy(__DIR__ . '/../../../config/publish/navigation.php', config_path('ion/navigation.php'));

			$configPublished = true;
		}

		if (\File::isFile(config_path('ion/settings.php'))) {
			$this->info('Settings config file already published. Skipping this step.');
		} else {
			$contents = Str::replaceFirst(
				'{authorizedApiRoles}',
				$userRolesAdded ? ':admin,superadmin' : '',
				file_get_contents(__DIR__ . '/../../../stubs/config/settings.stub')
			);

			$contents = Str::replaceFirst(
				'{systemWebRoles}',
				$userRolesAdded ? ':superadmin' : '',
				$contents
			);

			file_put_contents(config_path('ion/settings.php'), $contents);

			$configPublished = true;
		}

		if ($configPublished) {
			$this->info('Config published.');
		}
    }

	protected function createUserEntity($userRolesAdded)
	{
		$this->ensureDirectoryExists(app_path('Ion/Entities'));

		if (\File::isFile(app_path('Ion/Entities/User.php'))) {
			$this->info('User Entity already published. Skipping this step.');

			return;
		}

		$content = $userRolesAdded ?
			file_get_contents(__DIR__ . '/../../../stubs/Entities/UserWithRoles.stub') :
			file_get_contents(__DIR__ . '/../../../stubs/Entities/UserWithoutRoles.stub');

		file_put_contents(
			app_path('Ion/Entities/User.php'),
			Str::replaceFirst('{userNamespace}', $this->userNamespace(), $content)
		);

		$this->info('User Entity created in /app/Ion/Entities.');
    }

	protected function userNamespace()
	{
		return \File::isFile(app_path('Models/User.php')) ? 'App\Models\User' : 'App\User';
    }

	protected function ensureDirectoryExists($path)
	{
		if (!\File::isDirectory($path)) {
			\File::makeDirectory($path, 0755, true);
		}
	}

	protected function echoNewline()
	{
		$this->info('');
    }
}
