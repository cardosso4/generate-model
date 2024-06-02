<?php

namespace Cardosso4\GenerateModel\GenerateModel;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ModelMysql
{
    /**
     * Name table.
     */
    protected $table;

    /**
     * Name nameClass.
     */
    protected $nameClass;

    /**
     * Structure table.
     */
    protected $structureTable;


    public function __construct($nameTable,$nameClass)
    {

        $this->table = $nameTable;
        $this->nameClass = $nameClass;


    }

    public function insertModelMysql(): string
    {
        try {

            $this->structureTable = DB::select('describe `'.$this->table.'`');

            $body = '';

            $this->clearStructureTable();
            $this->beginModel($body);
            $this->constFields($body);
            $this->fillable($body);
            $this->casts($body);
            $this->relationshipModel($body);
            $body .= '}';

            return $body;

        } catch (QueryException $e) {
            return '';
        }
    }

    public function beginModel(&$body){

        $body .= '<?php' .PHP_EOL;
        $body .= 'namespace App\Models;'.PHP_EOL.PHP_EOL;
        $body .= 'use Illuminate\Database\Eloquent\Model;'.PHP_EOL.PHP_EOL;
        $body .= 'class '.$this->nameClass .' extends Model'.PHP_EOL.'{'.PHP_EOL.PHP_EOL;

        $body .= '    protected $table = \''.$this->table.'\';'.PHP_EOL.PHP_EOL;

    }

    protected function clearStructureTable()
    {
        $arrColumnDrop = ['created_at', 'updated_at', 'deleted_at'];
        $arrColumns = array_column($this->structureTable, 'Field');

        foreach ($arrColumnDrop as $item) {
            $index = array_search($item, $arrColumns);
            if ($index !== false) {
                unset($this->structureTable[$index]);
            }
        }

        ksort($this->structureTable);
    }

    protected function constFields(&$body):void
    {
        $body .= PHP_EOL;
        foreach ($this->structureTable as $column) {
            $body .= $this->setText('const', $column->Field, null);
        }

    }

    protected function fillable(&$body): void
    {
        $body .= PHP_EOL."\tprotected \$fillable = [".PHP_EOL;

        foreach ($this->structureTable as $column) {
            $body .= $this->setText('fillable', $column->Field, null);
        }

        $body .= "\t];".PHP_EOL;

    }

    protected function casts(&$body): void
    {
        $body .= PHP_EOL."\tprotected \$casts = [".PHP_EOL;

        foreach ($this->structureTable as $column) {
            $body .= $this->setText('cast', $column->Field, $this->typeData($column->Type));
        }

        $body .= "\t];".PHP_EOL;

    }

    public function relationshipModel(&$body): void
    {
        $relationship = DB::table('INFORMATION_SCHEMA.KEY_COLUMN_USAGE')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->where([
                'TABLE_SCHEMA' => getenv('DB_DATABASE'),
                'TABLE_NAME' => $this->table,
            ])
            ->get();

        $body .= PHP_EOL.PHP_EOL;
        $body .= "\t/**".PHP_EOL;
        $body .= "\t* Relationships".PHP_EOL;
        $body .= "\t*/".PHP_EOL;
        $body .= PHP_EOL;

        $createdFunctions = [];
        foreach ($relationship as $value) {
            $nameFunction = lcfirst(str_replace(' ', '', mb_convert_case(str_replace('_', ' ', $value->REFERENCED_TABLE_NAME), MB_CASE_TITLE, 'UTF-8')));
            $tableReference = str_replace(' ', '', mb_convert_case(str_replace('_', ' ', $value->REFERENCED_TABLE_NAME), MB_CASE_TITLE, 'UTF-8'));

            $body .= "\t".'public function '.$nameFunction.'(){'.PHP_EOL;
            $body .= "\t\t".'$this->belongsTo('.$tableReference.'::class, \''.$value->COLUMN_NAME.'\', \''.$value->REFERENCED_COLUMN_NAME.'\');'.PHP_EOL;
            $body .= "\t".'}'.PHP_EOL.PHP_EOL;

            $createdFunctions[] = $nameFunction;
        }

        $sqlRefrenceQuery = DB::table('information_schema.table_constraints as i')
            ->select(
                'i.table_name as nome_tabela', 'i.constraint_name as nome_fk', 'k.referenced_table_name as tabela_referencia',
                'k.referenced_column_name as coluna_tabela_referencia', 'k.column_name as column_fk'
            )
            ->leftJoin('information_schema.key_column_usage as k', 'i.constraint_name', 'k.constraint_name')
            ->where([
                'i.constraint_type' => 'FOREIGN KEY',
                'i.TABLE_SCHEMA' => getenv('DB_DATABASE'),
                'k.referenced_table_name' => $this->table,
            ])
            ->get();

        foreach ($sqlRefrenceQuery as $reference) {
            $nameFunction = lcfirst(str_replace(' ', '', mb_convert_case(str_replace('_', ' ', $reference->nome_tabela), MB_CASE_TITLE, 'UTF-8')));
            $tableReference = str_replace(' ', '', mb_convert_case(str_replace('_', ' ', $reference->nome_tabela), MB_CASE_TITLE, 'UTF-8'));

            if (in_array($nameFunction, $createdFunctions)) {
                continue;
            }

            $body .= "\t".'public function '.$nameFunction.'(){'.PHP_EOL;
            $body .= "\t\t".'$this->hasMany('.$tableReference.'::class, \''.$reference->column_fk.'\', \''.$reference->coluna_tabela_referencia.'\');'.PHP_EOL;
            $body .= "\t".'}'.PHP_EOL.PHP_EOL;
        }

    }

    protected function typeData($text)
    {
        $delimiter = '';
        if (str_contains($text, '(')) {
            $delimiter = '(';
        } elseif (str_contains($text, ' ')) {
            $delimiter = ' ';
        }

        if ($delimiter == '') {
            return $text;
        }

        $text = explode($delimiter, $text)[0];

        $arrTypeColumn = [
            'varchar' => 'string',
            'tinyint' => 'boolean',
        ];

        return key_exists($text, $arrTypeColumn) ? $arrTypeColumn[$text] : $text;
    }

    protected function setText($type, $field, $typeData)
    {
        switch ($type) {
            case 'const':
                return "\tpublic const FIELD_".strtoupper($field).' = \''.$field.'\';'.PHP_EOL;

            case 'cast':
                return "\t\tself::FIELD_".strtoupper($field).' => \''.$typeData.'\','.PHP_EOL;

            case 'fillable':
                return "\t\tself::FIELD_".strtoupper($field).','.PHP_EOL;
        }
    }


}