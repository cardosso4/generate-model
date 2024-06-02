<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>


### This library aims to facilitate the creation of the model file with the necessary data as shown in the example below.

# This library supports the databases.
- Mysql


## How to execute:
- To run this command, run in the terminal: php artisan generate:model {table_name}
- This command can be found in PHP artisan command list

---

## Example of the result
```
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class users extends Model
{

	public const FIELD_ID = 'id';
	public const FIELD_NAME = 'name';
	public const FIELD_ADDRESS_ID = 'address_id';


	protected $fillable = [
		self::FIELD_ID,
		self::FIELD_NAME,
		self::FIELD_ADDRESS_ID,
	];

	protected $casts = [
		self::FIELD_ID => 'bigint',
		self::FIELD_NAME => 'string',
		self::FIELD_ADDRESS_ID  => 'bigint',
	];


	/**
	* Relationships
	*/

	public function address(){
		$this->belongsTo(Address::class, 'address_id', 'id');
	}

}
```

## Package for installation
generate-model: [Packagist](https://packagist.org/packages/cardosso4/generate-model)
