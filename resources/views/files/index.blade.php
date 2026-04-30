@extends('layouts.app')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-1">Uploaded Files</h1>
        <p class="text-gray-500 text-sm">
            Files are automatically deleted after 7 days.
            <a href="{{ route('home') }}" class="text-indigo-600 hover:underline ml-1">&larr; Upload another file</a>
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @livewire('file-list')
    </div>
@endsection