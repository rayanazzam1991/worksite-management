<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateCAStructureCmd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:fca {module} {feature}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new CA feature directory structure';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $feature = $this->argument('feature');
        $module = $this->argument('module');
        $basePath = 'Modules/'.$module.'/src/'.$feature;

        // Application
        $applicationStructure = [
            'DTO',
            'Mappers',
            'Filter',
            'Exceptions',
            'Helpers',
            'Enums',
            'Jobs',
            'Providers',
            'Repositories',
            'UseCases',
        ];
        foreach ($applicationStructure as $applicationDirectory) {
            File::makeDirectory($basePath.'/Application/'.$applicationDirectory, 0755, true);
            touch($basePath.'/Application/'.$applicationDirectory.'/.gitkeep');
        }

        // Domain
        $domainStructure = [
            'Entities',
            'Factories',
            'Events',
            'ValueObjects',
            'Services',
        ];
        foreach ($domainStructure as $domainDirectory) {
            $path = $basePath.'/Domain/'.$domainDirectory;
            File::makeDirectory($path, 0755, true);
            touch($basePath.'/Domain/'.$domainDirectory.'/.gitkeep');
        }
        //                if ($domainDirectory == 'Model') {
        //                    $stub = File::get('./stubs/DomainModel.stub');
        //                    $stubReplace = [
        //                        '**BoundedContext**' => $feature,
        //                    ];
        //                    $file = strtr($stub, $stubReplace);
        //                    File::put($basePath . '/Domain/Entities/' . $feature . '.php', $file);
        //                }

        // Infrastructure
        $infrastructureStructure = [
            'Repository',
        ];

        foreach ($infrastructureStructure as $infrastructureDirectory) {
            File::makeDirectory($basePath.'/Infrastructure/'.$infrastructureDirectory, 0755, true);
            touch($basePath.'/Infrastructure/'.$infrastructureDirectory.'/.gitkeep');
        }

        //        // Eloquent
        //        $infrastructureEloquentStructure = [
        //            'Model',
        //            'Trait',
        //            'Repository',
        //        ];
        //        foreach ($infrastructureEloquentStructure as $infrastructureEloquentDirectory) {
        //            File::makeDirectory($basePath.'/Infrastructure/Eloquent/'.$infrastructureEloquentDirectory, 0755, true);
        //            touch($basePath.'/Infrastructure/Eloquent/'.$infrastructureEloquentDirectory.'/.gitkeep');
        //        }

        // Presentation
        $presentationStructure = [
            'Presenters',
            'ViewModels',
        ];
        foreach ($presentationStructure as $presentationDirectory) {
            File::makeDirectory($basePath.'/Presentation/'.$presentationDirectory, 0755, true);
            touch($basePath.'/Presentation/'.$presentationDirectory.'/.gitkeep');
        }
        echo 'Bounded Context created successfully';

        return 1;
    }
}
