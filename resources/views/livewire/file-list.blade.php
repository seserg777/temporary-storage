<div>
    @if ($files->isEmpty())
        <p class="text-gray-500 text-sm py-8 text-center">No files uploaded yet.</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">File name</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Type</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Size</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Expires at</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($files as $file)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-800 max-w-xs truncate">
                                {{ $file->original_name }}
                            </td>
                            <td class="px-4 py-3 text-gray-500">
                                {{ strtoupper(pathinfo($file->original_name, PATHINFO_EXTENSION)) }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                                {{ number_format($file->size / 1024 / 1024, 2) }} MB
                            </td>
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                                {{ $file->expires_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST"
                                      action="{{ route('files.destroy', $file) }}"
                                      onsubmit="return confirm('Are you sure you want to delete this file?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-600 hover:text-red-800 font-medium text-xs">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($files->hasPages())
            <div class="mt-4">
                {{ $files->links() }}
            </div>
        @endif
    @endif
</div>