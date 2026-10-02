import { configureStore } from '@reduxjs/toolkit';
import { setupListeners } from '@reduxjs/toolkit/query';
import authReducer from '../features/auth/authSlice';
import { baseApi } from '../services/api';
import { authListenerMiddleware } from './authListenerMiddleware';

export const store = configureStore({
    reducer: {
        auth: authReducer,
        [baseApi.reducerPath]: baseApi.reducer,
    },
    middleware: (getDefaultMiddleware) =>
        getDefaultMiddleware()
            .prepend(authListenerMiddleware.middleware)
            .concat(baseApi.middleware),
});

setupListeners(store.dispatch);
