# Directory Structure

## Directory Structure

```
•
├── app                                 # Almost all our app logic here
│   └── Console
│   └── Exception
│   └── HTTP
│   │   └── Controller                  # Endpoint logic
│   │   |   └── Api                     # All Api controller should be here
│   │   │       └── {Module}
│   │   │           └── {SubModule}	# Optional, each module can have submodule
│   │   └── Middleware
│   │   └── Requests                    # Validation and authorization check
│   │   │   └── {Module}
│   │   └── Resources                   # Auto format json response
│   │       └── {Module}
│   └── Provider        
│   └── Model                           # Database model
│       └── {Module}         
└── database
│   └── migrations                      # Database
│   └── seeds                           # Dummy data
└── docs                                # Documentation your module
└── routes                              # Routing url endpoint
    └── api                             
        └── {module}
    └── api.php							# Main route file for api
```

## Example

For example, you want to create some feature `Purchase Order` in module `Inventory`. Then you should create related file in this project using this structure

`module` is a module name, in this case Inventory

`submodule` is a sub module name, in this case Purchasing

`feature` is a feature name, in this case PurchaseOrder

### Create controller

Instead of defining all of your request handling logic as Closures in route files, you may wish to organize this behavior using Controller classes. Controllers can group related request handling logic into a single class. Controllers are stored in the `app/Http/Controllers` directory.

**Generate controller scaffolding** 

`php artisan make:controller Api\\{Module}\\{SubModule}\\{Feature}Controller --resource`

```
php artisan make:controller Api\\Inventory\\Purchasing\\PurchaseOrderController --resource
```

**Result**, you need to delete method `create` and `edit` because we don't need them for creating API

```
<?php

namespace App\Http\Controllers\Api\Inventory\Purchasing;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class PurchaseOrderController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

```

### Create routes

The routes in `routes/api.php` are stateless and are assigned the `api` middleware group.

Routes defined in the `routes/api.php` file are nested within a route group by the `RouteServiceProvider`. Within this group, the `/api` URI prefix is automatically applied so you do not need to manually apply it to every route in the file. You may modify the prefix and other route group options by modifying your `RouteServiceProvider` class.

#### Available Router Methods

The router allows you to register routes that respond to any HTTP verb:

```
Route::get($uri, $callback);
Route::post($uri, $callback);
Route::put($uri, $callback);
Route::patch($uri, $callback);
Route::delete($uri, $callback);
Route::options($uri, $callback);
```

**Create a routes file in routes directory**

```
└── routes                              
    └── api                             
        └── {module}					# Replace this with inventory
            └── {sub-module}			# Replace this with purchasing
	            └── {feature-name}.php  # Replace this with purchase-order.php
```

**Format** 

```php
// routes/api/{module}/{sub-module}/{feature}.php

<?php

Route::middleware('auth:api')->prefix('v1')->group(function () {
  Route::prefix('{Module}')->group(function () {
    Route::prefix('{SubModule}')->group(function () {
      Route::apiResource('{Feature}', 'PurchaseOrderController');
    });
  });
});


```

**Example**

```php
// routes/api/inventory/purchasing/purchase-order.php

<?php

Route::middleware('auth:api')->prefix('v1')->group(function () {
  Route::prefix('inventory')->group(function () {
    Route::prefix('purchasing')->group(function () {
      Route::apiResource('purchase-order', 'PurchaseOrderController');
    });
  });
});

```

And then update your main api routes

```php
// routes/api.php

<?php

...
  
require(base_path() . '/inventory/purchasing/purchase-order.php');
```



### Create database migration

Migrations are like version control for your database, allowing your team to easily modify and share the application's database schema. Migrations are typically paired with Laravel's schema builder to easily build your application's database schema. If you have ever had to tell a teammate to manually add a column to their local database schema, you've faced the problem that database migrations solve.

**Generate migration scaffolding**

`php artisan make:migration create_module_submodule_feature_table`

```
php artisan make:migration create_inventory_purchasing_purchase_order_table
```

**Result**

```
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoryPurchasingPurchaseOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_purchasing_purchase_order', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_purchasing_purchase_order');
    }
}

```

After successful generate your database migration, you can run `php artisan migrate` command to create your database

!> You need to run this migration from `homestead`

### Create database model

To get started, let's create an Eloquent model. Models typically live in the `app` directory, but you are free to place them anywhere that can be auto-loaded according to your `composer.json` file. All Eloquent models extend `Illuminate\Database\Eloquent\Model` class.

The easiest way to create a model instance is using the `make:model` [Artisan command](https://laravel.com/docs/5.5/artisan):

**Generate model scaffolding**

`php artisan make:model Model\\{Module}\\{SubModule}\\{Feature}`

```
php artisan make:model Model\\Inventory\\Purchasing\\PurchaseOrder
```

**Result**

```
<?php

namespace App\Model\Inventory\Purchasing;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
	// connect this model to database
	// $table = 'table_name'
    protected $table = 'inventory_purchasing_purchase_order';
}

```

### Create database seeder

Laravel includes a simple method of seeding your database with test data using seed classes. All seed classes are stored in the `database/seeds` directory. Seed classes may have any name you wish, but probably should follow some sensible convention, such as `UsersTableSeeder`, etc. By default, a `DatabaseSeeder` class is defined for you. From this class, you may use the `call` method to run other seed classes, allowing you to control the seeding order.

**Generate seeder scaffolding**

`php artisan make:seeder {Module}{SubModule}{Feature}TableSeeder`

```
php artisan make:seeder InventoryPurchasingPurchaseOrderTableSeeder
```

**Result**

```
<?php

use Illuminate\Database\Seeder;

class InventoryPurchasingPurchaseOrderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
    }
}

```

Within the `DatabaseSeeder` class, you may use the `call` method to execute additional seed classes. Using the `call` method allows you to break up your database seeding into multiple files so that no single seeder class becomes overwhelmingly large. Simply pass the name of the seeder class you wish to run:

```
/**
 * Run the database seeds.
 *
 * @return void
 */
public function run()
{
    $this->call([
	    ...
        InventoryPurchasingPurchaseOrderTableSeeder::class,
    ]);
}
```

Once you have written your seeder classes, you may use the `db:seed` Artisan command to seed your database. By default, the `db:seed` command runs the `DatabaseSeeder` class, which may be used to call other seed classes. However, you may use the `--class` option to specify a specific seeder class to run individually:

`php artisan db:seed --class="InventoryPurchasingPurchaseOrderTableSeeder"`

!> You need to run this migration from `homestead`

### Create resource

When building an API, you may need a transformation layer that sits between your Eloquent models and the JSON responses that are actually returned to your application's users. Laravel's resource classes allow you to expressively and easily transform your models and model collections into JSON.

To generate a resource class, you may use the `make:resource` Artisan command. By default, resources will be placed in the `app/Http/Resources` directory of your application.

**Generate resource scaffolding**

`php artisan make:resource {Module}\\{SubModule}\\{Feature}Resource`

```
php artisan make:resource Inventory\\Purchasing\\PurchaseOrderResource
```

**Result**

```
<?php

namespace App\Http\Resources\Inventory\Purchasing;

use Illuminate\Http\Resources\Json\Resource;

class PurchaseOrderResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}

```

### Create validation request

For more complex validation scenarios, you may wish to create a "form request". Form requests are custom request classes that contain validation logic. To create a form request class, use the `make:request` Artisan CLI command:

**Generate validation request scaffolding**

```bash
php artisan make:request Inventory\\Purchasing\\StorePurchaseOrder
```

**Result**

```php
<?php

namespace App\Http\Requests\Inventory\Purchasing;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrder extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}

```

### Final Structure

All done, now we are ready to write our logic to create a feature, and this is our final directory structure looks like

```
•
├── app                                 
│   └── Console
│   └── Exception
│   └── HTTP
│   │   └── Controller   
│   │   |   └── Api                     
│   │   │       └── Inventory
│   │   │    	     └── Purchasing
│   │   │        	      └── PurchaseOrderController.php
│   │   └── Middleware
│   │   └── Requests                    
│   │   |   └── Inventory
│   │   |    	└── Purchasing
│   │   |        	└── StorePurchaseOrder.php
│   │   └── Resources
│   │       └── Inventory
│   │       	└── Purchasing
│   │            	└── PurchaseOrderResource.php
│   └── Provider        
│   └── Model
│       └── Inventory
│        	└── Purchasing
│            	└── PurchaseOrder.php
└── database
│   └── migrations
│    	└── create_inventory_purchasing_purchase_order.php
│   └── seeds
│    	└── InventoryPurchasingPurchaseOrderSeeder.php
└── docs
└── routes
    └── api                             
    │   └── inventory
    │    	└── purchasing
    │        	└── purchase-order.php
    └── api.php
```

