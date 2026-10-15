@unless ($breadcrumbs->isEmpty())
    <div class="breadcrumbs text-sm">
        <ul>
            @foreach ($breadcrumbs as $breadcrumb)
                @if ($breadcrumb->url && !$loop->last)
                    <li>
                        <a href="{{ $breadcrumb->url }}" class="text-base-content/50 hover:text-primary">
                            @isset($breadcrumb->faIcon)<i class="{{ $breadcrumb->faIcon }} mr-1 text-xs"></i>@endisset
                            {{ $breadcrumb->title }}
                        </a>
                    </li>
                @else
                    <li class="text-base-content/70">{{ $breadcrumb->title }}</li>
                @endif
            @endforeach
        </ul>
    </div>
@endunless
