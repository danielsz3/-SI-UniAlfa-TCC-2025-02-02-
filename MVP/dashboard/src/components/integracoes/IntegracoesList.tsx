import * as React from 'react';
import { useListContext, useNotify, useDelete, useRefresh } from 'react-admin';
import { CardContent, Button, Box, Typography } from '@mui/material';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import { Loading } from '../Loading';

// Lista "mestra" de todas as integrações que sua plataforma oferece
const AVALIABLE_SERVICES = [
    {
        id: 'instagram',
        name: 'Instagram',
        description: 'Conecte sua conta para habilitar serviços automáticos.',
        connectUrl: 'http://127.0.0.1:8000/login/instagram', // URL do backend Laravel
    },
    {
        id: 'whatsapp',
        name: 'Whatsapp',
        description: 'Conecte seu número para enviar mensagens.',
        connectUrl: 'http://127.0.0.1:8000/login/facebook',
    },
];

const IntegrationsList = () => {
    const { data: connectedIntegrations, isLoading } = useListContext();
    const notify = useNotify();
    const refresh = useRefresh();

    const [deleteIntegration, { isLoading: isDeleting }] = useDelete();

    const handleDisconnect = (serviceId: string) => {
        deleteIntegration(
            'integrations',
            { id: serviceId },
            { 
                onSuccess: () => {
                    notify('Integração removida.', { type: 'info' });
                    refresh();
                },
                onError: (error) => notify(`Erro: ${error.message || 'não foi possível remover a integração'}.`, { type: 'error' })
            }
        );
    };
    
    if (isLoading) {
        return <Loading/>;
    }

    // Cruza a lista de serviços disponíveis com a lista de serviços já conectados
    const mergedServices = AVALIABLE_SERVICES.map(service => {
        const connectedIntegration = connectedIntegrations?.find(integration => integration.service === service.id);
        return { 
            ...service, 
            isConnected: !!connectedIntegration,
            username: connectedIntegration?.external_username 
        };
    });

    return (
        <CardContent>
            {mergedServices.map(service => (
                <Box key={service.id} sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '16px', borderBottom: '1px solid #e0e0e0', '&:last-child': { borderBottom: 0 } }}>
                    <Box>
                        <Typography variant="h6">{service.name}</Typography>
                        <Typography variant="body2" color="textSecondary">
                            {service.isConnected 
                                ? `Conectado como: @${service.username}` 
                                : service.description}
                        </Typography>
                    </Box>
                    <Box>
                        {service.isConnected ? (
                            <Button
                                variant="outlined"
                                color="secondary"
                                onClick={() => handleDisconnect(service.id)}
                                disabled={isDeleting}
                                startIcon={<CheckCircleIcon sx={{ color: 'green' }} />}
                            >
                                {isDeleting ? 'Removendo...' : 'Conectado'}
                            </Button>
                        ) : (
                            <Button variant="contained" href={service.connectUrl}>
                                Conectar
                            </Button>
                        )}
                    </Box>
                </Box>
            ))}
        </CardContent>
    );
};

export default IntegrationsList;