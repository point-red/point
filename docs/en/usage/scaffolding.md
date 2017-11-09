# Scaffolding

Scaffolding roughly aims to a quickly set up skeleton for an app or your project. The main purpose of Scaffolding is to speed your workflow rather than creating it new.

## Master Scaffolding

With master scaffolding you can create a new master in no time.

Just run this command and you will have all the template for your work.

```
php artisan scaffolding:master Warehouse
```

Result:

```
created Warehouse model
created Warehouse controller
created Warehouse resource
created Warehouse collection
created Warehouse store request
created Warehouse update request
created Warehouse factory
created Warehouse REST test class
created Warehouse validation test class
```

Then register your routes in `routes/api/master.php`

```
...

Route::apiResource('warehouses', 'WarehouseController');
```

All set, now you are ready to go to implement your code without doubt with structure.

