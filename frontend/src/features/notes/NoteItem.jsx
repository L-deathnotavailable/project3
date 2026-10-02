import { useState } from 'react';
import FeedbackMessage from '../../components/FeedbackMessage';
import { getApiErrorMessage } from '../../services/apiErrors';
import { useDeleteNoteMutation } from './notesApi';

export default function NoteItem({ note, onEdit }) {
    const [deleteNote, { error, isLoading }] = useDeleteNoteMutation();
    const [confirmationVisible, setConfirmationVisible] = useState(false);

    async function handleDelete() {
        try {
            await deleteNote(note.id).unwrap();
        } catch {
            // Le message est rendu depuis l'état de la mutation.
        }
    }

    return (
        <article className="card note-card">
            <div className="note-card__content">
                <p className="note-card__text">{note.text}</p>
                <span className="tag-pill">{note.tag?.name ?? 'Sans tag'}</span>
            </div>

            <div className="card-actions">
                <button className="button button--ghost" onClick={() => onEdit(note)} type="button">Modifier</button>
                {!confirmationVisible ? (
                    <button className="button button--danger-ghost" onClick={() => setConfirmationVisible(true)} type="button">
                        Supprimer
                    </button>
                ) : (
                    <div className="confirm-actions" role="group" aria-label="Confirmer la suppression">
                        <span>Confirmer ?</span>
                        <button className="button button--danger" disabled={isLoading} onClick={handleDelete} type="button">
                            {isLoading ? 'Suppression…' : 'Oui'}
                        </button>
                        <button className="button button--ghost" onClick={() => setConfirmationVisible(false)} type="button">Non</button>
                    </div>
                )}
            </div>

            <FeedbackMessage>{getApiErrorMessage(error, '')}</FeedbackMessage>
        </article>
    );
}
