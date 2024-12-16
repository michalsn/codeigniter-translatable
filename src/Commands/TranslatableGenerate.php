<?php

declare(strict_types=1);

namespace Michalsn\CodeIgniterTranslatable\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Michalsn\CodeIgniterTranslatable\Commands\Support\MigrationManager;

class TranslatableGenerate extends BaseCommand
{
    protected $group       = 'Translatable';
    protected $name        = 'translatable:generate';
    protected $description = 'Generate translatable skeleton structure.';
    protected $usage       = 'translatable:generate <table>';

    /**
     * The Command's Arguments
     *
     * @var array<string, string>
     */
    protected $arguments = [
        'table' => 'The table name.',
    ];

    /**
     * @return void
     */
    public function run(array $params)
    {
        $table = ! isset($params[0]) ? CLI::prompt('Type table name') : $params[0];

        helper('inflector');

        $this->createMigration(plural($table));
        $this->createModel($table);
        $this->createModelTranslation($table);
        $this->createEntity($table);

        CLI::write(CLI::color('  Ready! ', 'green') . 'Your skeleton files are ready. Now just modify them to your needs.');
    }

    /**
     * Create model.
     */
    private function createModel(string $table): void
    {
        $params = [
            0 => ucfirst(singular($table)) . 'Model',
        ];

        $entity           = ucfirst(singular($table));
        $modelTranslation = ucfirst(singular($table)) . 'TranslationModel';

        $this->call('make:model', $params);

        $file     = APPPATH . 'Models' . DIRECTORY_SEPARATOR . $params[0] . '.php';
        $contents = file_get_contents($file);

        $contents = str_replace(
            'use CodeIgniter\Model;',
            'use App\Entities\\' . $entity . ";\n" . 'use CodeIgniter\Model;',
            $contents
        );

        $contents = str_replace(
            'use CodeIgniter\Model;',
            'use CodeIgniter\Model;' . "\n" . 'use Michalsn\CodeIgniterTranslatable\Traits\HasTranslations;',
            $contents
        );

        $contents = str_replace(
            'protected $table',
            "use HasTranslations;\n\n\t" . 'protected $table',
            $contents
        );

        $contents = str_replace(
            "protected \$returnType       = 'array'",
            "protected \$returnType       = {$entity}::class",
            $contents
        );

        $contents = str_replace(
            'protected $useTimestamps = false;',
            'protected $useTimestamps = true;',
            $contents
        );

        $contents = str_replace(
            'protected $afterDelete    = [];',
            <<<'EOT'
                protected $afterDelete    = [];

                    protected function initialize(): void
                    {
                        $this->initTranslations();
                    }
                EOT,
            $contents
        );

        $contents = str_replace(
            '$this->initTranslations();',
            '$this->initTranslations(' . $modelTranslation . '::class);',
            $contents
        );

        file_put_contents($file, $contents);
    }

    /**
     * Create a model with translations.
     */
    private function createModelTranslation(string $table): void
    {
        $params = [
            0       => ucfirst(singular($table)) . 'TranslationModel',
            'table' => singular($table) . '_translations',
        ];

        $this->call('make:model', $params);

        $foreignKey = singular($table) . '_id';

        $file     = APPPATH . 'Models' . DIRECTORY_SEPARATOR . $params[0] . '.php';
        $contents = file_get_contents($file);

        $contents = str_replace(
            "protected \$returnType       = 'array'",
            "protected \$returnType       = 'object'",
            $contents
        );

        $contents = str_replace(
            'protected $allowedFields    = [];',
            "protected \$allowedFields    = ['{$foreignKey}', 'locale'];",
            $contents
        );

        file_put_contents($file, $contents);
    }

    /**
     * Create entity.
     */
    private function createEntity(string $table): void
    {
        $params = [
            0 => ucfirst(singular($table)),
        ];

        $this->call('make:entity', $params);

        $file     = APPPATH . 'Entities' . DIRECTORY_SEPARATOR . $params[0] . '.php';
        $contents = file_get_contents($file);

        $contents = str_replace(
            'use CodeIgniter\Entity\Entity;',
            'use CodeIgniter\Entity\Entity;' . "\n" . 'use Michalsn\CodeIgniterTranslatable\Traits\TranslatableEntity;',
            $contents
        );

        $contents = str_replace(
            '{',
            "{\n\tuse TranslatableEntity;\n",
            $contents
        );

        $contents = str_replace(
            "protected \$dates   = ['created_at', 'updated_at', 'deleted_at'];",
            "protected \$dates   = ['created_at', 'updated_at'];",
            $contents
        );

        file_put_contents($file, $contents);
    }

    /**
     * Create a migration file.
     */
    private function createMigration(string $table): void
    {
        $params = [
            0 => $table . 'WithTranslations',
        ];

        $this->call('make:migration', $params);

        $migration = (new MigrationManager())->getLatestMigration();
        $file      = $migration->getPathname();

        $contents = file_get_contents($file);

        $replace = view('\Michalsn\CodeIgniterTranslatable\Commands\Views\migrations_up.tpl.php', [
            'table'             => $table,
            'tableForeignKey'   => singular($table) . '_id',
            'tableTranslations' => singular($table) . '_translations',
        ]);
        $contents = $this->replaceFirst('//', $replace, $contents);

        $replace = view('\Michalsn\CodeIgniterTranslatable\Commands\Views\migrations_down.tpl.php', [
            'table'             => $table,
            'tableTranslations' => singular($table) . '_translations',
        ]);
        $contents = $this->replaceFirst('//', $replace, $contents);

        file_put_contents($file, $contents);
    }

    /**
     * Replace only the first occurrence of a string.
     *
     * @param string $search  The substring to search for.
     * @param string $replace The replacement string.
     * @param string $subject The original string.
     */
    private function replaceFirst(string $search, string $replace, string $subject): string
    {
        $pos = strpos($subject, $search);

        if ($pos === false) {
            return $subject;
        }

        return substr_replace($subject, $replace, $pos, strlen($search));
    }
}
