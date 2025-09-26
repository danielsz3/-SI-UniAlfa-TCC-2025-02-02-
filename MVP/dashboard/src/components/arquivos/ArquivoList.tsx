import {
    List,
    TextInput,
    useListContext,
} from 'react-admin';
import { Link } from 'react-router-dom';
import { Grid, Card, CardContent, Typography, Box } from '@mui/material';
import {
    PictureAsPdf as PdfIcon,
    InsertDriveFileOutlined as DocIcon, // Ícone genérico para documentos
    InsertDriveFile as GenericFileIcon, // Ícone para tipos desconhecidos
} from '@mui/icons-material';
import React from 'react';

// Filtros para a busca na lista (mantidos do seu código original)
const filters = [
    <TextInput label="Título" source="titulo" size="small" alwaysOn />,
];

/**
 * Retorna um ícone do Material-UI com base na extensão do arquivo.
 * @param {string} filename - O nome do arquivo (ex: "relatorio.pdf").
 * @returns {React.ReactElement} - O componente do ícone.
 */
const getIconForFileType = (filename: string): React.ReactElement => {
    if (!filename) {
        return <GenericFileIcon sx={{ fontSize: 40, color: 'grey.500' }} />;
    }

    const extension = filename.split('.').pop()?.toLowerCase();

    switch (extension) {
        case 'pdf':
            return <PdfIcon sx={{ fontSize: 40, color: 'red' }} />;
        case 'doc':
            return <DocIcon sx={{ fontSize: 40, color: 'blue' }} />;
        case 'docx':
            return <DocIcon sx={{ fontSize: 40, color: 'blue' }} />;
        case 'xls':
            return <DocIcon sx={{ fontSize: 40, color: 'green' }} />;
        case 'xlsx':
            return <DocIcon sx={{ fontSize: 40, color: 'green' }} />;
        default:
            return <GenericFileIcon sx={{ fontSize: 40, color: 'grey.700' }} />;
    }
};

// Função para converter bytes em formato legível
const formatFileSize = (bytes: number): string => {
    if (!bytes || bytes === 0) return '0 B';

    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    const size = (bytes / Math.pow(1024, i)).toFixed(1);

    return `${size} ${sizes[i]}`;
};

// Componente de Card individual para cada arquivo
const ArquivoGrid = () => {
    const { data, isLoading } = useListContext();

    if (isLoading || !data) return null

    return (
        <Grid
            container
            spacing={3}
            sx={{
                p: 2,
                backgroundColor: theme => theme.palette.background.default,
                borderColor: theme => theme.palette.background.default,
            }}
        >
            {data?.map(record => (
                <Grid key={record.id} size={{ xs: 12, md: 4, sm: 6 }}>
                    <Card
                        raised
                        sx={{
                            height: '100%',
                            display: 'flex',
                            textDecoration: 'none',
                            color: 'inherit',
                        }}
                        component={Link}
                        to={`/documentos/${record.id}`}
                    >
                        <Box
                            sx={{
                                width: 80,
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                backgroundColor: 'grey.200',
                            }}
                        >
                            {getIconForFileType(record?.arquivo)}
                        </Box>
                        <CardContent>
                            <Typography variant="h6" component="div" noWrap>
                                {record?.titulo || 'Sem título'}
                            </Typography>
                            <Typography variant="body2" color="text.secondary">
                                {record?.categoria || 'Sem categoria'}
                            </Typography>
                            <Typography variant="caption" color="text.secondary">
                                {formatFileSize(record?.tamanho) || 'Sem categoria'}
                            </Typography>
                        </CardContent>
                    </Card>
                </Grid>
            ))}
        </Grid>

    );
};

// Componente principal da lista
export const ArquivoList = () => (
    <List filters={filters} title="Documentos"
        sx={{
            '& .RaList-content': {
                boxShadow: 'none',
            },

        }}
    >
        <ArquivoGrid />
    </List>
);