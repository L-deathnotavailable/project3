export default function LoadingState({ message = 'Chargement…' }) {
    return <p className="state-message" role="status">{message}</p>;
}
