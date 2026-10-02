import { useDispatch, useSelector } from 'react-redux';
import { NavLink, Outlet, useNavigate } from 'react-router-dom';
import { baseApi } from '../services/api';
import { useLogoutMutation } from '../features/auth/authApi';
import { clearCredentials, selectCurrentUser } from '../features/auth/authSlice';

function navigationClass({ isActive }) {
    return isActive ? 'navigation__link navigation__link--active' : 'navigation__link';
}

export default function AppLayout() {
    const dispatch = useDispatch();
    const navigate = useNavigate();
    const user = useSelector(selectCurrentUser);
    const [logout, { isLoading }] = useLogoutMutation();

    async function handleLogout() {
        try {
            await logout().unwrap();
        } catch {
            // La session locale doit pouvoir être fermée même si le serveur est indisponible.
        } finally {
            dispatch(clearCredentials());
            dispatch(baseApi.util.resetApiState());
            navigate('/login', { replace: true });
        }
    }

    return (
        <div className="app-shell">
            <header className="app-header">
                <NavLink className="brand" to="/dashboard">Renote</NavLink>

                <nav className="navigation" aria-label="Navigation principale">
                    <NavLink className={navigationClass} to="/dashboard">Accueil</NavLink>
                    <NavLink className={navigationClass} to="/notes">Notes</NavLink>
                    <NavLink className={navigationClass} to="/tags">Tags</NavLink>
                </nav>

                <div className="account">
                    <span className="account__name">{user?.name}</span>
                    <button className="button button--ghost" disabled={isLoading} onClick={handleLogout} type="button">
                        {isLoading ? 'Déconnexion…' : 'Se déconnecter'}
                    </button>
                </div>
            </header>

            <main className="app-content">
                <Outlet />
            </main>
        </div>
    );
}
