@extends('layouts.app')

@section('title', 'React Preview')

@section('content')
<div class="min-h-screen bg-slate-200 flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-4xl">
        <div id="app"></div>
    </div>
</div>

@vite(['resources/react/main.tsx'])

@endsection
