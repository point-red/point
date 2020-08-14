# Dummy Data

Sometimes when we want to try our database, we need to insert each data manually. With database seeder we can fill our database automatically with no time.

## Generate dummy data

To generate data into database, we can use this command:

```bash
php artisan db:seed --class="DummyDatabaseSeeder"
```

## Create new dummy data

When you create a new feature, maybe you want to add your dummy data, you can use `factory()` to generate dummy data to your database.

```
•
├── database
    └── seeds
        └── DummyDatabaseSeeder.php
```

```php
// DummyDatabaseSeeder.php

public function run()
{
    // call default seeder that needed to our app
    $this->call(DefaultSeeder::class);

    // generate database seeder
    factory(User::class, 10)->create();
    factory(Person::class, 10)->create();
    factory(Warehouse::class, 10)->create();
    
    // add your factory() here
    
}
```

