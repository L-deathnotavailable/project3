import EmptyState from '../../components/EmptyState';
import NoteItem from './NoteItem';

export default function NoteList({ notes, onEdit }) {
    if (notes.length === 0) {
        return <EmptyState>Aucune note pour le moment.</EmptyState>;
    }

    return (
        <div className="card-list">
            {notes.map((note) => <NoteItem key={note.id} note={note} onEdit={onEdit} />)}
        </div>
    );
}
