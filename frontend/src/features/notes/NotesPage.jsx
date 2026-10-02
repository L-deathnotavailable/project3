import { useState } from 'react';
import { Link } from 'react-router-dom';
import FeedbackMessage from '../../components/FeedbackMessage';
import LoadingState from '../../components/LoadingState';
import { getApiErrorMessage } from '../../services/apiErrors';
import { useGetTagsQuery } from '../tags/tagsApi';
import NoteForm from './NoteForm';
import NoteList from './NoteList';
import { useGetNotesQuery } from './notesApi';

export default function NotesPage() {
    const notesQuery = useGetNotesQuery();
    const tagsQuery = useGetTagsQuery();
    const [editingNote, setEditingNote] = useState(null);
    const [status, setStatus] = useState('');

    function handleSaved(message) {
        setEditingNote(null);
        setStatus(message);
    }

    if (notesQuery.isLoading || tagsQuery.isLoading) {
        return <LoadingState message="Chargement des notes…" />;
    }

    const queryError = notesQuery.error ?? tagsQuery.error;

    return (
        <div className="page-stack">
            <header className="page-heading page-heading--split">
                <div>
                    <p className="eyebrow">Notes</p>
                    <h1>Vos notes</h1>
                    <p>Créez, modifiez et classez vos notes.</p>
                </div>
                <Link className="button button--secondary" to="/tags">Gérer les tags</Link>
            </header>

            <FeedbackMessage>{getApiErrorMessage(queryError, '')}</FeedbackMessage>
            <FeedbackMessage tone="success">{status}</FeedbackMessage>

            {tagsQuery.data?.length === 0 && (
                <FeedbackMessage tone="info">
                    Créez d’abord un tag pour pouvoir ajouter une note.
                </FeedbackMessage>
            )}

            <section className="panel">
                <NoteForm
                    key={editingNote?.id ?? 'new-note'}
                    note={editingNote}
                    onCancel={() => setEditingNote(null)}
                    onSaved={handleSaved}
                    tags={tagsQuery.data ?? []}
                />
            </section>

            <section aria-labelledby="notes-list-title">
                <div className="section-heading">
                    <h2 id="notes-list-title">Liste des notes</h2>
                    {notesQuery.isFetching && <span className="muted">Actualisation…</span>}
                </div>
                <NoteList notes={notesQuery.data ?? []} onEdit={setEditingNote} />
            </section>
        </div>
    );
}
