import { baseApi } from '../../services/api';

export const tagsApi = baseApi.injectEndpoints({
    endpoints: (builder) => ({
        getTags: builder.query({
            query: () => 'tags',
            transformResponse: (response) => response.data,
            providesTags: (result = []) => [
                { type: 'Tag', id: 'LIST' },
                ...result.map((tag) => ({ type: 'Tag', id: tag.id })),
            ],
        }),
        getTag: builder.query({
            query: (id) => `tags/${id}`,
            transformResponse: (response) => response.data,
            providesTags: (_result, _error, id) => [{ type: 'Tag', id }],
        }),
        createTag: builder.mutation({
            query: (tag) => ({
                url: 'tags',
                method: 'POST',
                body: tag,
            }),
            transformResponse: (response) => response.data,
            invalidatesTags: [{ type: 'Tag', id: 'LIST' }],
        }),
        updateTag: builder.mutation({
            query: ({ id, ...tag }) => ({
                url: `tags/${id}`,
                method: 'PUT',
                body: tag,
            }),
            transformResponse: (response) => response.data,
            invalidatesTags: (_result, _error, { id }) => [
                { type: 'Tag', id },
                { type: 'Tag', id: 'LIST' },
                { type: 'Note', id: 'LIST' },
            ],
        }),
        deleteTag: builder.mutation({
            query: (id) => ({
                url: `tags/${id}`,
                method: 'DELETE',
            }),
            invalidatesTags: (_result, _error, id) => [
                { type: 'Tag', id },
                { type: 'Tag', id: 'LIST' },
            ],
        }),
    }),
});

export const {
    useCreateTagMutation,
    useDeleteTagMutation,
    useGetTagQuery,
    useGetTagsQuery,
    useUpdateTagMutation,
} = tagsApi;
