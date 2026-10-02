import { Link } from 'react-router-dom';
import FeedbackMessage from '../../components/FeedbackMessage';
import LoadingState from '../../components/LoadingState';
import { getApiErrorMessage } from '../../services/apiErrors';
import { useGetNotesQuery } from '../notes/notesApi';
import { useGetTagsQuery } from '../tags/tagsApi';

export default function DashboardPage() {
    const notesQuery = useGetNotesQuery();
    const tagsQuery = useGetTagsQuery();

    if (notesQuery.isLoading || tagsQuery.isLoading) {
        return <LoadingState message="Chargement du tableau de bord…" />;
    }

    const error = notesQuery.error ?? tagsQuery.error;

    return (
        <div className="page-stack">
            <header className="page-heading">
                <p className="eyebrow">Tableau de bord</p>
                <h1>Bienvenue dans Renote</h1>
                <p>Créez vos notes et classez-les avec des tags.</p>
            </header>

            <FeedbackMessage>{getApiErrorMessage(error, '')}</FeedbackMessage>

            <div className="dashboard-grid">
                <Link className="metric-card" to="/notes">
                    <span className="metric-card__value">{notesQuery.data?.length ?? 0}</span>
                    <span className="metric-card__label">Notes</span>
                    <span className="metric-card__action">Consulter les notes</span>
                </Link>

                <Link className="metric-card" to="/tags">
                    <span className="metric-card__value">{tagsQuery.data?.length ?? 0}</span>
                    <span className="metric-card__label">Tags</span>
                    <span className="metric-card__action">Gérer les tags</span>
                </Link>
            </div>
        </div>
    );
}
