<?php

return [
    /*
     * The path to the the generated file.
     */
    'output' => base_path('gpt-plan.txt'),

    /*
     * The name of the project that will be included in the generated GPT instructions
     */
    'project_name' => null,

    /*
     * A description of the project that will be included in the generated GPT instructions
     * file.  This should be a short description of the project and can contain any custom
     * instructions that you'd like to include for the GPT to parse.
     */
    'project_description' => null,

    /*
     * Included Modules
     */
    'include' => [
        /*
         * Export a description of your database
         */
        'tables' => true,

        /*
         * Export a description of your models
         */
        'models' => true,

        /*
         * Export a description of your files in App\Services, if any
         */
        'services' => true,

        /*
         * Export a description of your composer.json file
         */
        'composer' => true,

        /*
         * Export a description of your package.json file
         */
        'npm' => true,
    ]
];