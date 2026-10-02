import { baseApi } from '../../services/api';

export const notesApi = baseApi.injectEndpoints({
    endpoints: (builder) => ({
        getNotes: builder.query({
            query: () => 'notes',
            transformResponse: (response) => response.data,
            providesTags: (result = []) => [
                { type: 'Note', id: 'LIST' },
                ...result.map((note) => ({ type: 'Note', id: note.id })),
            ],
        }),
        getNote: builder.query({
            query: (id) => `notes/${id}`,
            transformResponse: (response) => response.data,
            providesTags: (_result, _error, id) => [{ type: 'Note', id }],
        }),
        createNote: builder.mutation({
            query: (note) => ({
                url: 'notes',
                method: 'POST',
                body: note,
            }),
            transformResponse: (response) => response.data,
            invalidatesTags: [
                { type: 'Note', id: 'LIST' },
                { type: 'Tag', id: 'LIST' },
            ],
        }),
        updateNote: builder.mutation({
            query: ({ id, ...note }) => ({
                url: `notes/${id}`,
                method: 'PUT',
                body: note,
            }),
            transformResponse: (response) => response.data,
            invalidatesTags: (_result, _error, { id }) => [
                { type: 'Note', id },
                { type: 'Note', id: 'LIST' },
                { type: 'Tag', id: 'LIST' },
            ],
        }),
        deleteNote: builder.mutation({
            query: (id) => ({
                url: `notes/${id}`,
                method: 'DELETE',
            }),
            invalidatesTags: (_result, _error, id) => [
                { type: 'Note', id },
                { type: 'Note', id: 'LIST' },
                { type: 'Tag', id: 'LIST' },
            ],
        }),
    }),
});

export const {
    useCreateNoteMutation,
    useDeleteNoteMutation,
    useGetNoteQuery,
    useGetNotesQuery,
    useUpdateNoteMutation,
} = notesApi;
