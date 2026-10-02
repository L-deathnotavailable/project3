import { createBrowserRouter, Navigate } from 'react-router-dom';
import AppLayout from '../components/AppLayout';
import DashboardPage from '../features/dashboard/DashboardPage';
import LoginPage from '../features/auth/LoginPage';
import RegisterPage from '../features/auth/RegisterPage';
import NotesPage from '../features/notes/NotesPage';
import TagsPage from '../features/tags/TagsPage';
import NotFoundPage from '../pages/NotFoundPage';
import ProtectedRoute from '../routes/ProtectedRoute';
import PublicOnlyRoute from '../routes/PublicOnlyRoute';

export const router = createBrowserRouter([
    {
        element: <PublicOnlyRoute />,
        children: [
            { path: '/login', element: <LoginPage /> },
            { path: '/register', element: <RegisterPage /> },
        ],
    },
    {
        element: <ProtectedRoute />,
        children: [
            {
                element: <AppLayout />,
                children: [
                    { path: '/', element: <Navigate to="/dashboard" replace /> },
                    { path: '/dashboard', element: <DashboardPage /> },
                    { path: '/notes', element: <NotesPage /> },
                    { path: '/tags', element: <TagsPage /> },
                ],
            },
        ],
    },
    { path: '*', element: <NotFoundPage /> },
]);
