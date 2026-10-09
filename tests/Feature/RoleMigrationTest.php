<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RoleMigrationTest extends TestCase
{
    public static function roleMigrations(): array
    {
        return [
            'normalize roles' => ['2026_10_07_000002_align_user_identity_and_roles.php', 'up', ['customer', 'staff']],
            'restore legacy roles' => ['2026_10_07_000002_align_user_identity_and_roles.php', 'down', []],
            'add clerk and admin' => ['2026_10_07_000004_add_clerk_admin_roles_and_activity_log.php', 'up', ['customer', 'clerk', 'admin']],
            'restore staff role' => ['2026_10_07_000004_add_clerk_admin_roles_and_activity_log.php', 'down', ['customer', 'staff']],
        ];
    }

    #[DataProvider('roleMigrations')]
    public function test_postgres_role_migrations_change_constraints_separately_from_column_types(string $file, string $direction, array $roles): void
    {
        config([
            'database.default' => 'migration_sql',
            'database.connections.migration_sql' => [
                'driver' => 'pgsql',
                'database' => 'migration_test',
                'prefix' => '',
            ],
        ]);

        $connection = DB::connection();
        $pdo = $this->createMock(PDO::class);
        $pdo->method('getAttribute')->with(PDO::ATTR_SERVER_VERSION)->willReturn('17.0');
        $pdo->expects($this->never())->method('prepare');
        $connection->setPdo($pdo)->setReadPdo($pdo);
        Schema::swap($connection->getSchemaBuilder());
        $migration = require database_path('migrations/'.$file);

        // Compile the real migration using PostgreSQL's grammar without connecting.
        $queries = $connection->pretend(fn () => $migration->$direction());
        $sql = implode("\n", array_column($queries, 'query'));

        $this->assertStringNotContainsString('type varchar(255) check', strtolower($sql));
        $this->assertStringContainsString('alter column "role" type varchar(255)', strtolower($sql));

        $drop = 'ALTER TABLE "users" DROP CONSTRAINT IF EXISTS "users_role_check"';
        $this->assertStringContainsString($drop, $sql);
        $this->assertLessThan(strpos($sql, 'update "users"'), strpos($sql, $drop), 'Remove the old role restriction before converting stored roles.');

        if ($roles === []) {
            $this->assertStringNotContainsString('ADD CONSTRAINT "users_role_check"', $sql);
        } else {
            $allowed = "'".implode("', '", $roles)."'";
            $add = 'ALTER TABLE "users" ADD CONSTRAINT "users_role_check" CHECK ("role" IN ('.$allowed.'))';
            $this->assertStringContainsString($add, $sql);
            $this->assertGreaterThan(strrpos($sql, 'update "users"'), strpos($sql, $add), 'Validate the final role values after converting stored roles.');
        }
    }
}
