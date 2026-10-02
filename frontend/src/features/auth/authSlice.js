import { createSlice } from '@reduxjs/toolkit';
import { loadStoredAuth } from './authStorage';

const storedAuth = loadStoredAuth();

const initialState = {
    user: storedAuth?.user ?? null,
    token: storedAuth?.token ?? null,
};

const authSlice = createSlice({
    name: 'auth',
    initialState,
    reducers: {
        setCredentials: (state, action) => {
            state.user = action.payload.user;
            state.token = action.payload.token;
        },
        clearCredentials: (state) => {
            state.user = null;
            state.token = null;
        },
    },
});

export const { clearCredentials, setCredentials } = authSlice.actions;
export const selectCurrentUser = (state) => state.auth.user;
export const selectAuthToken = (state) => state.auth.token;
export const selectIsAuthenticated = (state) => Boolean(state.auth.token);
export default authSlice.reducer;
