<?php

namespace Polarwhite\LaravelGptArchitect\Console\Commands;

use Illuminate\Console\Command;

class GenerateGptResourceCommand extends Command
{
    private string $contents = '';
    protected $signature = 'make:gpt-plan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = config('gpt-architect.output', base_path('gpt-plan.txt'));
        $projectName = config('gpt-architect.project_name', 'Default Project Name');
        $projectDescription = config('gpt-architect.project_description', 'Default Project Description');

        $this->info('Generating ' . $filePath);
        $this->info('This may take a few minutes...');

        // Project info header
        $this->contents = "/**\n";
        $fileRevisionDate = now()->format('Y-m-d h:i a');
        $this->contents .= " * File Revision Date: {$fileRevisionDate}\n";
        if($projectName || $projectDescription) {
            if($projectName) {
                $this->contents .= "* Project: $projectName\n";
            }
            if($projectDescription) {
                $this->contents .= " * Description: $projectDescription\n";
            }
        }
        $this->contents .= "*/\n\n";


        // Header
        $this->contents .= "/**\n";
        $this->contents .= " * AUTO-GENERATED FILE FOR GPT RESOURCE\n";
        $this->contents .= " * This file contains structured data about the database schema, models, and services of the application.\n";
        $this->contents .= " * It is used for feeding into a custom GPT model to assist in understanding the application structure.\n";
        $this->contents .= " */\n\n";

        // Database Structure
        if(config('gpt-architect.include.tables')) {
            $this->writeDatabaseDescriptions();
        }

        // Models
        if(config('gpt-architect.include.models')) {
            $this->writeModelDescriptions();
        }

        // Services
        if(config('gpt-architect.include.services')) {
            $this->writeServiceDescriptions();
        }

        // Composer Packages
        if(config('gpt-architect.include.composer')) {
            $this->writeComposerPackages();
        }

        // NPM Packages
        if(config('gpt-architect.include.npm')) {
            $this->writeNpmPackages();
        }

        // 4. Output File
        file_put_contents($filePath, $this->contents);
    }

    # Tables
    private function writeDatabaseDescriptions()
    {
        $this->contents .= "/*\n";
        $this->contents .= " * DATABASE STRUCTURE\n";
        $this->contents .= " * The following interfaces represent the tables in the database along with their column definitions.\n";
        $this->contents .= " * Each interface name corresponds to a table, and its properties represent the table's columns.\n";
        $this->contents .= " */\n";

        $schemaManager = \DB::connection()->getDoctrineSchemaManager();

        $tables = $schemaManager->listTableNames();

        foreach ($tables as $tableName) {
            $this->contents .= "interface " . ucfirst($tableName) . " {\n";

            $columns = $schemaManager->listTableColumns($tableName);

            foreach ($columns as $column) {
                $type = $column->getType()->getName();
                $nullable = !$column->getNotnull();
                $typeString = $type;
                if($nullable) {
                    $typeString .= '|null';
                }
                $this->contents .= "\t" . $column->getName() . ": " . $typeString . ";\n";
            }

            $this->contents .= "}\n\n";
        }
    }

    private function mapToTypeScriptType($type, $nullable)
    {
        // Map database types to TypeScript types
        $typeScriptType = 'any';

        if (preg_match('/int|bigint|smallint|mediumint|tinyint|decimal|float|double/', $type)) {
            $typeScriptType = 'number';
        } elseif (preg_match('/bool|boolean/', $type)) {
            $typeScriptType = 'boolean';
        } elseif (preg_match('/char|varchar|text|longtext|mediumtext|tinytext|enum|set/', $type)) {
            $typeScriptType = 'string';
        } elseif (preg_match('/date|datetime|timestamp|time/', $type)) {
            $typeScriptType = 'Date';
        }

        // Adjust for nullable fields
        return $nullable ? $typeScriptType . ' | null' : $typeScriptType;
    }

    # Models
    private function writeModelDescriptions()
    {
        $this->contents .= "/*\n";
        $this->contents .= " * MODELS\n";
        $this->contents .= " * This section lists the Eloquent models used in the application along with their relationships and custom field castings.\n";
        $this->contents .= " */\n";

        $modelPath = app_path('Models');
        $modelFiles = \File::allFiles($modelPath);

        foreach ($modelFiles as $file) {
            $modelName = $file->getFilenameWithoutExtension();
            $fullModelName = 'App\\Models\\' . $modelName;

            if (!class_exists($fullModelName)) {
                continue;
            }

            $reflectionClass = new \ReflectionClass($fullModelName);
// Get table name
            $tableName = $reflectionClass->getDefaultProperties()['table'] ?? $this->defaultTableName($modelName);
            // Add a section header for the model
            $this->contents .= "/*\n";
            $this->contents .= " * Model: $modelName\n";
            $this->contents .= " * Path: " . $file->getRealPath() . "\n";
            $this->contents .= " * Table: " . $tableName . "\n";
            $this->contents .= " */\n";

            // Write custom field casting
            $this->writeModelCasting($reflectionClass);

            // Write relationships
            $this->writeModelRelationships($reflectionClass);

            // Write other methods
            $this->writeModelMethods($reflectionClass);
        }
    }
    private function writeModelCasting(\ReflectionClass $reflectionClass)
    {
        $defaultProperties = $reflectionClass->getDefaultProperties();
        if (isset($defaultProperties['casts'])) {
            $casts = $defaultProperties['casts'];
            $this->contents .= " * Custom Casts:\n";
            foreach ($casts as $field => $type) {
                $this->contents .= " * - $field: $type\n";
            }
            $this->contents .= "\n";
        }
    }

    private function defaultTableName($modelName)
    {
        // Convert the model name from StudlyCase to snake_case and pluralize
        return \Str::plural(\Str::snake($modelName));
    }

    private function writeModelRelationships(\ReflectionClass $reflectionClass)
    {
        $this->contents .= " * Relationships:\n";
        foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (!$this->isRelationshipMethod($method)) {
                continue;
            }

            $returnType = (string) $method->getReturnType();
            $this->contents .= " * - " . $method->getName() . ": " . $returnType . "\n";
        }
        $this->contents .= "\n";
    }

    private function isRelationshipMethod(\ReflectionMethod $method)
    {
        // This function needs to check if the method is a relationship method
        // A basic implementation would check if the return type is one of the Eloquent relationship types
        // Adjust this logic based on your project's implementation of relationships
        $relationshipTypes = [
            'Illuminate\Database\Eloquent\Relations\HasOne',
            'Illuminate\Database\Eloquent\Relations\HasMany',
            'Illuminate\Database\Eloquent\Relations\BelongsTo',
            'Illuminate\Database\Eloquent\Relations\BelongsToMany',
            'Illuminate\Database\Eloquent\Relations\HasOneThrough',
            'Illuminate\Database\Eloquent\Relations\HasManyThrough',
            // add other relationship types as needed
        ];

        $returnType = $method->getReturnType();
        return $returnType && in_array((string) $returnType, $relationshipTypes, true);
    }

    private function writeModelMethods(\ReflectionClass $reflectionClass)
    {
        $this->contents .= " * Other Methods:\n";
        foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($this->isRelationshipMethod($method) || $method->class !== $reflectionClass->getName()) {
                continue;
            }

            // Optionally, include parameter and return type info
            $params = array_map(function ($param) {
                return '$' . $param->getName();
            }, $method->getParameters());

            $returnType = $method->getReturnType() ? ': ' . (string) $method->getReturnType() : '';

            $this->contents .= " * - " . $method->getName() . "(" . implode(', ', $params) . ")" . $returnType . "\n";
        }
        $this->contents .= "\n";
    }

    # Services
    private function writeServiceDescriptions()
    {
        $this->contents .= "/*\n";
        $this->contents .= " * SERVICES\n";
        $this->contents .= " * Below are the classes in the App/Services directory, detailing their properties, methods, signatures, and return types.\n";
        $this->contents .= " */\n";

        $servicePath = app_path('Services');
        $serviceFiles = \File::allFiles($servicePath);

        foreach ($serviceFiles as $file) {
            $serviceName = $file->getFilenameWithoutExtension();
            $fullServiceName = 'App\\Services\\' . $serviceName;

            if (!class_exists($fullServiceName)) {
                continue;
            }

            $reflectionClass = new \ReflectionClass($fullServiceName);

            // Add a section header for the service
            $this->contents .= "/*\n";
            $this->contents .= " * Service: $serviceName\n";
            $this->contents .= " * Path: " . $file->getRealPath() . "\n";
            $this->contents .= " */\n";

            // Write properties
            $this->writeServiceProperties($reflectionClass);

            // Write methods
            $this->writeServiceMethods($reflectionClass);
        }
    }

    private function writeServiceProperties(\ReflectionClass $reflectionClass)
    {
        $this->contents .= " * Properties:\n";
        foreach ($reflectionClass->getProperties() as $property) {
            $docComment = $property->getDocComment() ? : 'No description';
            $this->contents .= " * - " . $property->getName() . ": " . $docComment . "\n";
        }
        $this->contents .= "\n";
    }

    private function writeServiceMethods(\ReflectionClass $reflectionClass)
    {
        $this->contents .= " * Methods:\n";
        foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            // Optionally, include parameter and return type info
            $params = array_map(function ($param) {
                return '$' . $param->getName();
            }, $method->getParameters());

            $returnType = $method->getReturnType() ? ': ' . (string) $method->getReturnType() : '';
            $docComment = $method->getDocComment() ? : 'No description';

            $this->contents .= " * - " . $method->getName() . "(" . implode(', ', $params) . ")" . $returnType . " " . $docComment . "\n";
        }
        $this->contents .= "\n";
    }


    # Packages
    private function writeComposerPackages()
    {
        $composerPath = base_path('composer.json');

        if (file_exists($composerPath)) {
            $composerJson = json_decode(file_get_contents($composerPath), true);

            $this->contents .= "/**\n";
            $this->contents .= " * COMPOSER PACKAGES\n";
            $this->contents .= " * Lists all Composer dependencies used in the project.\n";
            $this->contents .= " */\n";

            foreach ($composerJson['require'] as $package => $version) {
                $this->contents .= " * - $package: $version\n";
            }

            $this->contents .= "\n";
        }
    }


    private function writeNpmPackages()
    {
        $packagePath = base_path('package.json');

        if (file_exists($packagePath)) {
            $packageJson = json_decode(file_get_contents($packagePath), true);

            $this->contents .= "/**\n";
            $this->contents .= " * NPM PACKAGES\n";
            $this->contents .= " * Lists all NPM packages (dependencies and devDependencies) used in the project.\n";
            $this->contents .= " */\n";

            foreach (['dependencies', 'devDependencies'] as $section) {
                if (isset($packageJson[$section])) {
                    $this->contents .= " * $section:\n";
                    foreach ($packageJson[$section] as $package => $version) {
                        $this->contents .= " * - $package: $version\n";
                    }
                }
            }

            $this->contents .= "\n";
        }
    }
}