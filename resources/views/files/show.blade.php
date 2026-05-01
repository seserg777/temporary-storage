@extends('layouts.app')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-1">File Details</h1>
        <p class="text-gray-500 text-sm">
            <a href="{{ route('files.index') }}" class="text-indigo-600 hover:underline">&larr; Back to files</a>
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <dl class="divide-y divide-gray-100">
            <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-600">File name</dt>
                <dd class="mt-1 text-sm text-gray-800 sm:col-span-2 sm:mt-0 break-all">
                    {{ $file->original_name }}
                </dd>
            </div>
            <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-600">Type</dt>
                <dd class="mt-1 text-sm text-gray-800 sm:col-span-2 sm:mt-0">
                    {{ strtoupper(pathinfo($file->original_name, PATHINFO_EXTENSION)) }}
                    &mdash; <span class="text-gray-500">{{ $file->mime_type }}</span>
                </dd>
            </div>
            <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-600">Size</dt>
                <dd class="mt-1 text-sm text-gray-800 sm:col-span-2 sm:mt-0">
                    {{ $file->formatted_size }}
                </dd>
            </div>
            <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-600">Uploaded at</dt>
                <dd class="mt-1 text-sm text-gray-800 sm:col-span-2 sm:mt-0">
                    {{ $file->created_at->format('d.m.Y H:i') }}
                </dd>
            </div>
            <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-600">Expires at</dt>
                <dd class="mt-1 text-sm text-gray-800 sm:col-span-2 sm:mt-0">
                    {{ $file->expires_at->format('d.m.Y H:i') }}
                </dd>
            </div>
        </dl>

        <div class="mt-6 flex items-center gap-4 px-4">
            <a href="{{ route('files.index') }}"
               class="text-sm text-indigo-600 hover:underline">
                &larr; Back to files
            </a>

            <form method="POST"
                  action="{{ route('files.destroy', $file) }}"
                  onsubmit="return confirm('Are you sure you want to delete this file?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="text-sm text-red-600 hover:text-red-800 font-medium">
                    Delete this file
                </button>
            </form>
        </div>
    </div>
@endsection