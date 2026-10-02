import { useState } from 'react';
import FeedbackMessage from '../../components/FeedbackMessage';
import { getApiErrorMessage, getFieldError, getValidationErrors } from '../../services/apiErrors';
import { useCreateTagMutation, useUpdateTagMutation } from './tagsApi';

export default function TagForm({ tag = null, onCancel, onSaved }) {
    const [name, setName] = useState(tag?.name ?? '');
    const [createTag, createState] = useCreateTagMutation();
    const [updateTag, updateState] = useUpdateTagMutation();
    const mutationState = tag ? updateState : createState;
    const validationErrors = getValidationErrors(mutationState.error);

    async function handleSubmit(event) {
        event.preventDefault();

        try {
            if (tag) {
                await updateTag({ id: tag.id, name }).unwrap();
            } else {
                await createTag({ name }).unwrap();
                setName('');
            }

            onSaved(tag ? 'Tag mis à jour.' : 'Tag créé.');
        } catch {
            // RTK Query expose déjà l'erreur dans mutationState.error.
        }
    }

    return (
        <form className="form-stack" onSubmit={handleSubmit} noValidate>
            <div className="section-heading">
                <div>
                    <h2>{tag ? 'Modifier le tag' : 'Nouveau tag'}</h2>
                    <p>Les tags permettent de classer les notes.</p>
                </div>
                {tag && <button className="button button--ghost" onClick={onCancel} type="button">Annuler</button>}
            </div>

            <FeedbackMessage>{getApiErrorMessage(mutationState.error, '')}</FeedbackMessage>

            <label className="field">
                <span>Nom</span>
                <input
                    maxLength="50"
                    name="name"
                    onChange={(event) => setName(event.target.value)}
                    placeholder="Exemple : Travail"
                    required
                    value={name}
                />
                <small className="field__error">{getFieldError(validationErrors, 'name')}</small>
            </label>

            <button className="button button--primary" disabled={mutationState.isLoading} type="submit">
                {mutationState.isLoading ? 'Enregistrement…' : tag ? 'Enregistrer' : 'Ajouter le tag'}
            </button>
        </form>
    );
}
