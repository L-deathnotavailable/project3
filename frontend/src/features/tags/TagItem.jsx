import { useState } from 'react';
import FeedbackMessage from '../../components/FeedbackMessage';
import { getApiErrorMessage } from '../../services/apiErrors';
import { useDeleteTagMutation } from './tagsApi';

export default function TagItem({ tag, onEdit }) {
    const [deleteTag, { error, isLoading }] = useDeleteTagMutation();
    const [confirmationVisible, setConfirmationVisible] = useState(false);

    async function handleDelete() {
        try {
            await deleteTag(tag.id).unwrap();
        } catch {
            // Une erreur 409 est affichée lorsque le tag est encore utilisé.
        }
    }

    return (
        <article className="card tag-card">
            <div>
                <h3>{tag.name}</h3>
                <p className="muted">{tag.notes_count ?? 0} note(s)</p>
            </div>

            <div className="card-actions">
                <button className="button button--ghost" onClick={() => onEdit(tag)} type="button">Modifier</button>
                {!confirmationVisible ? (
                    <button className="button button--danger-ghost" onClick={() => setConfirmationVisible(true)} type="button">
                        Supprimer
                    </button>
                ) : (
                    <div className="confirm-actions" role="group" aria-label="Confirmer la suppression">
                        <button className="button button--danger" disabled={isLoading} onClick={handleDelete} type="button">
                            {isLoading ? 'Suppression…' : 'Confirmer'}
                        </button>
                        <button className="button button--ghost" onClick={() => setConfirmationVisible(false)} type="button">Annuler</button>
                    </div>
                )}
            </div>

            <FeedbackMessage>{getApiErrorMessage(error, '')}</FeedbackMessage>
        </article>
    );
}
