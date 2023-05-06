<?php

namespace Amohamed\Laravelmodelmaker\Console\Generators;

use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

/**
 * Class MigrationGenerator generates migration files
 * @package Amohamed\Laravelmodelmaker\Console\Commands
 * @Author Abdallah Mohamed
 */
class MigrationGenerator
{
    protected $name;
    protected $fields;

    /**
     * MigrationGenerator constructor.
     * @param $name
     * @param array $fields
     */
    public function __construct($name, $fields = [])
    {
        $this->name = $name;
        $this->fields = $fields;
    }

    /**
     * Generate the migration file and save it to the database/migrations directory
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \RuntimeException
     */
    public function generate()
    {
        $filesystem = new Filesystem();
        $stubPath = __DIR__ . '/../Templates/migration.stub';
        $stubContent = $filesystem->get($stubPath);

        $tableName = Str::snake(Str::pluralStudly($this->name));

        $fields = $this->generateFields();

        $stubContent = str_replace('{{tableName}}', $tableName, $stubContent);
        $stubContent = str_replace('{{fields}}', $fields, $stubContent);

        // Check if a migration file with the same table name already exists
        $migrationsDirectory = database_path('migrations/');
        $existingMigrations = $filesystem->files($migrationsDirectory);

        foreach ($existingMigrations as $existingMigration) {
            if (strpos($existingMigration->getFilename(), "_create_{$tableName}_table.php") !== false) {
                // throw an exception or print an error message here
                throw new \RuntimeException("A migration file for the '{$tableName}' table already exists.");
            }
        }

        $migrationPath = $migrationsDirectory . date('Y_m_d_His') . "_create_{$tableName}_table.php";
        $filesystem->put($migrationPath, $stubContent);
    }

    /**
     * Generate the fields for the migration file
     * @return string
     */
    protected function generateFields()
    {
        $fields = '';
        foreach ($this->fields as $field) {
            list($fieldName, $fieldType) = explode(':', $field);
            $modifiers = explode(':', $field, 3);

            if (isset($modifiers[2])) {
                $modifiers = explode(',', $modifiers[2]);
            } else {
                $modifiers = [];
            }

            if ($fieldType === 'foreignId') {
                $fields .= "            \$table->{$fieldType}('{$fieldName}')->constrained()->cascadeOnDelete();\n";
            } else {
                $fields .= "            \$table->{$fieldType}('{$fieldName}')";
                foreach ($modifiers as $modifier) {
                    if (preg_match('/default\((.+)\)/', $modifier, $matches)) {
                        $defaultValue = $matches[1];
                        $fields .= "->default({$defaultValue})";
                    } else {
                        $fields .= "->{$modifier}()";
                    }
                }
                $fields .= ";\n";
            }
        }
        return $fields;
    }
}
