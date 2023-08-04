@extends('layouts.app')

@section('content')
<div class="container">
    
    @include('hookah.includes.title')

    <div class="row justify-content-center">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Description</th>
                    <th scope="col">Manufacturer</th>
                    <th scope="col" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($hookahs as $hookah)
                    <tr>
                        <th scope="row">{{ $hookah->id }}</th>
                        <td scope="row">{{ $hookah->name }}</td>
                        <td scope="row">{{ $hookah->description }}</td>
                        <td scope="row">{{-- Todo --}}</td>
                        <td scope="row" class="actions text-center">
                            <form action="{{ route('hookah.destroy', $hookah->id) }}" method="Post">
                                <a href="{{ route('hookah.edit', $hookah->id) }}">
                                    <i class="fa-solid fa-pen-to-square fa-lg"></i>
                                </a>
                                <button type="submit" style="border: 0;">
                                    <i class="fa-solid fa-trash-can fa-lg"></i>
                                </button>
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-center">
            {!! $hookahs->links() !!}
        </div>
    </div>
</div>
@endsection