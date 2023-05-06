<?php

namespace Amohamed\Laravelmodelmaker\Console\Generators;

use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

/**
 * Class RelationshipGenerator - Generate relationships for a model
 * @package Amohamed\Laravelmodelmaker\Console\Commands
 * @Author Abdallah Mohamed
 */
class RelationshipGenerator
{
    protected $name;
    protected $relationships;

    /**
     * RelationshipGenerator constructor.
     * @param $name
     * @param array $relationships
     */
    public function __construct($name, $relationships = [])
    {
        $this->name = $name;
        $this->relationships = $relationships;
    }

    /**
     * Generate the relationship methods for the model
     * @return void
     */
    public function generate()
    {
        $filesystem = new Filesystem();
        $modelName = Str::studly($this->name);
        $modelPath = app_path('Models/' . $modelName . '.php');
        $modelContent = $filesystem->get($modelPath);

        $stubPath = __DIR__ . '/../Templates/relationship.stub';
        $stubContent = $filesystem->get($stubPath);

        $relationshipMethods = $this->generateRelationshipMethods($stubContent);

        // Find the position of the last closing bracket
        $lastBracketPosition = strrpos($modelContent, '}');

        // Insert the relationship methods before the last closing bracket
        $modelContent = substr_replace($modelContent, $relationshipMethods . "\n", $lastBracketPosition, 0);

        $filesystem->put($modelPath, $modelContent);
    }


    /**
     * Generate a relationship method
     * @param $stubContent
     * @param $type
     * @param $relation
     * @param $modelName
     * @return string
     */
    protected function generateRelationshipMethods($stubContent)
    {
        $relationshipMethods = [];

        foreach ($this->relationships as $type => $relations) {
            foreach ($relations as $relation) {
                $relationshipName = Str::camel($relation);

                // Pluralize relationship names for hasMany and belongsToMany relationships
                if ($type === 'hasMany' || $type === 'belongsToMany') {
                    $relationshipName = Str::plural($relationshipName);
                }

                $relationshipType = $type;
                $relatedModel = Str::studly($relation);
                $foreignKeys = '';

                if ($type === 'belongsTo' || $type === 'belongsToMany') {
                    $foreignKeys = ", '" . Str::snake($relatedModel) . "_id'";
                }

                $relationshipMethod = str_replace('{{relationshipName}}', $relationshipName, $stubContent);
                $relationshipMethod = str_replace('{{relationshipType}}', $relationshipType, $relationshipMethod);
                $relationshipMethod = str_replace('{{relatedModel}}', $relatedModel, $relationshipMethod);
                $relationshipMethod = str_replace('{{foreignKeys}}', $foreignKeys, $relationshipMethod);

                $relationshipMethods[] = "\n" . $relationshipMethod;
            }
        }

        // Format the relationship methods string without the closing bracket
        $formattedRelationshipMethods = implode('', $relationshipMethods);
        $formattedRelationshipMethods = rtrim($formattedRelationshipMethods);

        return $formattedRelationshipMethods;
    }
}
