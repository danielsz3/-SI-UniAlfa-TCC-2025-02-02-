import * as React from 'react';
import { Title, ListContextProvider, useListController } from 'react-admin';
import { Card } from '@mui/material';
import IntegrationsList from './IntegracoesList';

const IntegrationsPage = () => {
    // Busca os dados da rota /api/integrations
    const listContext = useListController({
        resource: 'integracoes',
        perPage: 25,
        sort: { field: 'service', order: 'ASC' }
    });

    return (
        // Fornece os dados para o componente filho
        <ListContextProvider value={listContext}>
            <Title title="Gerenciar Integrações" />
            <Card>
                <IntegrationsList />
            </Card>
        </ListContextProvider>
    );
};

export default IntegrationsPage;