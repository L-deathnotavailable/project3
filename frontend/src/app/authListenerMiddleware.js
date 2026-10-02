import { createListenerMiddleware, isAnyOf } from '@reduxjs/toolkit';
import { clearCredentials, setCredentials } from '../features/auth/authSlice';
import { clearStoredAuth, storeAuth } from '../features/auth/authStorage';

export const authListenerMiddleware = createListenerMiddleware();

authListenerMiddleware.startListening({
    matcher: isAnyOf(setCredentials, clearCredentials),
    effect: (action, listenerApi) => {
        if (clearCredentials.match(action)) {
            clearStoredAuth();
            return;
        }

        storeAuth(listenerApi.getState().auth);
    },
});
