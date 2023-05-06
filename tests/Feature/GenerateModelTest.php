<?php

namespace Amohamed\Laravelmodelmaker\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Amohamed\Laravelmodelmaker\Tests\TestCase;

class GenerateModelTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->cleanUpMigrationFiles();
    }

    public function tearDown(): void
    {
        $this->cleanUpMigrationFiles();
        parent::tearDown();
    }

    protected function cleanUpMigrationFiles()
    {
        $migrationFiles = glob(base_path('database/migrations/*_create_test_models_table.php'));
        foreach ($migrationFiles as $file) {
            unlink($file);
        }
    }

    public function test_generate_model_and_migration_with_relationships()
    {
        // Run the artisan command to generate the model and migration with relationships
        $this->artisan('atm:generate-model TestModel --fields=name:string,age:integer')
            ->expectsQuestion('Would you like to generate relationships for this model?', 'yes')
            ->expectsQuestion('Select the relationships you want to generate for this model. You can specify multiple relationships by separating them with a comma.', '1,2')
            ->expectsQuestion('Enter the related model for the \'hasMany\' relationship:', 'RelatedModel1')
            ->expectsQuestion('Enter the related model for the \'belongsTo\' relationship:', 'RelatedModel2')
            ->expectsQuestion('Choose one of the existing models by entering the corresponding number:', '1')
            ->expectsQuestion('Would you like to generate relationships for another model?', 'yes')
            ->run();

        
    
        // Check if the model file exists
        $this->assertFileExists(base_path('app/Models/TestModel.php'));
    
        // Check if the migration file exists
        $migrationFiles = glob(base_path('database/migrations/*_create_test_models_table.php'));
        $this->assertNotEmpty($migrationFiles, 'Migration file not found.');
    
        // Use the first found migration file for further assertions
        $migrationFile = $migrationFiles[0];
    
        // Check if the model has the correct fields
        $modelContent = file_get_contents(base_path('app/Models/TestModel.php'));
        $this->assertStringContainsString("'name',", $modelContent);
        $this->assertStringContainsString("'age',", $modelContent);
    
        // Check if the model has the correct relationships
        $this->assertStringContainsString("public function comments()", $modelContent);
        $this->assertStringContainsString("return \$this->hasMany(Comment::class);", $modelContent);
        $this->assertStringContainsString("public function user()", $modelContent);
        $this->assertStringContainsString("return \$this->belongsTo(User::class);", $modelContent);
    
        // Check if the migration has the correct fields
        $migrationContent = file_get_contents($migrationFile);
        $this->assertStringContainsString("\$table->string('name');", $migrationContent);
        $this->assertStringContainsString("\$table->integer('age');", $migrationContent);
    }
    

}
