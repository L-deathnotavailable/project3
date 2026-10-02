import { getApiErrorMessage, getFieldError, getValidationErrors } from '../src/services/apiErrors';

describe('apiErrors', () => {
    const error = {
        status: 422,
        data: {
            message: 'Les données fournies sont invalides.',
            data: {
                errors: {
                    email: ['Cette adresse e-mail est déjà utilisée.'],
                },
            },
        },
    };

    it('récupère le message général de l’API', () => {
        expect(getApiErrorMessage(error)).toBe('Les données fournies sont invalides.');
    });

    it('récupère la première erreur d’un champ', () => {
        const errors = getValidationErrors(error);

        expect(getFieldError(errors, 'email')).toBe('Cette adresse e-mail est déjà utilisée.');
        expect(getFieldError(errors, 'password')).toBeNull();
    });

    it('explique une API injoignable sans afficher l’erreur technique', () => {
        expect(getApiErrorMessage({ status: 'FETCH_ERROR' })).toContain('Impossible de contacter l’API');
    });
});
