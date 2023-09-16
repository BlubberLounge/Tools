@unless ($breadcrumbs->isEmpty())
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb m-0">
            @foreach ($breadcrumbs as $breadcrumb)

                @if ($breadcrumb->url && !$loop->last)
                    <li class="breadcrumb-item">
                        <a href="{{ $breadcrumb->url }}">
                            @if (isset($breadcrumb->faIcon))
                                <i class="{{ $breadcrumb->faIcon }}"></i>
                            @else
                                {{ __($breadcrumb->title) }}
                            @endif
                        </a>
                    </li>
                @else
                    <li class="breadcrumb-item active" aria-current="page">{{ __($breadcrumb->title) }}</li>
                @endif

            @endforeach
        </ol>
    </nav>
@endunless
