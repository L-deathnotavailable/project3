<x-layouts.app :title="__('Notes')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-900">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">Vos notes</h1>
                    <p class="text-sm text-neutral-600 dark:text-neutral-300">Créez et classez vos notes avec un tag.</p>
                </div>
                <a href="{{ route('tags.index') }}" class="text-sm text-blue-600 hover:underline">Gérer les tags</a>
            </div>

            @if (session('status'))
                <div class="mt-4 rounded border border-green-200 bg-green-50 p-3 text-green-700" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('notes.store') }}" class="mt-6 space-y-3">
                @csrf

                <div>
                    <label for="text" class="mb-1 block text-sm font-medium">Texte</label>
                    <textarea id="text" name="text" rows="4" placeholder="Écrivez votre note…" class="w-full rounded border p-2">{{ old('text') }}</textarea>
                    @error('text')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tag_id" class="mb-1 block text-sm font-medium">Tag</label>
                    <select id="tag_id" name="tag_id" class="w-full rounded border p-2">
                        <option value="">Sélectionnez un tag</option>
                        @foreach ($tags as $tag)
                            <option value="{{ $tag->id }}" @selected(old('tag_id') == $tag->id)>{{ $tag->name }}</option>
                        @endforeach
                    </select>
                    @error('tag_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Ajouter la note</button>
            </form>
        </div>

        <div class="space-y-3">
            @forelse ($notes as $note)
                <article class="flex items-start justify-between gap-4 rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-900">
                    <div>
                        <p class="whitespace-pre-line">{{ $note->text }}</p>
                        <p class="mt-2 text-sm text-neutral-500">Tag : {{ $note->tag->name }}</p>
                    </div>

                    <form method="POST" action="{{ route('notes.destroy', $note) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:underline">Supprimer</button>
                    </form>
                </article>
            @empty
                <p class="rounded-xl border border-dashed border-neutral-300 p-6 text-center text-neutral-500">Aucune note pour le moment.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>
