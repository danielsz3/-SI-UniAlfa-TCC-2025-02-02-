export const authProvider = {
    login: async ({ username, password }: { username: string; password: string }) => {
        const request = new Request('https://fakestoreapi.com/auth/login', {
            method: 'POST',
            body: JSON.stringify({ username, password }),
            headers: new Headers({ 'Content-Type': 'application/json' }),
        });

        const response = await fetch(request);

        if (!response.ok) {
            throw new Error('Credenciais inválidas');
        }

        const { token } = await response.json();

        localStorage.setItem('authToken', token);
        return Promise.resolve();
    },

    logout: () => {
        localStorage.removeItem('authToken');
        return Promise.resolve();
    },

    checkAuth: () => {
        return localStorage.getItem('authToken') ? Promise.resolve() : Promise.reject();
    },

    checkError: (error: any) => {
        if (error.status === 401 || error.status === 403) {
            localStorage.removeItem('authToken');
            return Promise.reject();
        }
        return Promise.resolve();
    },

    getPermissions: () => Promise.resolve(),
};
