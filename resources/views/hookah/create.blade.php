@extends('layouts.app')

@section('content')
<div class="container">
    
    @include('hookah.includes.title')
    
    @include('hookah.includes.form', ['action' => 'create'])

</div>
@endsection