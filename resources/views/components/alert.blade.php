@foreach($alerts as $key => $message)
    @if(is_array($message))
        @foreach($message as $sub)
            @if(!is_array($sub))
                <div class="alert alert-{{ $key }} alert-dismissible" role="alert">
                    {{ $sub }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    </button>
                </div>
            @endif
        @endforeach

    @else
        <div class="alert alert-{{ $key }} alert-dismissible" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
            </button>
        </div>
    @endif
@endforeach
