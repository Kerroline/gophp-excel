<?php

namespace Kerroline\PhpGoExcel\Commands;

use Illuminate\Console\GeneratorCommand;

class SetupCommand extends GeneratorCommand
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'php-go-excel:setup';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Install model required ...';

  /**
   * The type of class being generated.
   *
   * @var string
   */
  protected $type = 'DataLog Files';



  /**
   * Execute the console command.
   *
   * @return bool|null
   */
  // public function handle()
  // {
  //   $stubsList = [
  //     'Log'                       => 'models/log.stub',
  //     'LogEvent'                  => 'models/logevent.stub',
  //     'Loggable'                  => 'models/loggable.stub',
  //     'DescriptionLogConstructor' => 'log-constructors/description.stub'
  //   ];


  //   $this->info('Welcome to the Tridmedia Logging config generator');


  //   $this->callSilent('vendor:publish', ['--provider' => 'Tridmedia\Logging\LoggingServiceProvider']);

  //   $this->info('Publish config and migrations: successful');


  //   foreach ($stubsList as $stubName => $stubPath) {

  //     $qualifyName = $this->qualifyClass($stubName);

  //     if ($this->alreadyExists($qualifyName)) {
  //       $this->error($qualifyName . ' already exists!');
  //       return false;
  //     }


  //     $this->buildStub($qualifyName, $stubPath);

  //     $this->info($qualifyName . ' created successfully.');
  //   }

  //   $this->info($this->type . ' created successfully.');
  // }

  // protected function buildStub($stubName, $stubPath)
  // {
  //   $this->makeDirectory($this->getPath($stubName));

  //   $stubFile = $this->files->get(__DIR__ . '/stubs/' . $stubPath);
  //   $this->files->put(($this->getPath($stubName)), $stubFile);
  // }

  // protected function getDefaultNamespace($rootNamespace): string
  // {
  //   return $rootNamespace . '\\' . config('logging.path');
  // }

  // protected function getStub()
  // {
  //   return '';
  // }
}
