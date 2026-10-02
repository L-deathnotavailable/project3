import { createApi, fetchBaseQuery } from '@reduxjs/toolkit/query/react';
import { clearCredentials } from '../features/auth/authSlice';

function normalizeBaseUrl(value) {
    const baseUrl = value?.trim();

    if (!baseUrl) {
        return '';
    }

    return `${baseUrl.replace(/\/+$/, '')}/`;
}

const rawBaseQuery = fetchBaseQuery({
    baseUrl: normalizeBaseUrl(import.meta.env.VITE_API_BASE_URL),
    prepareHeaders: (headers, { getState }) => {
        headers.set('Accept', 'application/json');
        headers.set('Content-Type', 'application/json');

        const token = getState().auth.token;

        if (token) {
            headers.set('Authorization', `Bearer ${token}`);
        }

        return headers;
    },
});

async function baseQueryWithAuthentication(args, api, extraOptions) {
    const result = await rawBaseQuery(args, api, extraOptions);

    if (result.error?.status === 401) {
        api.dispatch(clearCredentials());
        api.dispatch(baseApi.util.resetApiState());
    }

    return result;
}

export const baseApi = createApi({
    reducerPath: 'api',
    baseQuery: baseQueryWithAuthentication,
    tagTypes: ['Note', 'Tag'],
    endpoints: () => ({}),
});
