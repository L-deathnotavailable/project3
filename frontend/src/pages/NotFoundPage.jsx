import { Link } from 'react-router-dom';

export default function NotFoundPage() {
    return (
        <main className="not-found">
            <p className="eyebrow">Erreur 404</p>
            <h1>Cette page n’existe pas.</h1>
            <Link className="button button--primary" to="/">Revenir à l’accueil</Link>
        </main>
    );
}
