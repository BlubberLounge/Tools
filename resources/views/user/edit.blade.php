@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">

    @include('user.includes.title')

    @include('user.includes.form', ['action' => 'edit'])

</div>
@endsection
