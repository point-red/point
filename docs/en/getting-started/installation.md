# Installation

## Server Requirements

Point framework has a few system requirements. Of course, all of these requirements are satisfied by the Laravel Homestead virtual machine, so it's highly recommended that you use Homestead as your local Point development environment.

However, if you are not using Homestead, you will need to make sure your server meets the following requirements:

- PHP >= 7.1.3
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension

Since Point using Firestore you should install and enable *grpc*. For more detailed guide please check [https://cloud.google.com/php/grpc](https://cloud.google.com/php/grpc)  

## Download

Point utilizes Composer to manage its dependencies. So, before using Laravel, make sure you have Composer installed on your machine.

```bash
git clone git@github.com:point-red/point.git point
```

## Homestead (Recommended)

Before launching your Homestead environment, you must install [VirtualBox 5.1](https://www.virtualbox.org/wiki/Downloads) and [Vagrant](https://www.vagrantup.com/downloads.html). All of these software packages provide easy-to-use visual installers for all popular operating systems.

#### Installing Homestead

You may install Homestead by simply cloning the repository. Consider cloning the repository into a `Homestead` folder within your "home" directory, as the Homestead box will serve as the host to all of your Laravel projects:

```
cd ~

git clone https://github.com/laravel/homestead.git Homestead
```

You should check out a tagged version of Homestead since the `master` branch may not always be stable. You can find the latest stable version on the [GitHub Release Page](https://github.com/laravel/homestead/releases):

```
cd Homestead

// Clone the desired release...
git checkout v6.5.0
```

Once you have cloned the Homestead repository, run the `bash init.sh` command from the Homestead directory to create the `Homestead.yaml` configuration file. The `Homestead.yaml` file will be placed in the Homestead directory:

```
// Mac / Linux...
bash init.sh

// Windows...
init.bat
```

### Configuring Homestead

#### Setting Your Provider

The `provider` key in your `Homestead.yaml` file indicates which Vagrant provider should be used: `virtualbox`

```
provider: virtualbox
```

#### Configuring Shared Folders

The `folders` property of the `Homestead.yaml` file lists all of the folders you wish to share with your Homestead environment. As files within these folders are changed, they will be kept in sync between your local machine and the Homestead environment. You may configure as many shared folders as necessary:

```
folders:
    - map: ~/code
      to: /home/vagrant/code
```

If you are only creating a few sites, this generic mapping will work just fine. However, as the number of sites continue to grow, you may begin to experience performance problems. This problem can be painfully apparent on low-end machines or projects that contain a very large number of files. If you are experiencing this issue, try mapping every project to its own Vagrant folder:

```
folders:
    - map: ~/code/project1
      to: /home/vagrant/code/project1

    - map: ~/code/project2
      to: /home/vagrant/code/project2
```

#### Configuring Nginx Sites

Not familiar with Nginx? No problem. The `sites` property allows you to easily map a "domain" to a folder on your Homestead environment. A sample site configuration is included in the `Homestead.yaml` file. Again, you may add as many sites to your Homestead environment as necessary. Homestead can serve as a convenient, virtualized environment for every Laravel project you are working on:

```
sites:
    - map: point.dev
      to: /home/vagrant/code/point/public
```

If you change the `sites` property after provisioning the Homestead box, you should re-run `vagrant reload --provision` to update the Nginx configuration on the virtual machine.

#### The Hosts File

You must add the "domains" for your Nginx sites to the `hosts` file on your machine. The `hosts` file will redirect requests for your Homestead sites into your Homestead machine. On Mac and Linux, this file is located at `/etc/hosts`. On Windows, it is located at `C:\Windows\System32\drivers\etc\hosts`. The lines you add to this file will look like the following:

```
192.168.10.10  point.dev
```

Make sure the IP address listed is the one set in your `Homestead.yaml` file. Once you have added the domain to your `hosts` file and launched the Vagrant box you will be able to access the site via your web browser:

```
http://point.dev
```

### Launching The Vagrant Box

Once you have edited the `Homestead.yaml` to your liking, run the `vagrant up` command from your Homestead directory. Vagrant will boot the virtual machine and automatically configure your shared folders and Nginx sites.

To destroy the machine, you may use the `vagrant destroy --force` command.

#### Installing The Homestead Vagrant Box

Once VirtualBox / VMware and Vagrant have been installed, you should add the `laravel/homestead`box to your Vagrant installation using the following command in your terminal. It will take a few minutes to download the box, depending on your Internet connection speed:

```
vagrant box add laravel/homestead
```

If this command fails, make sure your Vagrant installation is up to date.

### Accessing Homestead Globally

Sometimes you may want to `vagrant up` your Homestead machine from anywhere on your filesystem. You can do this on Mac / Linux systems by adding a Bash function to your Bash profile. On Windows, you may accomplish this by adding a "batch" file to your `PATH`. These scripts will allow you to run any Vagrant command from anywhere on your system and will automatically point that command to your Homestead installation:

#### Mac / Linux

```
function homestead() {
    ( cd ~/Homestead && vagrant $* )
}
```

Make sure to tweak the `~/Homestead` path in the function to the location of your actual Homestead installation. Once the function is installed, you may run commands like `homestead up` or `homestead ssh` from anywhere on your system.

#### Windows

Create a `homestead.bat` batch file anywhere on your machine with the following contents:

```
@echo off

set cwd=%cd%
set homesteadVagrant=C:\Homestead

cd /d %homesteadVagrant% && vagrant %*
cd /d %cwd%

set cwd=
set homesteadVagrant=
```

Make sure to tweak the example `C:\Homestead` path in the script to the actual location of your Homestead installation. After creating the file, add the file location to your `PATH`. You may then run commands like `homestead up` or `homestead ssh` from anywhere on your system.

### Connecting Via SSH

You can SSH into your virtual machine by issuing the `vagrant ssh` terminal command from your Homestead directory.

But, since you will probably need to SSH into your Homestead machine frequently, consider adding the "function" described above to your host machine to quickly SSH into the Homestead box.

### Connecting To Databases

A `homestead` database is configured for both MySQL and PostgreSQL out of the box. For even more convenience, Laravel's `.env` file configures the framework to use this database out of the box.

To connect to your MySQL or PostgreSQL database from your host machine's database client, you should connect to `127.0.0.1` and port `33060` (MySQL) or `54320` (PostgreSQL). The username and password for both databases is `homestead` / `secret`.

> You should only use these non-standard ports when connecting to the databases from your host machine. You will use the default 3306 and 5432 ports in your Laravel database configuration file since Laravel is running within the virtual machine.

## Non Homestead (Not Recommended)

If you have PHP installed locally and you would like to use PHP's built-in development server to serve your application, you may use the serve Artisan command. This command will start a development server at http://localhost:8000

```bash
php artisan serve
```

Of course, more robust local development options are available via Homestead and Valet.

#### Web Server Configuration

##### Apache

Point includes a public/.htaccess file that is used to provide URLs without the index.php front controller in the path. Before serving Laravel with Apache, be sure to enable the mod_rewrite module so the .htaccess file will be honored by the server.

If the .htaccess file that ships with Laravel does not work with your Apache installation, try this alternative:

```
Options +FollowSymLinks
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]
```

##### Nginx

If you are using Nginx, the following directive in your site configuration will direct all requests to the  index.php front controller:

```
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

Of course, when using Homestead or Valet, pretty URLs will be automatically configured.

## Configuration

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

**Generate Passport Encryption Key**

Create the encryption keys needed to generate secure access tokens. In addition, the command will create "personal access" and "password grant" clients which will be used to generate access tokens:

```bash
php artisan passport:install
```
