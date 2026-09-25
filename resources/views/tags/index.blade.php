<x-layouts.app :title="__('Tags')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-900">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">Tags</h1>
                    <p class="text-sm text-neutral-600 dark:text-neutral-300">Créez les catégories utilisées par vos notes.</p>
                </div>
                <a href="{{ route('notes.index') }}" class="text-sm text-blue-600 hover:underline">Voir les notes</a>
            </div>

            @if (session('status'))
                <div class="mt-4 rounded border border-green-200 bg-green-50 p-3 text-green-700" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('tags.store') }}" class="mt-6 flex items-start gap-2">
                @csrf
                <div class="flex-1">
                    <label for="name" class="sr-only">Nom du tag</label>
                    <input id="name" name="name" value="{{ old('name') }}" placeholder="Nouveau tag" class="w-full rounded border px-3 py-2" />
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Ajouter</button>
            </form>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($tags as $tag)
                <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-900">
                    <p class="font-semibold">{{ $tag->name }}</p>
                    <p class="text-sm text-neutral-500">{{ trans_choice(':count note|:count notes', $tag->notes_count, ['count' => $tag->notes_count]) }}</p>
                </div>
            @empty
                <p class="text-neutral-500">Aucun tag pour le moment.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>
