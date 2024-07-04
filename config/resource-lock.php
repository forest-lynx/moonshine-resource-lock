<?php

declare(strict_types=1);

return [

    /**
     * The timeout in minutes when a resource is locked resource.
     */
    'lock_timeout' => 10,

    /**
     * Show information about the user who blocked the resource
     */
    'show_owner_modal' => true,


    /**
    * A class that returns information about the user who blocked the resource for display in a modal window
    */
    'resource_lock_owner' => \ForestLynx\MoonShine\Actions\ResourceLockOwnerAction::class,

    /**
     * Displaying information about resource blocking on the index page
     */
    'resource_lock_to_index_page' => true,
];
