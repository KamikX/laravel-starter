@extends('layouts.master')

@section('content')
    <div
        class="container mx-auto flex min-h-[calc(100vh-9rem)] flex-col items-center justify-center gap-6 bg-gray-50 px-4 py-10">
        <h1 class="text-center text-2xl font-semibold">Jodit livewire component</h1>

        <div class="w-full max-w-6xl">
            <x-jodit::editor name="content" :value="old('content')" placeholder="Write your post here…" :required="true" />
        </div>
    </div>
@endsection
