@extends('layouts.app')

@push('scripts')
<script>
    window.addEventListener('DOMContentLoaded', () => {

        document.getElementById('toggle-compact').addEventListener('click', function () {
            document.body.classList.toggle('compact-view');
            const isCompact = document.body.classList.contains('compact-view');

            const icon = this.querySelector('i');
            const text = this.querySelector('span');
            if (isCompact) {
                icon.classList.replace('fa-compress', 'fa-expand');
                text.textContent = 'Full View';
            } else {
                icon.classList.replace('fa-expand', 'fa-compress');
                text.textContent = 'Compact View';
            }

            sessionStorage.setItem('compactView', isCompact);
        });

        if (sessionStorage.getItem('compactView') === 'true') {
            document.body.classList.add('compact-view');
            const btn = document.getElementById('toggle-compact');
            btn.querySelector('i').classList.replace('fa-compress', 'fa-expand');
            btn.querySelector('span').textContent = 'Full View';
        }

            document.getElementById('copy-list-btn').addEventListener('click', function () {
        const names = Array.from(document.querySelectorAll('.card-title'))
            .map(el => el.textContent.trim())
            .filter(Boolean);

        if (!names.length) {
            alert('No cocktails found to copy.');
            return;
        }

        const list = names.join('\n');
        navigator.clipboard.writeText(list)
            .then(() => {
                const originalHTML = this.innerHTML;
                this.innerHTML = '<i class="fa-solid fa-check me-1"></i>Copied!';
                setTimeout(() => this.innerHTML = originalHTML, 1500);
            })
            .catch(() => alert('Failed to copy.'));
        });

        const modalEl = document.getElementById('cocktailModal');
        const modal = new bootstrap.Modal(modalEl);

        // Open modal for new cocktail
        document.getElementById('add-cocktail-btn').addEventListener('click', () => {
            modalEl.querySelector('.modal-title').textContent = 'Add Cocktail';
            modalEl.querySelector('form').reset();
            modalEl.querySelector('input[name="id"]').value = '';
            modal.show();
        });

        // Open modal for editing
        document.querySelectorAll('.edit-cocktail-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const rating = this.dataset.rating;
                const creator = this.dataset.creator;

                modalEl.querySelector('.modal-title').textContent = 'Edit Cocktail';
                modalEl.querySelector('input[name="id"]').value = id;
                modalEl.querySelector('input[name="name"]').value = name;
                modalEl.querySelector('input[name="rating"]').value = rating;
                modalEl.querySelector('input[name="creator"]').value = creator;

                modal.show();
            });
        });
    });
</script>
@endpush

@push('styles')
<style>
    body.compact-view .cocktail-image {
        display: none !important;
    }

    body.alternative-view #cocktail-grid {
    display: none !important;
    }

    body.alternative-view #alternative-list {
    display: block !important;
    }
</style>
@endpush

@section('content')
{{-- <div>
    @foreach ($cocktails as )

    @endforeach
</div> --}}
<div class="container p-0 py-4">
    <div class="d-flex justify-content-center justify-content-between justify-content-lg-center align-items-center mb-4">
        <h1 class="fw-bold fs-2 text-center m-0">{{ __('cocktails') }}</h1>

        <div class="d-flex gap-2">
            <button id="copy-list-btn" type="button" class="btn btn-outline-secondary btn-sm d-inline-flex d-lg-none align-items-center">
                <i class="fa-solid fa-copy me-1"></i>
                <span>Copy List</span>
            </button>

            @permission('create.cocktail')
                <button id="add-cocktail-btn" type="button" class="btn btn-success btn-sm d-inline-flex d-lg-none align-items-center">
                    <i class="fa-solid fa-plus me-1"></i>
                    <span>Add</span>
                </button>
            @endpermission

            <button id="toggle-compact" type="button" class="btn btn-outline-primary btn-sm d-inline-flex d-lg-none align-items-center">
                <i class="fa-solid fa-compress me-1"></i>
                <span>Compact View</span>
            </button>
        </div>
    </div>
    <div class="row justify-center"> {{-- gap-3 --}}
        @foreach($cocktails as $cocktail)
            <div class="col-6 col-sm-4 col-md-4 col-lg-3 d-flex justify-center p-2">
                <div class="card shadow-sm border rounded-3 p-0 mb-2 flex-fill d-flex flex-column" style="max-width: 26rem;">
                    <img src="{{ asset('storage/'. $cocktail->image) ?? 'https://placehold.co/600x200' }}" class="card-img-top rounded-3 cocktail-image" alt="{{ $cocktail->name }}">
                    <div class="card-body d-flex flex-column flex-grow-1">
                        <div class="d-flex align-items-center mb-3">
                            <div class="d-flex align-items-center me-2">
                                @for($i = 1; $i <= 5; $i++)
                                    @if ($cocktail->average_rating >= $i)
                                        <i class="fa-solid fa-star text-warning me-1"></i>
                                    @elseif ($cocktail->average_rating >= ($i - 0.5))
                                        <i class="fa-solid fa-star-half-stroke text-warning me-1"></i>
                                    @else
                                        <i class="fa-regular fa-star text-warning me-1"></i>
                                    @endif
                                @endfor
                            </div>
                            <span class="badge bg-light text-primary border border-primary me-2 d-none d-md-block">
                                {{ number_format($cocktail->average_rating, 1) }} out of 5
                            </span>
                            <span class="ms-0">
                                ({{ $cocktail->timesRated() }})
                            </span>
                        </div>

                        <a href="#" class="text-decoration-none mb-3">
                            <h5 class="card-title fw-semibold">
                                {{ $cocktail->name }}
                            </h5>
                        </a>

                        <div class="mt-auto d-flex align-items-center justify-content-between">
                            <button type="button" class="btn btn-primary me-2">
                                <i class="fa-solid fa-list-ol"></i>
                            </button>
                            <button type="button" class="btn btn-primary me-2">
                                <i class="fa-solid fa-comments"></i>
                            </button>
                            <button type="button" class="btn btn-primary d-none d-lg-block">
                                <i class="fa-solid fa-user-ninja"></i>
                            </button>
                            <span class="small-75 ms-auto">
                                {{ $cocktail->createdBy->name }}
                                <span class="d-none d-md-block">
                                    {{ __('am') .' '. $cocktail->created_at->format('d.m.Y') }}
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="modal fade" id="cocktailModal" tabindex="-1" aria-labelledby="cocktailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="cocktail-form" method="POST" action="#">
        @csrf
        <input type="hidden" name="id">

        <div class="modal-header">
          <h5 class="modal-title" id="cocktailModalLabel">Add Cocktail</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Image</label>
            <input type="file" name="image" class="form-control">
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
