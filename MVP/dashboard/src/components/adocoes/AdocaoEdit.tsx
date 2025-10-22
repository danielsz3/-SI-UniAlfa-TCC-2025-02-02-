import {
    Edit,
    SimpleForm,
    TextInput,
    TextField,
    BooleanField,
    TextArrayField,
    useRecordContext
} from 'react-admin';
import { Box, Typography, Divider, Icon, Stack } from '@mui/material';
import CottageOutlinedIcon from '@mui/icons-material/CottageOutlined';
import ChildFriendlyOutlinedIcon from '@mui/icons-material/ChildFriendlyOutlined';

const VisualizacaoDados = () => {
    const record = useRecordContext();
    if (!record) return null;

    return (
        <Box sx={{ width: '100%', p: 0 }}>
            {/* Pet Info */}
            <Box display="flex" alignItems="center"
                sx={{ minWidth: '100%', border: '1px solid #ccc', borderRadius: 2, mb: 1 }}>
                <Box sx={{ width: 64, height: 64, bgcolor: 'grey.300', mr: 2 }} />
                <Box>
                    <Typography variant="h6">Nome do Pet</Typography>
                    <Typography variant="body2">Sexo - Idade</Typography>
                </Box>
            </Box>

            {/* Usuário Info */}
            <Typography variant="h6">{record.usuario?.nome} - {record.usuario?.telefone}</Typography>
            <Typography variant="body2">
                {record.usuario?.endereco?.logradouro}, {record.usuario?.endereco?.bairro},
                {record.usuario?.endereco?.cidade} - {record.usuario?.endereco?.uf}
            </Typography>

            <Divider sx={{ my: 1 }} />
            <Box sx={{ display: 'flex', justifyContent: 'space-between' }}>
                {/* Família e Rotina */}
                <Box sx={{}}>
                    <Typography variant="subtitle1">Família e Rotina</Typography>

                    <Box sx={{ bgcolor: "#ccc", p: 3, borderRadius: 10 }}>
                        <Stack direction="row" spacing={1}>
                            <CottageOutlinedIcon />
                            <Typography variant="body1">Reside com</Typography>
                        </Stack>
                        <TextField source="qtd_pessoas_casa" label="Reside com" />
                    </Box>

                    <Box sx={{ bgcolor: "#ccc", p: 3, borderRadius: 10 }}>
                        <Stack direction="row" spacing={1}>
                            <ChildFriendlyOutlinedIcon />
                            <Typography variant="body1">Tem filhos ?</Typography>
                        </Stack>
                        <BooleanField source="possui_filhos" label="Tem filhos?" />
                    </Box>

                    <Box sx={{ bgcolor: "#ccc", p: 3, borderRadius: 10 }}>
                        <Stack direction="row" spacing={1}>
                            <ChildFriendlyOutlinedIcon />
                            <Typography variant="body1">Rotina</Typography>
                        </Stack>
                        <TextArrayField
                            source="sobre_rotina"
                        />
                    </Box>
                </Box>

                {/* Segurança */}
                <Box>
                    <Typography variant="subtitle1">Segurança</Typography>
                    <TextField source="acesso_rua_janelas" label="Acesso à rua - Janelas" />
                    <TextField source="acesso_rua_portoes_muros" label="Acesso à rua - Muros" />
                </Box>
            </Box>

            {/* Observações Finais */}
            <Typography variant="subtitle1">Observações Finais</Typography>
            <TextField source="renda_familiar" label="Renda" />
            <BooleanField source="aceita_termos" label="Aceite e Permissão" />
        </Box>
    );
};

export const AdocaoEdit = () => (
    <Edit title="Análise de Adoção">
        <SimpleForm>
            <VisualizacaoDados />

            <TextInput source="status" label="Status da Adoção" fullWidth />

        </SimpleForm>
    </Edit>
);
