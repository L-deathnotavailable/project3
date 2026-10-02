import { useState } from 'react';
import FeedbackMessage from '../../components/FeedbackMessage';
import { getApiErrorMessage, getFieldError, getValidationErrors } from '../../services/apiErrors';
import { useCreateNoteMutation, useUpdateNoteMutation } from './notesApi';

export default function NoteForm({ note = null, tags, onCancel, onSaved }) {
    const [form, setForm] = useState({
        text: note?.text ?? '',
        tag_id: note?.tag?.id?.toString() ?? '',
    });
    const [createNote, createState] = useCreateNoteMutation();
    const [updateNote, updateState] = useUpdateNoteMutation();

    const mutationState = note ? updateState : createState;
    const validationErrors = getValidationErrors(mutationState.error);

    function updateField(event) {
        setForm((current) => ({ ...current, [event.target.name]: event.target.value }));
    }

    async function handleSubmit(event) {
        event.preventDefault();

        const payload = {
            text: form.text,
            tag_id: Number(form.tag_id),
        };

        try {
            if (note) {
                await updateNote({ id: note.id, ...payload }).unwrap();
            } else {
                await createNote(payload).unwrap();
                setForm({ text: '', tag_id: '' });
            }

            onSaved(note ? 'Note mise à jour.' : 'Note créée.');
        } catch {
            // RTK Query expose déjà l'erreur dans mutationState.error.
        }
    }

    return (
        <form className="form-stack" onSubmit={handleSubmit} noValidate>
            <div className="section-heading">
                <div>
                    <h2>{note ? 'Modifier la note' : 'Nouvelle note'}</h2>
                    <p>Une note doit être associée à un tag.</p>
                </div>
                {note && (
                    <button className="button button--ghost" onClick={onCancel} type="button">
                        Annuler
                    </button>
                )}
            </div>

            <FeedbackMessage>{getApiErrorMessage(mutationState.error, '')}</FeedbackMessage>

            <label className="field">
                <span>Texte</span>
                <textarea
                    name="text"
                    onChange={updateField}
                    placeholder="Écrivez votre note…"
                    required
                    rows="5"
                    value={form.text}
                />
                <small className="field__error">{getFieldError(validationErrors, 'text')}</small>
            </label>

            <label className="field">
                <span>Tag</span>
                <select name="tag_id" onChange={updateField} required value={form.tag_id}>
                    <option value="">Sélectionnez un tag</option>
                    {tags.map((tag) => <option key={tag.id} value={tag.id}>{tag.name}</option>)}
                </select>
                <small className="field__error">{getFieldError(validationErrors, 'tag_id')}</small>
            </label>

            <button
                className="button button--primary"
                disabled={mutationState.isLoading || tags.length === 0}
                type="submit"
            >
                {mutationState.isLoading ? 'Enregistrement…' : note ? 'Enregistrer' : 'Ajouter la note'}
            </button>
        </form>
    );
}
