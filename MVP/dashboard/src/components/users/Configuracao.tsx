import { Card } from '@mui/material';
import React from 'react';
import { Title } from 'react-admin';

const ConfiguracaoUsuario: React.FC = () => (
    <Card style={{ padding: '20px' }}>
        <Title title="Configurações do Usuário" />
        <h2>Minha Página de Configuração</h2>
        <p>
            Aqui você pode colocar um formulário para o usuário editar o perfil,
            trocar a senha, etc.
        </p>
    </Card>
);

export default ConfiguracaoUsuario;