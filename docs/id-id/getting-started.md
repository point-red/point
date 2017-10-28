# Permulaan

## Spesifikasi Server

Point framework has a few system requirements. Of course, all of these requirements are satisfied by the Laravel Homestead virtual machine, so it's highly recommended that you use Homestead as your local Point development environment.

However, if you are not using Homestead, you will need to make sure your server meets the following requirements:

* PHP >= 7.0.0
* OpenSSL PHP Extension
* PDO PHP Extension
* Mbstring PHP Extension
* Tokenizer PHP Extension
* XML PHP Extension

## Instalasi

Point utilizes Composer to manage its dependencies. So, before using Laravel, make sure you have Composer installed on your machine.

```bash
git clone git@github.com:point-red/point.git point
```

**Local Development Server**

If you have PHP installed locally and you would like to use PHP's built-in development server to serve your application, you may use the serve Artisan command. This command will start a development server at http://localhost:8000

```bash
php artisan serve
```
Of course, more robust local development options are available via Homestead and Valet.

## Konfigurasi

**Public Directory**

After installing Point, you should configure your web server's document / web root to be the  public directory. The index.php in this directory serves as the front controller for all HTTP requests entering your application.

**Configuration Files**

All of the configuration files for the Point are stored in the config directory. Each option is documented, so feel free to look through the files and get familiar with the options available to you.

**Directory Permissions**

After installing Point, you may need to configure some permissions. Directories within the storage and the bootstrap/cache directories should be writable by your web server or Point will not run. If you are using the Homestead virtual machine, these permissions should already be set.

**Application Key**

The next thing you should do after installing Point is set your application key to a random string. you can run this command from your root project

```bash
php artisan key:generate
```

Typically, this string should be 32 characters long. The key can be set in the .env environment file. If you have not renamed the .env.example file to .env, you should do that now. If the application key is not set, your user sessions and other encrypted data will not be secure!

**Additional Configuration**

Point needs almost no other configuration out of the box. You are free to get started developing! However, you may wish to review the config/app.php file and its documentation. It contains several options such as timezone and locale that you may wish to change according to your application.

## Konfigurasi Web Server

### Apache

Point includes a public/.htaccess file that is used to provide URLs without the index.php front controller in the path. Before serving Laravel with Apache, be sure to enable the mod_rewrite module so the .htaccess file will be honored by the server.

If the .htaccess file that ships with Laravel does not work with your Apache installation, try this alternative:
```
Options +FollowSymLinks
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]
```
### Nginx

If you are using Nginx, the following directive in your site configuration will direct all requests to the  index.php front controller:
```
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```
Of course, when using Homestead or Valet, pretty URLs will be automatically configured.
