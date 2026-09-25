<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="mt-6 p-4 border border-neutral-200 dark:border-neutral-700 rounded-xl bg-white dark:bg-neutral-900">
            <h1 class="text-2xl font-bold">Renote</h1>
            <p class="mt-2 text-neutral-600 dark:text-neutral-300">
                Organisez vos notes et les tags qui permettent de les retrouver.
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <a href="{{ route('notes.index') }}" class="p-5 border border-neutral-200 dark:border-neutral-700 rounded-xl bg-white dark:bg-neutral-900 hover:border-blue-500">
                <h2 class="text-xl font-semibold">Notes</h2>
                <p class="mt-1 text-neutral-600 dark:text-neutral-300">
                    {{ trans_choice(':count note|:count notes', $notesCount, ['count' => $notesCount]) }}
                </p>
            </a>

            <a href="{{ route('tags.index') }}" class="p-5 border border-neutral-200 dark:border-neutral-700 rounded-xl bg-white dark:bg-neutral-900 hover:border-blue-500">
                <h2 class="text-xl font-semibold">Tags</h2>
                <p class="mt-1 text-neutral-600 dark:text-neutral-300">
                    {{ trans_choice(':count tag|:count tags', $tagsCount, ['count' => $tagsCount]) }}
                </p>
            </a>
        </div>
    </div>
</x-layouts.app>
