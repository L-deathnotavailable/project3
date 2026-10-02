import { Link } from 'react-router-dom';

export default function AuthLayout({ title, description, children, footer }) {
    return (
        <main className="auth-page">
            <section className="auth-card" aria-labelledby="auth-title">
                <Link className="brand brand--centered" to="/login">Renote</Link>
                <header className="auth-card__header">
                    <h1 id="auth-title">{title}</h1>
                    <p>{description}</p>
                </header>
                {children}
                {footer && <div className="auth-card__footer">{footer}</div>}
            </section>
        </main>
    );
}
