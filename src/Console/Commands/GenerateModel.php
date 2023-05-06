<?php

namespace Amohamed\Laravelmodelmaker\Console\Commands;

use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Amohamed\Laravelmodelmaker\Console\Generators\ModelGenerator;
use Amohamed\Laravelmodelmaker\Console\Generators\MigrationGenerator;
use Amohamed\Laravelmodelmaker\Console\Generators\RelationshipGenerator;

/**
 * Class GenerateModel - Generate a model with specified fields and migration
 * @package Amohamed\Laravelmodelmaker\Console\Commands
 * @Author Abdallah Mohamed
 */
class GenerateModel extends Command
{
    protected $signature = 'atm:generate-model {name} {--fields=} {--hasMany=} {--belongsTo=} {--hasOne=} {--belongsToMany=}';

    protected $description = 'Generate a model with specified fields, migration and relationships';

    /**
     * GenerateModel constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all the existing models in the project
     * @return array
     */
    public function handle()
    {
        $name = $this->argument('name');
        $fields = $this->option('fields') ? explode(',', $this->option('fields')) : [];

        $modelGenerator = new ModelGenerator($name, $fields);
        $modelGenerator->generate();

        $migrationGenerator = new MigrationGenerator($name, $fields);
        $migrationGenerator->generate();

        $this->newLine();

        $this->components->twoColumnDetail('<fg=green>Model name</>', $name);

        $this->components->twoColumnDetail('<fg=green>Fields</>', implode(', ', $fields));

        $this->newLine();

        $this->info('You have successfully created a model and migration for ' . $name . '.');

        $this->newLine();

        // Ask the user if they want to create relationships
        $generateRelationshipsForAnotherModel = $this->choice('Would you like to generate relationships for this model '. $name .'?', ['yes', 'no'], 1) === 'yes';
        if ($generateRelationshipsForAnotherModel) {
            $generateRelationshipsForAnotherModel = $this->generateRelationships($name);
        }

        // Keep asking if the user wants to create relationships for other models
        while (isset($generateRelationshipsForAnotherModel) && $generateRelationshipsForAnotherModel) {
            $existingModels = $this->getExistingModels();
            $indexedModels = [];

            $this->info('Existing models in the project:');
            foreach ($existingModels as $index => $model) {
                $indexedModels[$index + 1] = $model;
                $this->info(($index + 1) . " => {$model}");
            }

            $this->newLine();
            $validModelIndex = false;
            while (!$validModelIndex) {
                $chosenModelIndex = $this->ask('Choose one of the existing models by entering the corresponding number');
                if (isset($indexedModels[$chosenModelIndex])) {
                    $validModelIndex = true;
                } else {
                    $this->error("Invalid model index. Please enter a valid number.");
                }
            }
            $chosenModel = $indexedModels[$chosenModelIndex];
            $generateRelationshipsForAnotherModel = $this->generateRelationships($chosenModel);
        }
    }

    /**
     * Get existing models in the project
     * @return array
     *
     */
    protected function generateRelationships($modelName)
    {
        $availableRelationships = [
            '1' => 'hasMany',
            '2' => 'belongsTo',
            '3' => 'hasOne',
            '4' => 'belongsToMany',
        ];
    
        $this->info('Choose the type of relationship you want to generate for ' . $modelName . '.');
        $this->newLine();
        foreach ($availableRelationships as $key => $relationship) {
            $this->info("{$key} => {$relationship}");
        }
    
        $selectedRelationships = $this->ask('Select the relationships you want to generate for this model. You can specify multiple relationships by separating them with a comma.');
    
        $selectedRelationships = explode(',', $selectedRelationships);
    
        $relationships = [];
    
        foreach ($selectedRelationships as $relationshipKey) {
            if (!isset($availableRelationships[$relationshipKey])) {
                $this->error("Invalid relationship option. Please try again.");
                return $this->generateRelationships($modelName);
            }
    
            $relationship = $availableRelationships[$relationshipKey];
    
            $existingModels = $this->getExistingModels();
            $indexedModels = [];
    
            $this->info('Please choose the model for the ' . $relationship . ' relationship.');
            
            $this->newLine();
    
            foreach ($existingModels as $index => $model) {
                $indexedModels[$index + 1] = $model;
                $this->info(($index + 1) . " => {$model}");
            }
    
            $this->newLine();
            $validModelIndex = false;
            while (!$validModelIndex) {
                $chosenModelIndex = $this->ask("Choose one of the existing models for the '{$relationship}' relationship by entering the corresponding number");
                if (isset($indexedModels[$chosenModelIndex])) {
                    $validModelIndex = true;
                } else {
                    $this->error("Invalid model index. Please enter a valid number.");
                }
            }
            $relatedModel = $indexedModels[$chosenModelIndex];
    
            // Check if the relationship exists
            $relationshipExists = $this->relationshipExists($modelName, $relationship, $relatedModel);
    
            if ($relationshipExists) {
                // Inform the user that the relationship already exists
                $this->warn("The '{$relationship}' relationship with '{$relatedModel}' already exists in the '{$modelName}' model.");
                continue;
            }
    
            if (!isset($relationships[$relationship])) {
                $relationships[$relationship] = [];
            }
            $relationships[$relationship][] = $relatedModel;

            if ($modelName === $relatedModel) {
                $this->error("You cannot create a relationship between the model and itself. Please choose a different model.");
                continue;
            }
        }
    
        $relationshipGenerator = new RelationshipGenerator($modelName, $relationships);
        $relationshipGenerator->generate();
        $this->newLine();
    
        // if the user wants to end the process, return false
        if ($this->choice('Would you like to generate relationships for another model?', ['yes', 'no'], 1) === 'no') {
    
            $this->info('Relationships created successfully.');
    
            $this->newLine();
    
            $this->writeWelcomeMessage();
    
            return false;
        }
    
        // If the user wants to continue generating relationships, return true
        return true;
    }
    


    protected function getExistingModels()
    {
        $modelsPath = app_path('Models');
        $files = scandir($modelsPath);
        $models = [];

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $models[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }

        return $models;
    }

    protected function writeWelcomeMessage()
    {
        $asciiLogo = <<<EOT
                            <options=bold><fg=bright-magenta>
                             _                               _   __  __           _      _   __  __       _
                            | |                             | | |  \/  |         | |    | | |  \/  |     | |            
                            | |     __ _ _ __ __ ___   _____| | | \  / | ___   __| | ___| | | \  / | __ _| | _____ _ __ 
                            | |    / _` | '__/ _` \ \ / / _ \ | | |\/| |/ _ \ / _` |/ _ \ | | |\/| |/ _` | |/ / _ \ '__|
                            | |___| (_| | | | (_| |\ V /  __/ | | |  | | (_) | (_| |  __/ | | |  | | (_| |   <  __/ |   
                            |______\__,_|_|  \__,_| \_/ \___|_| |_|  |_|\___/ \__,_|\___|_| |_|  |_|\__,_|_|\_\___|_|  
                            
                            </></>
                        EOT;

        $this->line("\n" . $asciiLogo . "\n");
        $this->line("\n<options=bold>Congratulations, you've created your first Laravel Model!</> *\\(^_^)/*\n");

        if ($this->choice('Would you like to show some love by starring the repo?', ['yes', 'no'], 1) === 'yes') {

            if (PHP_OS_FAMILY == 'Darwin') exec('open https://github.com/Abdallah-Tah/laravelmodelmaker');
            if (PHP_OS_FAMILY == 'Windows') exec('start https://github.com/Abdallah-Tah/laravelmodelmaker');
            if (PHP_OS_FAMILY == 'Linux') exec('xdg-open https://github.com/Abdallah-Tah/laravelmodelmaker');

            $this->line("Thanks! Means the world to me!");
        }
    }

    /**
     * Check if the specified relationship exists in the given model
     *
     * @param string $modelName
     * @param string $relationship
     * @param string $relatedModel
     * @return bool
     */
    protected function relationshipExists($modelName, $relationshipMethod, $relatedModel)
    {
        $fullyQualifiedModelName = "App\Models\\" . $modelName;

        if (!class_exists($fullyQualifiedModelName)) {
            return false;
        }

        $filePath = app_path('Models/' . $modelName . '.php');
        $content = file_get_contents($filePath);

        $functionNames = [
            lcfirst($relatedModel),
            ucfirst($relatedModel),
            Str::camel($relatedModel),
        ];

        foreach ($functionNames as $functionName) {

            if ($relationshipMethod == 'belongsToMany' || $relationshipMethod == 'hasMany') {
                $functionName = Str::plural($functionName);
            }

            if (strpos($content, $functionName) !== false && strpos($content, $relationshipMethod) !== false) {
                return true;
            }
        }

        return false;
    }
}
