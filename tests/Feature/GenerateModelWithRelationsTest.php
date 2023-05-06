<?php

namespace Amohamed\Laravelmodelmaker\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Amohamed\Laravelmodelmaker\Tests\TestCase;

class GenerateModelWithRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_model_with_relationships()
    {
        // Run the artisan command to generate the model with relationships
        $this->artisan('atm:generate-model-with-relations TestModel --hasMany=TestModel2 --belongsTo=TestModel3 --hasOne=TestModel4 --belongsToMany=TestModel5');

        // Check if the model file exists
        $this->assertFileExists(app_path('Models/TestModel.php'));

        // Perform any other relevant assertions for your package's functionality
    }
}
