<div>
    <div {{ $attributes->merge(["class" => "bg-white p-5"]) }}>
        <div class="card-header p-3">
            {{ $header }}
        </div>
        <div class="p-3">
            {{ $slot }}
        </div>
    </div>
</div>