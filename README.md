 # Блокировка ресурсов
[![Software License][ico-license]](LICENSE)

[![Laravel][ico-laravel]](Laravel) [![PHP][ico-php]](PHP) 

Добавляет функционал блокировки ресурсов для административной панели MoonShine. При редактировании записи пользователем происходит блокировка ресурса, чтоб другие пользователи не могли редактировать его одновременно.
> [!CAUTION]
> Версия MoonShine должна быть не ниже 2..
## Содержание
* [Установка](#установка)
* [Использование](#использование)
* [Конфигурация](#конфигурация)
* [Лицензия](#лицензия)

## Установка
Команда для установки:
```bash
composer require forest-lynx/moonshine-resource-lock
```
Затем запустите команду установки:
```bash
php artisan resource-lock:install
```
Вы можете опубликовать конфигурационный файл с помощью команды:
```bash
php artisan vendor:publish --tag=resource-lock-config
```
Подробнее о [конфигурации пакета](#конфигурация).

## Использование
Пакет resource-lock позволяет заблокировать ресурс и предотвратить его редактирование другими пользователями. В настоящее время блокировка применяется только при редактировании ресурса в режиме отдельной страницы.

##### Активация блокировки ресурса

Для активации блокировки ресурса необходимо добавить в `ModelResource` трейт `WithResourceLock`.

```php
<?php
//...
use ForestLynx\MoonShine\Traits\WithResourceLock;

class PostResource extends ModelResource
{
    use WithResourceLock;
//...
```
Теперь Ваш ресурс может быть заблокирован.

## Конфигурация

![preview](./screenshots/lock.png)

##### Время блокировки ресурса.
По умолчанию ресурс будет заблокирован на 10 минут, или до момента его сохранения, в зависимости от того что наступит раньше.
Для изменения времени блокировки ресурса необходимо изменить конфигурацию пакета, а именно в файле `config/resource-lock.php` параметр `lock_time`. Указывается время в минутах.

##### Отображение информации о пользователе заблокировавшем ресурс

По умолчанию в модальном окне отображается информация о пользователе, который заблокировал ресурс. Для управления этим поведением можно изменить конфигурацию пакета, а именно в файле `config/resource-lock.php` параметр `show_owner_modal`.
По умолчанию отображается имя пользователя. Для отображения других данных о пользователе заблокировавшем ресурс вы можете создать свое действие, чтобы перезаписать поведение по умолчанию, для этого необходимо создать класс действия унаследованный от `ResourceLockOwnerAction` и зарегистрировать его в конфигурационном файле.
Например:
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
Затем внести его в конфигурационный файл:
```php
    //...
    -'resource_lock_owner' => \ForestLynx\MoonShine\Actions\ResourceLockOwnerAction::class
    +'resource_lock_owner' => \App\Actions\CustomActions::class
    //...
```

##### Редирект для блокировки ресурса

По умолчанию при нажатии на кнопку `Назад` модального окна заблокированного ресурса происходит редирект на индексную страницу. Для изменения URL редиректа, вы можете переопределить метод `getReturnUrlResourceLock` в своем ресурсе.

```php
    public function getReturnUrlResourceLock(): string
    {
        return 'https://...';
    }
```
## Лицензия
[Лицензия MIT](LICENSE).


[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[ico-laravel]: https://img.shields.io/badge/Laravel-10+-FF2D20?style=for-the-badge&logo=laravel
[ico-php]: https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php

