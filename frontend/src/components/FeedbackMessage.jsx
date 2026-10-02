export default function FeedbackMessage({ children, tone = 'error' }) {
    if (!children) {
        return null;
    }

    return (
        <div className={`feedback feedback--${tone}`} role={tone === 'error' ? 'alert' : 'status'}>
            {children}
        </div>
    );
}
