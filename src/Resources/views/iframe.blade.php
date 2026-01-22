<x-shop::layouts
    :has-header="false"
    :has-feature="false"
    :has-footer="false"
>
    <x-slot:title>
        @lang('parampos::app.resources.title')
    </x-slot>
</x-shop::layouts>

<iframe src="{{ $iframeUrl }}" style="width: 100%; height: 100vh; border: none; margin-inline: auto;"></iframe>
