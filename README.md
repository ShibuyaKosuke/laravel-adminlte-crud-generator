# laravel-adminlte-crud-generator

Crud generator for [laravel-adminlte](https://github.com/JeroenNoten/Laravel-AdminLTE)

## Feature

- Generate **lang files** from Column Comment on **MySQL** Database.
- Generate **blade view files** from tables and columns.
- Generate **Model files** from tables and columns.
    - All models except user.
- Generate **Controller files** from tables and columns.
- Generate **FormRequest files** from tables and columns.
- Generate **ViewComposer files** from tables and columns.
- Add routing to routes/web.php

## Install & Setup

### Install laravel

```bash
composer create-project --prefer-dist laravel/laravel project_name "6.*"
```

### Install laravel-adminlte-crud-generator

```bash
composer require shibuyakosuke/laravel-adminlte-crud-generator
```

### Setup laravel

If you use Japanese, edit `app/config/app.php`

```diff
/**
 * app/config/app.php
 */
-    'locale' => 'en',
+    'locale' => 'ja',
```

```diff
/**
 * app/config/app.php
 */
    'providers' => [
+        Collective\Html\HtmlServiceProvider::class, // add
+        Watson\BootstrapForm\BootstrapFormServiceProvider::class, // add
+        Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class, // add
    ],
```

```diff
/**
 * app/config/app.php
 */
    'aliases' => [
+        'Form' => Collective\Html\FormFacade::class, // add
+        'Html' => Collective\Html\HtmlFacade::class, // add
+        'BootForm' => Watson\BootstrapForm\Facades\BootstrapForm::class, // add
    ],
```

## Setup laravel-adminlte

```bash
php artisan adminlte:install
```

 [see](https://github.com/JeroenNoten/Laravel-AdminLTE) more information

## Setup laravel-adminlte-crud

```bash
php artisan vendor:publish --provider="Shibuyakosuke\LaravelCrudGenerator\Providers\ResourceServiceProvider"
```

Output these files below.

> - /configs/adminlte_crud.php
> - /app/Observers/*
> - /app/Traits/*

## Edit files

> Add '(new AdminlteMenu($events))->build();' to 'AppServiceProvider::boot()', and it displays adminlte sidebar menu.

```php
<?php

/**
 * app/Providers/AppServiceProvider
 */

namespace App\Providers;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Shibuyakosuke\LaravelCrudGenerator\AdminlteMenu\AdminlteMenu;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Dispatcher $events)
    {
        (new AdminlteMenu($events))->build(); // Add for menu of laravel-adminlte
    }
}
```

> Display Breadcrumbs 

```php
/**
 * resources/views/vendor/adminlte/page.blade.php
 */

<section class="content-header">
    @yield('content_header')

    {{ Breadcrumbs::render(Request::route()->getName(), Request::route()->parameters()) }} // Add
</section>
```

## Configs

`/configs/adminlte_crud.php`

```php
<?php

return [
    'default_model_path' => 'app/Models/', // Output models to this path.
    'textarea' => 500, // If the max length is above this value, output textarea otherwise input element.
    'required_label' => '*', // If the field is not allowed null, output form element with this text.
    'required_label_position' => 'after' // or before
];
```

## Usage

###  Run migration

Generate crud files, if you have **migration files with comment** like belows.

#### Notes

Foreign keys setting helps generating **relations** for models.

```php
class CreateUsersTable extends Migration
{
    use \App\Traits\DatabaseCommonColumns; // add

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('role_id')->comment('ロールID');
            $table->unsignedBigInteger('company_id')->comment('会社ID');
            $table->string('name')->comment('氏名');
            $table->string('email')->unique()->comment('メールアドレス');
            $table->timestamp('email_verified_at')->nullable()->comment('メール認証日時');
            $table->string('password')->comment('パスワード');
            $table->rememberToken()->comment('リメンバートークン');

            $this->addCommonColumns($table); // add

            // foreign keys setting helps generating relations for models. 
            $table->foreign('role_id')->references('id')->on('roles');
            $table->foreign('company_id')->references('id')->on('companies');
        });
        $this->comment('users', 'ユーザー'); // add table comment
    }
}
```

```bash
php artisan migrate
```

### Generate crud

```bash
php artisan crud:generate
```

and then you should append this sentence to 'configs/app.php'.

```php
/*
 * Package Service Providers...
 */
\App\Providers\ViewServiceProvider::class,
```

## Options

### Overwrite files

```bash
php artisan crud:generate --force
```

### Specify table name

_Separate multiple tables with commas_

```bash
php artisan crud:generate --table=users,companies
```

### Output files

```bash
project ┌ app ┬ Http ┬ Controllers ─ ExampleController.php
        │     │      │
        │     │      ├ Requests ─ ExampleFormRequest.php
        │     │      │
        │     │      └ View ─ Composers ─ ExampleComposer.php
        │     │
        │     └ (Models) ─ Example.php
        │
        └ resources ┬ lang ── (ja) ─┬ tables.php
                    │               └ columns.php
                    │
                    └ views ─ examples ┬ index.blade.php
                                       ├ show.blade.php
                                       ├ create.blade.php
                                       └ edit.blade.php
```

And append to `route/web.php` automatically.

```php
Route::resource('/users', 'UserController');
```

# Thanks to

[@jeroennoten](https://github.com/jeroennoten)
[@dwightwatson](https://github.com/dwightwatson)