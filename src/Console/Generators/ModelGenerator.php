<?php

namespace Amohamed\Laravelmodelmaker\Console\Generators;

use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

/**
 * Class ModelGenerator - Generate a model with specified fields
 * @package Amohamed\Laravelmodelmaker\Console\Commands
 * @Author Abdallah Mohamed
 */
class ModelGenerator
{
    protected $name;
    protected $fields;

    /**
     * ModelGenerator constructor.
     * @param $name
     * @param array $fields
     */
    public function __construct($name, $fields = [])
    {
        $this->name = $name;
        $this->fields = $fields;
    }

    /**
     * Generate the model file
     * @return void
     */
    public function generate()
    {
        $filesystem = new Filesystem();
        $stubPath = __DIR__ . '/../Templates/model.stub';
        $stubContent = $filesystem->get($stubPath);

        $modelName = Str::studly($this->name);

        $fillableFields = $this->generateFillable();

        $stubContent = str_replace('{{modelName}}', $modelName, $stubContent);
        $stubContent = str_replace('{{fillableFields}}', $fillableFields, $stubContent);

        $modelPath = app_path('Models/' . $modelName . '.php');
        $filesystem->put($modelPath, $stubContent);
    }

    /**
     * Generate the fillable fields for the model
     * @return string
     */
    protected function generateFillable()
    {
        $fillableFields = '';
        $count = count($this->fields);
        foreach ($this->fields as $index => $field) {
            $fieldComponents = explode(':', $field);
            $fieldName = $fieldComponents[0];
            $comma = $index < $count - 1 ? ',' : '';
            $fillableFields .= "\n        '{$fieldName}'{$comma}";
        }

        return $fillableFields;
    }
}
