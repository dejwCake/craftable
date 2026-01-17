# Craftable - build admin panels with Laravel #

- [About](#about)
  - [Demo](#demo)
  - [Packages used](#made-of-components)
- [Requirements](#requirements)
- [Installation](#installation)
  - [New project](#new-craftable-project)
  - [Add to existing project](#add-craftable-to-existing-project)
- [Basics](#basics)
- [Documentation](#documentation)
- [Where to go next?](#where-to-go-next)

## About ##

Hi Crafter, welcome to the official documentation for Craftable – a Laravel-based open‑source toolkit for building administration interfaces. It’s a minimal admin template and a starting point for developing back‑office systems, intranets, or CMS‑like systems.

This package is forked from [BRACKETS-by-TRIAD/craftable](https://github.com/BRACKETS-by-TRIAD/craftable) and is continuously maintained.

![Craftable administration area example](https://www.getcraftable.com/docs/5.0/images/posts-crud.png "Craftable administration area example")

You could call it a CMS, but it’s a very slim one, with as little content to manage as possible. It has:
- Admin UI based on CoreUI (http://coreui.io/)
- CRUD generator
- Authentication, profile, and users CRUD
- Translations manager
- Other helpers to quickly bootstrap your new administration area (Media Library, Admin Listing, etc.)

### Demo ###

Try the live demo at https://demo.getcraftable.com.

Use these credentials to sign in:
- email: `demo@getcraftable.com`
- password: `demo123`

You can see an administration of:
- [Posts](https://demo.getcraftable.com/admin/posts) - this is the standard CRUD generated with the `admin-generator` package
- [Translatable Articles](https://demo.getcraftable.com/admin/translatable-articles) - this is the showcase for `translatable` Eloquent models
- [Manage access](https://demo.getcraftable.com/admin/users) - this is an extended CRUD for managing users (your existing Eloquent model)
- [Translations](https://demo.getcraftable.com/admin/translations) - where you can manage the translations stored in the database

### Made of components ###

Our intent was to split all the stuff into several packages with as few dependencies as possible. This is what we're coming with at the moment:
- [Admin UI](https://getcraftable.com/docs/5.0/user-interface) - admin template (CoreUI assets, Blade views, Vue components)
- [Admin Generator](https://getcraftable.com/docs/5.0/explore-generator) - CRUD generator for Eloquent models
- [Admin Authentication](https://getcraftable.com/docs/5.0/auth) - ability to authenticate into Admin area
- [Translatable](https://getcraftable.com/docs/5.0/translatable) - ability to have translatable content (extending Laravel's default Localization)
- [Admin Listing](https://getcraftable.com/docs/5.0/listing) - ability to quickly build a query for administration listing for your Eloquent models
- [Media Library](https://getcraftable.com/docs/5.0/media) - ability to attach media to Eloquent models
- [Admin Translations](https://getcraftable.com/docs/5.0/translations) - translation manager (with UI)

Craftable uses all the packages above. It also uses some other 3rd party packages (like Spatie's `spatie/laravel-permission`) and provides some basic default configuration to speed up development of a typical administration interface.

## Requirements ##

Craftable requires:
- PHP 8.2+
- Supported databases:
  - MariaDB 11.6+
  - PostgreSQL 17+
- npm 5.3+
- Node.js 8.4+

Craftable is built on Laravel, so you should check out its requirements too. It is compatible with Laravel 12:
- https://laravel.com/docs/12.x/installation

## Installation ##

### New Craftable project ###

First you need to have a Laravel application, so follow the Laravel installation guide: https://laravel.com/docs/12.x/installation

Create an empty database of your choice (PostgreSQL or MySQL).

Now require these two main packages:
```bash
composer require dejwcake/craftable
composer require --dev dejwcake/admin-generator
```

### Add Craftable to existing project ###

Alternatively, you can use your existing Laravel application. Start by requiring these two main packages:

```bash
composer require dejwcake/craftable
composer require --dev dejwcake/admin-generator
```

### Package installation ###

To install Craftable, run:
```bash
php artisan craftable:install
```

This is going to install all dependencies, publish all important vendor configs, migrate, set up some configs, configure webpack, and run migrations.

The command is going to generate and **print the password for the default administrator** account. Save this password to your clipboard, we are going to need it soon.

## Basics ##

Once installed, navigate your browser to `/admin/login`. You should be able to see a login screen.

![Admin login form](https://docs.getcraftable.com/assets/login-form.png "Admin login form")

Use these credentials to log in:
- Email: `admin@getcraftable.com`
- Password: use the password from your clipboard (it was printed at the end of the `craftable:install` command)

After logging in you should be able to see a default homepage and two menu items:
- Manage access
- Translations

![Admin homepage](https://docs.getcraftable.com/assets/admin-home.png "Admin homepage")

## Documentation ##

You can find full documentation for this package and other packages Craftable uses at https://docs.getcraftable.com/#/craftable.

## Where to go next? ##

At this point you are ready to start building your administration area. You probably want to start building a typical CRUD interface for your Eloquent models. You should definitely check our [Admin Generator](https://getcraftable.com/docs/5.0/explore-generator) documentation.

In case you rather want to create some atypical custom made administration, then you probably want to head over to [Admin UI](https://getcraftable.com/docs/5.0/user-interface) package.

Have fun and craft something awesome!

## How to contribute

- Drop a :star: on the GitHub repository (optional)<br/>

- Before contributing, please read [CONTRIBUTING.md](https://github.com/BRACKETS-by-TRIAD/craftable/blob/master/CONTRIBUTING.md) and [CODE_OF_CONDUCT.md](https://github.com/BRACKETS-by-TRIAD/craftable/blob/master/CODE_OF_CONDUCT.md).

- Create an issue for the project or a feature you would like to add to the project, and get the task assigned to yourself. (Issues can be any bug fixes or features you want to add to this project.)

- Fork the repo to your GitHub account.<br/>

- Clone the repo to a local folder on your machine by using this command with your forked repository link in place of the URL below:<br/>
  `git clone https://github.com/dejwcake/craftable`
- Create a branch using the command below.
  `git branch <your-branch-name>`
- Check out your branch.
  `git checkout <your-branch-name>`
- Add your code in your local machine folder.
  `git add .`
- Commit your changes.
  `git commit -m "<add your message here>"`
- Push your changes.
  `git push --set-upstream origin <your-branch-name>`

- Open a pull request (compare your branch with the owner `main` branch).

## Contributors 🌟
<br>
<h3 align="center">
 <b>Kudos to these amazing people</b>
</h3>
<a href="https://github.com/BRACKETS-by-TRIAD/craftable/graphs/contributors">

  <img src="https://contrib.rocks/image?repo=BRACKETS-by-TRIAD/craftable&&max=817" />

</a>
<br>

## Licence
MIT Licence. Refer to the [LICENSE](https://github.com/BRACKETS-by-TRIAD/craftable/blob/master/LICENSE) file to get more info.

## How to develop this project

### Composer

Update dependencies:
```shell
docker compose run -it --rm test composer update
```

Composer normalization:
```shell
docker compose run -it --rm php-qa composer normalize
```

### Run tests

Run tests with pcov:
```shell
docker compose run -it --rm test ./vendor/bin/phpunit -d pcov.enabled=1
```

To switch between postgresql and mariadb change in `docker-compose.yml` DB_CONNECTION environmental variable:
```git
- DB_CONNECTION: pgsql
+ DB_CONNECTION: mysql
```

### Run code analysis tools (php-qa)

PHP compatibility:
```shell
docker compose run -it --rm php-qa phpcs --standard=.phpcs.compatibility.xml --cache=.phpcs.cache
```

Code style:
```shell
docker compose run -it --rm php-qa phpcs -s --colors --extensions=php
```

Fix style issues:
```shell
docker compose run -it --rm php-qa phpcbf -s --colors --extensions=php
```

Static analysis (phpstan):
```shell
docker compose run -it --rm php-qa phpstan analyse --configuration=phpstan.neon
```

Mess detector (phpmd):
```shell
docker compose run -it --rm php-qa phpmd ./src,./install-stubs,./resources,./tests ansi phpmd.xml --suffixes php --baseline-file phpmd.baseline.xml
```
