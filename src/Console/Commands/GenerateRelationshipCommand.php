<?php

namespace Amohamed\Laravelmodelmaker\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Amohamed\Laravelmodelmaker\Console\Generators\RelationshipGenerator;


/**
 * Class GenerateRelationshipCommand - Generate relationships for an existing model
 * @package Amohamed\Laravelmodelmaker\Console\Commands
 * @Author Abdallah Mohamed
 */
class GenerateRelationshipCommand extends Command
{
    protected $signature = 'atm:generate-relationship';

    protected $description = 'Generate relationships for an existing model';

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
     *
     */
    public function handle()
    {
        $existingModels = $this->getExistingModels();
        $indexedModels = [];

        $this->newLine();

        if (empty($existingModels)) {
            $this->error('No models found in the project. Please create a model first.');
            return;
        }

        $this->newLine();

        $this->info('Select the model you want to create a relationship for:');

        $this->newLine();

        foreach ($existingModels as $index => $model) {
            $modelName = Str::studly($model);
            $indexedModels[$index + 1] = $modelName;
            $this->info(($index + 1) . " => {$modelName}");
        }


        $this->newLine();
        $validModelIndex = false;
        while (!$validModelIndex) {
            $chosenModelIndex = $this->ask('Choose one of the existing models by entering the corresponding number');
            $this->newLine();
            if (isset($indexedModels[$chosenModelIndex])) {
                $validModelIndex = true;
            } else {
                $this->error("Invalid model index. Please enter a valid number.");
            }
        }
        $chosenModel = $indexedModels[$chosenModelIndex];

        $name = $chosenModel;

        $this->info("You have chosen {$name} model.");

        $this->newLine();

        $this->components->twoColumnDetail('<fg=green>Model name</>', $name);

        $this->newLine();

        $this->info('Model found successfully.');

        $this->newLine();

        // Initialize the variable to prevent the error
        $generateRelationshipsForAnotherModel = false;

        // Ask the user if they want to create relationships
        if ($this->choice('Would you like to generate relationships for this model?', ['yes', 'no'], 1) === 'yes') {
            $generateRelationshipsForAnotherModel = $this->generateRelationships($name);
        }

        // Keep asking if the user wants to create relationships for other models
        while ($generateRelationshipsForAnotherModel) {
            $existingModels = $this->getExistingModels();
            $indexedModels = [];

            $this->info('Select the model you want to create a relationship for:');
            foreach ($existingModels as $index => $model) {
                $modelName = Str::studly($model);
                $indexedModels[$index + 1] = $modelName;
                $this->info(($index + 1) . " => {$modelName}");
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
     * Get existing models
     *
     * @return array
     */
    protected function generateRelationships($chosenModel)
    {
        $availableRelationships = [
            '1' => 'hasMany',
            '2' => 'belongsTo',
            '3' => 'hasOne',
            '4' => 'belongsToMany',
        ];
    
        $this->info('Available relationship options:');
        $this->newLine();
        foreach ($availableRelationships as $key => $relationship) {
            $this->info("{$key} => {$relationship}");
        }
    
        $selectedRelationships = $this->ask('Select the relationships you want to generate for this model '. $chosenModel.'. You can specify multiple relationships by separating them with a comma.');
    
        $selectedRelationships = explode(',', $selectedRelationships);
    
        $relationships = [];
    
        foreach ($selectedRelationships as $relationshipKey) {
            if (!isset($availableRelationships[$relationshipKey])) {
                $this->error("Invalid relationship option. Please try again.");
                return $this->generateRelationships($chosenModel);
            }
    
            $relationship = $availableRelationships[$relationshipKey];
    
            $existingModels = $this->getExistingModels();
            $indexedModels = [];
    
            $this->info('Please choose the model for the ' . $relationship . ' relationship.');
    
            $this->newLine();
    
            foreach ($existingModels as $index => $model) {
                $modelName = Str::studly($model);
                $indexedModels[$index + 1] = $modelName;
                $this->info(($index + 1) . " => {$modelName}");
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
    
            if ($chosenModel == $relatedModel) {
                $this->error("You cannot create a relationship between the model and itself. Please choose a different model.");
                continue;
            }
    
            // Check if the relationship exists
            $relationshipExists = $this->relationshipExists($chosenModel, $relationship, $relatedModel);
    
            if ($relationshipExists) {
                // Inform the user that the relationship already exists
                $this->warn("The '{$relationship}' relationship with '{$relatedModel}' already exists in the '{$chosenModel}' model.");
                continue;
            }
    
            if (!isset($relationships[$relationship])) {
                $relationships[$relationship] = [];
            }
            $relationships[$relationship][] = $relatedModel;
        }
    
        $relationshipGenerator = new RelationshipGenerator($chosenModel, $relationships);
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
    

    /**
     * Get all the existing models
     *
     * @return array
     */
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

        return array_map('strtolower', $models);
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
        $fullyQualifiedRelatedModelName = "App\Models\\" . $relatedModel;

        if (!class_exists($fullyQualifiedRelatedModelName)) {
            return false;
        }

        $filePath = app_path('Models/' . $relatedModel . '.php');
        $content = file_get_contents($filePath);

        $functionNames = [
            lcfirst($modelName),
            ucfirst($modelName),
            Str::camel($modelName),
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

    /**
     * Check if the specified model exists
     *
     * @param string $modelName
     * @return bool
     */
    protected function validateModelName($modelName)
    {
        $modelsPath = app_path('Models');
        $files = scandir($modelsPath);
        $models = [];

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $models[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }

        return in_array($modelName, $models);
    }

    /**
     * Write the welcome message to the console
     *
     * @return void
     */
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
}
