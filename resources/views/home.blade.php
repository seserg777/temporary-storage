@extends('layouts.app')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-1">Upload a file</h1>
        <p class="text-gray-500 text-sm">
            Upload PDF or DOCX files. Files are automatically deleted after 24 hours.
            <a href="{{ route('files.index') }}" class="text-indigo-600 hover:underline ml-1">View uploaded files &rarr;</a>
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div id="upload-zone"
             class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-indigo-400 transition-colors">
            <p class="text-gray-400 text-sm mb-3">Drag &amp; drop a PDF or DOCX file here, or</p>
            <label for="file-input"
                   class="inline-block cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                Choose file
            </label>
            <input id="file-input" type="file" accept=".pdf,.docx" class="hidden">
            <p class="text-xs text-gray-400 mt-3">Max 10 MB &mdash; PDF or DOCX only</p>
        </div>

        {{-- Progress bar --}}
        <div id="upload-progress" class="mt-4 hidden">
            <div class="flex justify-between text-xs text-gray-500 mb-1">
                <span id="upload-filename" class="truncate max-w-xs"></span>
                <span id="upload-percent" class="ml-2 shrink-0">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div id="upload-bar" class="bg-indigo-500 h-2 rounded-full transition-all duration-150" style="width: 0%"></div>
            </div>
        </div>

        {{-- Error message --}}
        <div id="upload-error" class="mt-4 hidden rounded-md bg-red-50 border border-red-200 px-4 py-3 text-red-700 text-sm"></div>
    </div>
@endsection