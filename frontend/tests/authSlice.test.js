import authReducer, { clearCredentials, setCredentials } from '../src/features/auth/authSlice';

describe('authSlice', () => {
    it('enregistre les informations de connexion', () => {
        const credentials = {
            token: 'token-de-test',
            user: { id: 1, name: 'Lara', email: 'lara@example.com' },
        };

        expect(authReducer(undefined, setCredentials(credentials))).toEqual(credentials);
    });

    it('efface les informations à la déconnexion', () => {
        const state = {
            token: 'token-de-test',
            user: { id: 1, name: 'Lara', email: 'lara@example.com' },
        };

        expect(authReducer(state, clearCredentials())).toEqual({ token: null, user: null });
    });
});
