export const authProvider = {
    login: async ({ email, password }: { email: string; password: string }) => {
        const request = new Request('http://127.0.0.1:8000/api/login', {
            method: 'POST',
            body: JSON.stringify({ email, password }),
            headers: new Headers({ 'Content-Type': 'application/json' }),
        });

        const response = await fetch(request);

        console.log(response);

        if (!response.ok) {
            throw new Error('Credenciais inválidas');
        }

        const { access_token } = await response.json();

        localStorage.setItem('authToken', access_token);
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
