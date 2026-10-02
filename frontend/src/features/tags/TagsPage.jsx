import { useState } from 'react';
import { Link } from 'react-router-dom';
import FeedbackMessage from '../../components/FeedbackMessage';
import LoadingState from '../../components/LoadingState';
import EmptyState from '../../components/EmptyState';
import { getApiErrorMessage } from '../../services/apiErrors';
import TagForm from './TagForm';
import TagItem from './TagItem';
import { useGetTagsQuery } from './tagsApi';

export default function TagsPage() {
    const tagsQuery = useGetTagsQuery();
    const [editingTag, setEditingTag] = useState(null);
    const [status, setStatus] = useState('');

    if (tagsQuery.isLoading) {
        return <LoadingState message="Chargement des tags…" />;
    }

    function handleSaved(message) {
        setEditingTag(null);
        setStatus(message);
    }

    return (
        <div className="page-stack">
            <header className="page-heading page-heading--split">
                <div>
                    <p className="eyebrow">Tags</p>
                    <h1>Classement des notes</h1>
                    <p>Créez les catégories utilisées par vos notes.</p>
                </div>
                <Link className="button button--secondary" to="/notes">Voir les notes</Link>
            </header>

            <FeedbackMessage>{getApiErrorMessage(tagsQuery.error, '')}</FeedbackMessage>
            <FeedbackMessage tone="success">{status}</FeedbackMessage>

            <section className="panel">
                <TagForm
                    key={editingTag?.id ?? 'new-tag'}
                    onCancel={() => setEditingTag(null)}
                    onSaved={handleSaved}
                    tag={editingTag}
                />
            </section>

            <section aria-labelledby="tags-list-title">
                <div className="section-heading">
                    <h2 id="tags-list-title">Liste des tags</h2>
                    {tagsQuery.isFetching && <span className="muted">Actualisation…</span>}
                </div>

                {tagsQuery.data?.length ? (
                    <div className="tag-grid">
                        {tagsQuery.data.map((tag) => <TagItem key={tag.id} onEdit={setEditingTag} tag={tag} />)}
                    </div>
                ) : (
                    <EmptyState>Aucun tag pour le moment.</EmptyState>
                )}
            </section>
        </div>
    );
}
