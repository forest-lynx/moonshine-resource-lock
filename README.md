 # Блокировка ресурсов


[![Latest Stable Version](https://img.shields.io/packagist/v/forest-lynx/moonshine-resource-lock)](https://github.com/forest-lynx/moonshine-resource-lock)
[![Total Downloads](https://img.shields.io/packagist/dt/forest-lynx/moonshine-resource-lock)](https://github.com/forest-lynx/moonshine-resource-lock) 
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)\
[![Laravel](https://img.shields.io/badge/Laravel-11+-FF2D20?style=for-the-badge&logo=laravel)](Laravel) 
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php)](PHP) 
[![PHP](https://img.shields.io/badge/Moonshine-2.18+-1B253B?style=for-the-badge)](https://github.com/moonshine-software/moonshine) 

Documentation in [English](./doc/README-EN.md)

Добавляет функцию блокировки ресурсов в административную панель MoonShine. Когда пользователь редактирует запись, ресурс блокируется, чтобы другие пользователи не могли одновременно вносить изменения.

|Версия пакета | Версия админ-панели MoonShine |
|:---:|:---:|
| ^1.x | ^2.18.0 |
| ^2.x | ^3.x |
## Содержание
* [Установка](#установка)
* [Использование](#использование)
* [Конфигурация](#конфигурация)
* [Отображение всех блокировок](#отображение-всех-блокировок)
* [Публикация конфигурации и языковых файлов](#публикация-конфигурации-и-языковых-файлов)
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
Команда запустит миграции, и предложит опубликовать конфигурационный файл и языковые файлы.

## Использование
Пакет `resource-lock` позволяет заблокировать ресурс и предотвратить его редактирование другими пользователями. В настоящее время блокировка применяется только при редактировании ресурса в режиме отдельной страницы.

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
Время блокировки регулируется параметром `lock_time`, который указан в конфигурационном файле, значение указывается в минутах.

##### Отображение информации о пользователе заблокировавшем ресурс

По умолчанию в модальном окне выводится информация о пользователе, который заблокировал доступ к ресурсу.

За вывод информации о пользователе отвечает параметр `show_owner_modal` (который по умолчанию имеет значение `true`) в конфигурационном файле.

В модальном окне отображается только имя пользователя, заблокировавшего доступ к ресурсу. Для отображения иной информации вы можете создать свой класс, который будет унаследован от `ResourceLockOwnerAction`, и зарегистрировать его в конфигурационном файле. Таким образом, вы сможете настроить отображение дополнительных сведений о заблокировавшем доступ пользователе в модальном окне.
Например:
```php
<?php

namespace App\Actions;

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

По умолчанию, когда вы нажимаете кнопку «Назад» в модальном окне заблокированного ресурса, происходит переход на индексную страницу ресурса. Однако вы можете изменить URL страницы редиректа, переопределив метод `getReturnUrlResourceLock` в вашем ресурсе.

```php
<?php
//...
class PostResource extends ModelResource
{
    //...
    use WithResourceLock;
    //...
    protected function getReturnUrlResourceLock(): string
    {
        return 'https://...';
    }
    //...
}
```
##### Отображение информации о заблокированном ресурсе на индексной странице

На индексной странице ресурса по умолчанию отображается информация о том, что доступ к ресурсу был заблокирован другим пользователем. Это отображается в виде специального значка:
![preview](./screenshots/indexInfo.png)

Чтобы скрыть эту информацию на индексной странице ресурса, можно в конфигурационном файле установить для параметра `resource_lock_to_index_page` значение `false`.
В зависимости от ваших потребностей, вы можете настроить отображение информации о заблокированном ресурсе на индексной странице ресурса с помощью объявления метода в вашем ресурсе `isDisplayOnIndexPage()`. Этот метод должен возвращать логическое значение `true` или `false`.
Например:
```php
<?php
//...
class PostResource extends ModelResource
{
    //...
    use WithResourceLock;
    //...
    public function isDisplayOnIndexPage(): bool
    {
        return false;
    }
    //...
}
```
> [!CAUTION]
> Пока это работает только для ресурсов с отображением через `TableBuilder`.

## Отображение всех блокировок
В этом пакете можно настроить отображение всех заблокированных ресурсов. 

![preview](./screenshots/lockResource.png)

Название ресурса для использования в меню административной панели MoonShine: 

`ForestLynx\MoonShine\Resources\LockResource`.

Удаление записи приводит к разблокировке ресурса.

Чтобы узнать больше о доступных вариантах отображения, обратитесь к документации административной панели [MoonShine](https://moonshine-laravel.com/docs/resource/menu/menu)

##### Очистка от устаревших записей о блокировке ресурсов
Для очистки таблицы базы данных от всех устаревших записей о блокировке ресурсов запустите команду:

```bash
php artisan resource-lock:clear-old
```
## Публикация конфигурации и языковых файлов
Чтобы опубликовать конфигурационный файл запустите команду:
```bash
php artisan vendor:publish --tag=resource-lock-config
```
Подробнее о [конфигурации пакета](#конфигурация).

Для публикации языковых файлов запустите команду:
```bash
php artisan vendor:publish --tag=resource-lock-lang
```
## Лицензия
[Лицензия MIT](LICENSE).

