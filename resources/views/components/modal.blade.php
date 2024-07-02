@props([
    'title' => '',
    'wide' => $isWide ?? false,
    'auto' => $isAuto ?? false,
])
<div x-data="modal(`true`,'',`false`)"
    {{ $attributes }}
>
    <template x-teleport="body">
        <div class="modal-template">
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-10"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-10"
                aria-modal="true"
                role="dialog"
                class="modal"
            >
                <div class="modal-dialog
                @if($wide) modal-dialog-xl @elseif($auto) modal-dialog-auto @endif"
                     x-bind="dismissModal"
                >
                    <div class="modal-content">
                        <div class="modal-header bgc-error">
                            <x-moonshine::icon
                                icon="heroicons.outline.lock-closed"
                                size="6"
                            />
                            <h5 class="modal-title grow">
                                {{ $title ?? '' }}
                            </h5>
                        </div>
                        <div class="modal-body">
                            {{ $slot ?? '' }}
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="open" x-transition.opacity class="modal-backdrop"></div>
        </div>
    </template>
</div>
