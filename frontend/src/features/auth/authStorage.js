const STORAGE_KEY = 'renote.auth';

export function loadStoredAuth() {
    if (typeof window === 'undefined') {
        return null;
    }

    try {
        const storedValue = window.sessionStorage.getItem(STORAGE_KEY);
        const auth = storedValue ? JSON.parse(storedValue) : null;

        if (!auth?.token || !auth?.user) {
            return null;
        }

        return {
            token: auth.token,
            user: auth.user,
        };
    } catch {
        window.sessionStorage.removeItem(STORAGE_KEY);
        return null;
    }
}

export function storeAuth(auth) {
    if (typeof window !== 'undefined') {
        window.sessionStorage.setItem(STORAGE_KEY, JSON.stringify(auth));
    }
}

export function clearStoredAuth() {
    if (typeof window !== 'undefined') {
        window.sessionStorage.removeItem(STORAGE_KEY);
    }
}
