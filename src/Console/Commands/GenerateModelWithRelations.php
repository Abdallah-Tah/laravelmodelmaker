<?php

namespace Amohamed\Laravelmodelmaker\Console\Commands;

use Illuminate\Console\Command;
use Amohamed\Laravelmodelmaker\Console\Generators\ModelGenerator;
use Amohamed\Laravelmodelmaker\Console\Generators\RelationshipGenerator;

/**
 * Class GenerateModelWithRelations - Generate a model with specified relationships
 * @package Amohamed\Laravelmodelmaker\Console\Commands
 * @Author Abdallah Mohamed
 */
class GenerateModelWithRelations extends Command
{
    protected $signature = 'atm:generate-model-with-relations {name} {--hasMany=} {--belongsTo=} {--hasOne=} {--belongsToMany=}';

    protected $description = 'Generate a model with specified relationships';

    /**
     * GenerateModelWithRelations constructor.
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
        $hasMany = $this->option('hasMany') ? explode(',', $this->option('hasMany')) : [];
        $belongsTo = $this->option('belongsTo') ? explode(',', $this->option('belongsTo')) : [];
        $hasOne = $this->option('hasOne') ? explode(',', $this->option('hasOne')) : [];
        $belongsToMany = $this->option('belongsToMany') ? explode(',', $this->option('belongsToMany')) : [];

        $relationships = [
            'hasMany' => $hasMany,
            'belongsTo' => $belongsTo,
            'hasOne' => $hasOne,
            'belongsToMany' => $belongsToMany,
        ];

        $modelGenerator = new ModelGenerator($name);
        $modelGenerator->generate();

        $relationshipGenerator = new RelationshipGenerator($name, $relationships);
        $relationshipGenerator->generate();

        $this->info('Model created with relationships.');
    }
}
