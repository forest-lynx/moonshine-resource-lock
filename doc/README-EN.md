# Blocking resources
[![Software License][ico-license]](LICENSE)

[![Laravel][ico-laravel]](Laravel) [![PHP][ico-php]](PHP) 

Adds the resource blocking feature to the MoonShine admin panel. When a user edits an entry, the resource is blocked so that other users cannot make changes at the same time.
> [!CAUTION]
> MoonShine version must be at least 2.18.0
## Content
* [Installation](#installation)
* [Usage](#usage)
* [Configuration](#configuration)
* [Display of all locks](#display-of-all-locks)
* [Publishing configuration and language files](#publishing-configuration-and-language-files)
* [License](#license)

## Installation
The command to install:
```bash
composer require forest-lynx/moonshine-resource-lock
```
Then run the installation command:
```bash
php artisan resource-lock:install
```
The command will start migrations, and will offer to publish the configuration file and language files.

## Usage
The `resource-lock` package allows you to lock a resource and prevent it from being edited by other users. Currently, the lock is applied only when editing a resource in a separate page mode.

##### Activating a resource lock

To activate the resource lock, add the `WithResourceLock` trait to the `ModelResource`.

```php
<?php
//...
use ForestLynx\MoonShine\Traits\WithResourceLock;

class PostResource extends ModelResource
{
    use WithResourceLock;
//...
```
Now your resource may be blocked.
## Configuration

![preview](./screenshots/lock.png)

##### The time when the resource was blocked.
By default, the resource will be blocked for 10 minutes, or until it is saved, whichever comes first.
The lock time is regulated by the `lock_time` parameter, which is specified in the configuration file, the value is specified in minutes.

##### Displaying information about the user who blocked the resource

By default, the modal window displays information about the user who blocked access to the resource.

The `show_owner_modal` parameter (which is set to `true` by default) in the configuration file is responsible for displaying user information.

The modal window displays only the name of the user who blocked access to the resource. To display other information, you can create your own class, which will be inherited from the `ResourceLockOwnerAction`, and register it in the configuration file. This way, you can configure the display of additional information about the user who blocked access in the modal window.
For example:

```php
namespace App\Actions;
//...
use ForestLynx\ResourceLock\Actions\ResourceLockOwnerAction;

class CustomActions extends ResourceLockOwnerAction
{
    public function execute(Model|Authenticatable $user): ?string
    {
        return $user->email;
    }
}
```
Then add it to the configuration file:

```php
    //...
    -'resource_lock_owner' => \ForestLynx\MoonShine\Actions\ResourceLockOwnerAction::class
    +'resource_lock_owner' => \App\Actions\CustomActions::class
    //...
```

##### Redirect to block a resource

By default, when you click the Back button in the modal window of a blocked resource, you are redirected to the index page of the resource. However, you can change the URL of the redirect page by overriding the `getReturnUrlResourceLock` method in your resource.

```php
    public function getReturnUrlResourceLock(): string
    {
        return 'https://...';
    }
```
##### Displaying information about a blocked resource on an index page

By default, the index page of the resource displays information that access to the resource has been blocked by another user. This is displayed as a special icon:

![preview](./screenshots/indexInfo.png)

To control this behavior, you can change the `resource_lock_to_index_page` parameter in the configuration file `config/resource-lock.php `.

> [!CAUTION]
> So far, this only works for resources with display via 'TableBuilder'.

## Displaying all locks
In this package, you can configure the display of all blocked resources. 

![preview](./screenshots/lockResource.png)

The name of the resource to use in the MoonShine admin panel menu: 

`ForestLynx\MoonShine\Resources\LockResource`.

Deleting an entry unlocks the resource.

To learn more about the available display options, refer to the documentation of the [MoonShine](https://moonshine-laravel.com/docs/resource/menu/menu ) admin panel.

##### Clearing outdated resource lock records
To clear the database table of all outdated resource lock records, run the command:

```bash
php artisan resource-lock:clear-old
```
## Publishing configuration and language files
To publish the configuration file, run the command:

```bash
php artisan vendor:publish --tag=resource-lock-config
```
Learn more about [package configuration](#configuration).

To publish language files, run the command:

```bash
php artisan vendor:publish --tag=resource-lock-lang
```
## License
[MIT License](LICENSE).


[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[ico-laravel]: https://img.shields.io/badge/Laravel-11+-FF2D20?style=for-the-badge&logo=laravel
[ico-php]: https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php
