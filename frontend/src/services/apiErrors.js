export function getApiErrorMessage(error, fallback = 'Une erreur est survenue.') {
    if (error?.status === 'FETCH_ERROR') {
        return 'Impossible de contacter l’API. Vérifiez que le serveur Laravel est démarré.';
    }

    return error?.data?.message ?? error?.error ?? fallback;
}

export function getValidationErrors(error) {
    return error?.data?.data?.errors ?? {};
}

export function getFieldError(errors, field) {
    const messages = errors[field];

    return Array.isArray(messages) ? messages[0] : null;
}
