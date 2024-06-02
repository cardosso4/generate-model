<?php

namespace Cardosso4\GenerateModel\Commands;

use Cardosso4\GenerateModel\ModelMysql;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class CreateModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:model {table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model';

    private string $directory;
    /**
     * @var array|false|string|string[]|null
     */
    private string|array|null|false $nameClass;


    /**
     * Execute the console command.
     */
    public function handle()
    {

        $tableName = $this->argument('table');
        $this->directory = base_path().'/app/Models/';
        $this->nameClass = str_replace(' ','',mb_convert_case(str_replace('_',' ', $tableName),MB_CASE_TITLE,"UTF-8"));

        $dbConnection = Config::get('database.default');

        $bodyModel = '';
        if($dbConnection == 'mysql'){

            $mysql = new ModelMysql($tableName,$this->nameClass);
            $bodyModel = $mysql->insertModelMysql();

        }

        if(!empty($bodyModel)){

            switch ($this->generateFile($bodyModel)){

                case 'created':
                    $this->info('File '.$this->nameClass .' generated in the directory: '.$this->directory);
                    break;

                case 'updated':
                    $this->info('File '.$this->nameClass .' updated in the directory: '.$this->directory);
                    break;

                case 'no modification':
                    $this->warn('File was not modified');
                    break;

                default:
                    $this->warn('It was not possible to generate the file');

            }

        }else{
            $this->error("The table was not found: {$tableName}");
        }


    }

    private function generateFile($bodyModel): string
    {

        if(file_exists($this->directory.$this->nameClass.'.php')){

            $check = $this->choice('Do you want to replace the file: '.$this->nameClass.'.php ?',['yes', 'no']);

            if($check == 'yes'){
                file_put_contents($this->directory.$this->nameClass.'.php', $bodyModel);
                return 'updated';
            }

            return 'no modification';

        }

        file_put_contents($this->directory.$this->nameClass.'.php', $bodyModel);
        return 'created';

    }

}
