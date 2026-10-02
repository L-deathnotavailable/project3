import { useState } from 'react';
import { useDispatch } from 'react-redux';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import AuthLayout from '../../components/AuthLayout';
import FeedbackMessage from '../../components/FeedbackMessage';
import { getApiErrorMessage, getFieldError, getValidationErrors } from '../../services/apiErrors';
import { useLoginMutation } from './authApi';
import { setCredentials } from './authSlice';

export default function LoginPage() {
    const dispatch = useDispatch();
    const location = useLocation();
    const navigate = useNavigate();
    const [login, { isLoading }] = useLoginMutation();
    const [form, setForm] = useState({ email: '', password: '' });
    const [requestError, setRequestError] = useState(null);

    const validationErrors = getValidationErrors(requestError);

    function updateField(event) {
        setForm((current) => ({ ...current, [event.target.name]: event.target.value }));
    }

    async function handleSubmit(event) {
        event.preventDefault();
        setRequestError(null);

        try {
            const credentials = await login({ ...form, device_name: 'renote-react' }).unwrap();
            dispatch(setCredentials(credentials));
            navigate(location.state?.from ?? '/dashboard', { replace: true });
        } catch (error) {
            setRequestError(error);
        }
    }

    return (
        <AuthLayout
            title="Connexion"
            description="Retrouvez vos notes et vos tags."
            footer={<p>Pas encore de compte ? <Link to="/register">S’inscrire</Link></p>}
        >
            <form className="form-stack" onSubmit={handleSubmit} noValidate>
                <FeedbackMessage>{getApiErrorMessage(requestError, '')}</FeedbackMessage>

                <label className="field">
                    <span>Adresse e-mail</span>
                    <input
                        autoComplete="email"
                        name="email"
                        onChange={updateField}
                        required
                        type="email"
                        value={form.email}
                    />
                    <small className="field__error">{getFieldError(validationErrors, 'email')}</small>
                </label>

                <label className="field">
                    <span>Mot de passe</span>
                    <input
                        autoComplete="current-password"
                        name="password"
                        onChange={updateField}
                        required
                        type="password"
                        value={form.password}
                    />
                    <small className="field__error">{getFieldError(validationErrors, 'password')}</small>
                </label>

                <button className="button button--primary button--full" disabled={isLoading} type="submit">
                    {isLoading ? 'Connexion…' : 'Se connecter'}
                </button>
            </form>
        </AuthLayout>
    );
}
