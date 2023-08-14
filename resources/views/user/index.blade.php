@extends('layouts.app')

@section('content')
<div class="container-fluid px-3">

    @include('user.includes.title')

    <div class="row justify-content-center px-4">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Username</th>
                    <th scope="col">Firstname</th>
                    <th scope="col">Lastname</th>
                    <th scope="col" class="d-none d-xl-table-cell">E-Mail</th>
                    <th scope="col">Role</th>
                    <th scope="col" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <th scope="row">{{ $user->id }}</th>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->firstname }}</td>
                        <td>{{ $user->lastname }}</td>
                        <td class="d-none d-xl-table-cell">{{ $user->email }}</td>
                        <td>
                            @foreach ($user->getRoles() as $k => $role)
                                {{ $role->name . (count($user->getRoles())-1 != $k ? ', ' : '') }}
                            @endforeach
                        </td>
                        <td class="text-center">
                            <form action="{{ route('user.destroy', $user->id) }}" method="POST">
                                @csrf
                                @method('DELETE')

                                <a href="{{ route('user.edit',$user->id) }}" class="btn text-black">
                                    <i class="fa-solid fa-pen-to-square fa-lg"></i>
                                </a>
                                <button type="submit" class="btn text-black">
                                    <i class="fa-solid fa-trash-can fa-lg"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-center">
            {!! $users->links() !!}
        </div>
    </div>
</div>
@endsection
