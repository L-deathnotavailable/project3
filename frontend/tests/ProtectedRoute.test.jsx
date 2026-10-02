import { configureStore } from '@reduxjs/toolkit';
import { render, screen } from '@testing-library/react';
import { Provider } from 'react-redux';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import authReducer from '../src/features/auth/authSlice';
import ProtectedRoute from '../src/routes/ProtectedRoute';

function renderRoute(auth) {
    const store = configureStore({
        reducer: { auth: authReducer },
        preloadedState: { auth },
    });

    return render(
        <Provider store={store}>
            <MemoryRouter initialEntries={['/notes']}>
                <Routes>
                    <Route element={<ProtectedRoute />}>
                        <Route path="/notes" element={<p>Notes privées</p>} />
                    </Route>
                    <Route path="/login" element={<p>Connexion requise</p>} />
                </Routes>
            </MemoryRouter>
        </Provider>,
    );
}

describe('ProtectedRoute', () => {
    it('redirige un visiteur sans token vers la connexion', () => {
        renderRoute({ token: null, user: null });

        expect(screen.getByText('Connexion requise')).toBeInTheDocument();
    });

    it('affiche la route privée avec un token', () => {
        renderRoute({
            token: 'token-de-test',
            user: { id: 1, name: 'Lara', email: 'lara@example.com' },
        });

        expect(screen.getByText('Notes privées')).toBeInTheDocument();
    });
});
