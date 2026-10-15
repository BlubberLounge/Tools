@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">

    @include('user.includes.title')
    <div class="flex flex-wrap px-4 mb-2">
        Online:
        @foreach ($onlineUsers as $usera)
            <div class="px-1 flex items-center">
                <span class="badge badge-primary">{{ $usera->full_name }}</span>
            </div>
        @endforeach
    </div>
    <div class="flex flex-wrap justify-center px-4">
        <table class="table table-zebra">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Online</th>
                    <th scope="col">Username</th>
                    <th scope="col">Firstname</th>
                    <th scope="col">Lastname</th>
                    <th scope="col" class="hidden xl:table-cell">E-Mail</th>
                    <th scope="col">Role</th>
                    <th scope="col" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <th scope="row">{{ $user->id }}</th>
                        <td>
                            <span class="badge {{ $user->isOnline() ? 'badge-success' : 'badge-error' }}">
                                {{ $user->isOnline() ? 'online' : 'offline' }}
                            </span>
                        </td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->firstname }}</td>
                        <td>{{ $user->lastname }}</td>
                        <td class="hidden xl:table-cell">{{ $user->email }}</td>
                        <td>
                            @foreach ($user->getRoles() as $k => $role)
                                {{ $role->name . (count($user->getRoles())-1 != $k ? ', ' : '') }}
                            @endforeach
                        </td>
                        <td class="text-center">
                            <form action="{{ route('user.destroy', $user->id) }}" method="POST">
                                @csrf
                                @method('DELETE')

                                <a href="{{ route('user.edit',$user->id) }}" class="btn btn-ghost btn-sm">
                                    <i class="fa-solid fa-pen-to-square fa-lg"></i>
                                </a>
                                <button type="submit" class="btn btn-ghost btn-sm">
                                    <i class="fa-solid fa-trash-can fa-lg"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="flex justify-center">
            {!! $users->links() !!}
        </div>
    </div>
</div>
@endsection
