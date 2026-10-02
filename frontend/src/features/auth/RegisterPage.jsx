import { useState } from 'react';
import { useDispatch } from 'react-redux';
import { Link, useNavigate } from 'react-router-dom';
import AuthLayout from '../../components/AuthLayout';
import FeedbackMessage from '../../components/FeedbackMessage';
import { getApiErrorMessage, getFieldError, getValidationErrors } from '../../services/apiErrors';
import { useRegisterMutation } from './authApi';
import { setCredentials } from './authSlice';

const initialForm = {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
};

export default function RegisterPage() {
    const dispatch = useDispatch();
    const navigate = useNavigate();
    const [register, { isLoading }] = useRegisterMutation();
    const [form, setForm] = useState(initialForm);
    const [requestError, setRequestError] = useState(null);

    const validationErrors = getValidationErrors(requestError);

    function updateField(event) {
        setForm((current) => ({ ...current, [event.target.name]: event.target.value }));
    }

    async function handleSubmit(event) {
        event.preventDefault();
        setRequestError(null);

        try {
            const credentials = await register({ ...form, device_name: 'renote-react' }).unwrap();
            dispatch(setCredentials(credentials));
            navigate('/dashboard', { replace: true });
        } catch (error) {
            setRequestError(error);
        }
    }

    return (
        <AuthLayout
            title="Créer un compte"
            description="Commencez à organiser vos notes."
            footer={<p>Déjà inscrit ? <Link to="/login">Se connecter</Link></p>}
        >
            <form className="form-stack" onSubmit={handleSubmit} noValidate>
                <FeedbackMessage>{getApiErrorMessage(requestError, '')}</FeedbackMessage>

                <label className="field">
                    <span>Nom</span>
                    <input autoComplete="name" name="name" onChange={updateField} required value={form.name} />
                    <small className="field__error">{getFieldError(validationErrors, 'name')}</small>
                </label>

                <label className="field">
                    <span>Adresse e-mail</span>
                    <input autoComplete="email" name="email" onChange={updateField} required type="email" value={form.email} />
                    <small className="field__error">{getFieldError(validationErrors, 'email')}</small>
                </label>

                <label className="field">
                    <span>Mot de passe</span>
                    <input autoComplete="new-password" name="password" onChange={updateField} required type="password" value={form.password} />
                    <small className="field__error">{getFieldError(validationErrors, 'password')}</small>
                </label>

                <label className="field">
                    <span>Confirmation du mot de passe</span>
                    <input
                        autoComplete="new-password"
                        name="password_confirmation"
                        onChange={updateField}
                        required
                        type="password"
                        value={form.password_confirmation}
                    />
                </label>

                <button className="button button--primary button--full" disabled={isLoading} type="submit">
                    {isLoading ? 'Création…' : 'Créer mon compte'}
                </button>
            </form>
        </AuthLayout>
    );
}
