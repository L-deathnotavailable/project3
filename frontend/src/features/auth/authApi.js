import { baseApi } from '../../services/api';

function transformAuthenticationResponse(response) {
    return {
        ...response.data,
        message: response.message,
    };
}

export const authApi = baseApi.injectEndpoints({
    endpoints: (builder) => ({
        login: builder.mutation({
            query: (credentials) => ({
                url: 'login',
                method: 'POST',
                body: credentials,
            }),
            transformResponse: transformAuthenticationResponse,
        }),
        register: builder.mutation({
            query: (account) => ({
                url: 'register',
                method: 'POST',
                body: account,
            }),
            transformResponse: transformAuthenticationResponse,
        }),
        logout: builder.mutation({
            query: () => ({
                url: 'logout',
                method: 'POST',
            }),
        }),
    }),
});

export const { useLoginMutation, useLogoutMutation, useRegisterMutation } = authApi;
