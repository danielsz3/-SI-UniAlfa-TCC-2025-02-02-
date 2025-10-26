import {
    DeleteButton,
    Edit,
    SaveButton,
    SimpleForm,
    TextInput,
    Toolbar,
    useRecordContext
} from 'react-admin';
import { Box, Typography, Divider, Grid, Chip, Stack, ChipProps } from '@mui/material';

// --- ÍCONES ---
import CottageOutlinedIcon from '@mui/icons-material/CottageOutlined';
import ChildFriendlyOutlinedIcon from '@mui/icons-material/ChildFriendlyOutlined';
import DirectionsRunOutlinedIcon from '@mui/icons-material/DirectionsRunOutlined';
import WindowOutlinedIcon from '@mui/icons-material/WindowOutlined';
import FenceOutlinedIcon from '@mui/icons-material/FenceOutlined';
import MonetizationOnOutlinedIcon from '@mui/icons-material/MonetizationOnOutlined';
import ThumbUpOutlinedIcon from '@mui/icons-material/ThumbUpOutlined';
import PetsOutlinedIcon from '@mui/icons-material/PetsOutlined';
import { formatarDiferencaData } from '../../utils/formatDate';
import ThumbDownIcon from '@mui/icons-material/ThumbDown';

type StatusAdocao = string;
type QtdPessoas = string;
type AcessoJanelas = string;
type AcessoMuros = string;
type RendaFamiliar = string;
type SobreRotina = Record<string, string>;

type Animal = {
    id: number;
    nome: string;
    sexo: string;
    data_nascimento: string;
    imagens: any[];
};

type Usuario = {
    endereco: any;
    id: number;
    nome: string;
    cpf: string;
    telefone?: string;
};

interface AdocaoRecord {
    id: number;
    status: StatusAdocao;
    qtd_pessoas_casa: QtdPessoas;
    possui_filhos: boolean | 0 | 1;
    sobre_rotina: SobreRotina | null;
    acesso_rua_janelas: AcessoJanelas;
    acesso_rua_portoes_muros: AcessoMuros;
    renda_familiar: RendaFamiliar;
    aceita_termos: boolean | 0 | 1;
    animal: Animal;
    usuario: Usuario;
}

const QTD_PESSOAS_LABELS: Record<QtdPessoas, string> = {
    sozinho: 'Sozinho',
    uma_pessoa: 'Uma pessoa',
    duas_pessoas: 'Duas pessoas',
    tres_pessoas: 'Três pessoas',
};

const ACESSO_JANELAS_LABELS: Record<string, string> = {
    janelas_telas_sem_acesso_rua: 'Janelas com telas, sem acesso a rua',
    janelas_sem_telas_com_acesso: 'Janelas sem telas, com acesso a rua',
    janelas_sem_telas_instalarei: 'Janelas sem telas, será instalado',
};

const ACESSO_MUROS_LABELS: Record<string, string> = {
    impedem_escape: 'Impedem o escape',
    permitem_acesso_rua: 'Permitem acesso à rua',
};

const RENDA_LABELS: Record<RendaFamiliar, string> = {
    acima_2_sm: 'Acima de 2 salários mínimos',
    abaixo_2_sm: 'Abaixo de 2 salários mínimos',
    outro: 'Outro valor',
};

const CHIP_COLORS: ChipProps['color'][] = [
    'primary',
    'secondary',
    'success',
    'info',
    'warning',
    'error',
];

interface InfoCardProps {
    icon: React.ReactElement;
    title: string;
    children: React.ReactNode;
}

const InfoCard: React.FC<InfoCardProps> = ({ icon, title, children }) => (
    <Box sx={{
        backgroundColor: '#f4f4f4',
        border: '1px solid #ddd',
        borderRadius: 3,
        p: 2,
        mb: 0,
        minHeight: '100px',
        display: 'flex',
        flexDirection: 'column'
    }}>
        <Stack direction="row" spacing={1} alignItems="center" sx={{ mb: 1 }}>
            {icon}
            <Typography variant="body1" fontWeight="bold">
                {title}
            </Typography>
        </Stack>

        <Box sx={{
            pl: 4,
            flexGrow: 1
        }}>
            {children}
        </Box>
    </Box>
);

/**
 * Componente principal que renderiza a visualização dos dados
 */
const VisualizacaoDados = () => {
    // AQUI a grande mudança: tipamos o useRecordContext
    const record = useRecordContext<AdocaoRecord>();
    if (!record) return null;

    // Função auxiliar tipada
    const formataBool = (value: boolean | number | undefined | null): string => {
        return value ? 'Sim' : 'Não';
    };

    const capitalize = (s: string): string => {
        if (typeof s !== 'string' || s.length === 0) return '';
        return s.charAt(0).toUpperCase() + s.slice(1);
    };

    // Tipagem inferida (correta) a partir do 'record'
    const animalInfo = `${record.animal?.sexo || 'Sexo desconhecido'} - ${formatarDiferencaData(record.animal?.data_nascimento)}`;

    const imageUrl = record.animal?.imagens[0]?.caminho
        ? import.meta.env.VITE_API_URL + `/imagens/${record.animal?.imagens[0]?.caminho}`
        : null;

    // Manipulador de erro para imagem (com tipagem de evento)
    const onImageError = (e: React.SyntheticEvent<HTMLImageElement, Event>) => {
        (e.target as HTMLImageElement).style.display = 'none';
        // Aqui você poderia mostrar o Box de fallback
    };

    const renderRotinaChips = () => {
        // Se 'sobre_rotina' for null ou um objeto vazio
        if (!record.sobre_rotina || Object.keys(record.sobre_rotina).length === 0) {
            return <Typography variant="body2">Nenhuma rotina informada.</Typography>;
        }

        // Transforma { "horarios": "manhã", "atividade": "passeio" }
        // em [ ["horarios", "manhã"], ["atividade", "passeio"] ]
        return Object.entries(record.sobre_rotina).map(([key, value], index) => (
            <Chip
                key={key}
                // (NOVO) Aplica a cor dinamicamente
                color={CHIP_COLORS[index % CHIP_COLORS.length]}
                // (NOVO) Capitaliza a chave e mostra o valor
                label={`${capitalize(key)}: ${value}`}
            />
        ));
    };

    return (
        <Box sx={{ width: '100%', p: 0 }}>
            {/* Pet Info */}
            <Box display="flex" alignItems="center"
                sx={{
                    border: '1px solid #ddd',
                    borderRadius: 2,
                    mb: 2,
                    p: 2,
                    backgroundColor: '#f9f9f9'
                }}>

                {/* Imagem do Pet */}
                <Box
                    component="img"
                    src={imageUrl || undefined} // 'src' prefere 'undefined' a 'null'
                    onError={onImageError}
                    sx={{
                        width: 80,
                        height: 80,
                        bgcolor: 'grey.300',
                        mr: 2,
                        borderRadius: '50%',
                        objectFit: 'cover'
                    }}
                />
                {/* Fallback caso a imagem não carregue ou não exista */}
                {!imageUrl && (
                    <Box sx={{
                        width: 80,
                        height: 80,
                        bgcolor: 'grey.300',
                        mr: 2,
                        borderRadius: '50%',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center'
                    }}>
                        <PetsOutlinedIcon color="primary" />
                    </Box>
                )}

                {/* Nome e Info do Pet */}
                <Box>
                    <Typography variant="h5" fontWeight="bold">{record.animal?.nome}</Typography>
                    <Typography variant="body1" color="text.secondary">
                        {animalInfo}
                    </Typography>
                </Box>
            </Box>

            {/* Usuário Info */}
            <Typography variant="h6">{record.usuario?.nome} - {record.usuario?.telefone || 'Telefone não informado'}</Typography>
            <Typography variant="body2" color="text.secondary" gutterBottom>
                {record.usuario?.endereco?.logradouro}, {record.usuario?.endereco?.bairro},
                {record.usuario?.endereco?.cidade} - {record.usuario?.endereco?.uf}
            </Typography>

            <Divider sx={{ my: 2 }} />

            <Grid container spacing={2}>
                <Grid size={{ xs: 12, md: 6 }}>
                    <Typography variant="h6" gutterBottom>Família e Rotina</Typography>

                    <Grid container spacing={2}>
                        <Grid size={{ xs: 12, md: 6 }}>
                            <InfoCard
                                icon={<CottageOutlinedIcon color="primary" />}
                                title="Reside com"
                            >
                                <Typography variant="body2">{QTD_PESSOAS_LABELS[record.qtd_pessoas_casa] || 'Não informado'}</Typography>
                            </InfoCard>
                        </Grid>

                        <Grid size={{ xs: 12, md: 6 }}>
                            <InfoCard
                                icon={<ChildFriendlyOutlinedIcon color="primary" />}
                                title="Tem filhos?"
                            >
                                <Typography variant="body2">
                                    {formataBool(record.possui_filhos)}
                                </Typography>
                            </InfoCard>
                        </Grid>

                        <Grid size={{ xs: 12, md: 12 }}>
                            <InfoCard
                                icon={<DirectionsRunOutlinedIcon color="primary" />}
                                title="Rotina"
                            >
                                <Stack direction="row" spacing={1} useFlexGap flexWrap="wrap">
                                    {renderRotinaChips()}
                                </Stack>
                            </InfoCard>
                        </Grid>
                    </Grid>
                </Grid>

                {/* Coluna da Direita: Segurança */}
                <Grid size={{ xs: 12, md: 6 }}>
                    <Typography variant="h6" gutterBottom>Segurança</Typography>

                    <Grid container spacing={2}>
                        <Grid size={{ xs: 12, md: 12 }}>
                            <InfoCard
                                icon={<WindowOutlinedIcon color="primary" />}
                                title="Acesso à rua - Janelas:"
                            >
                                <Typography variant="body2">{ACESSO_JANELAS_LABELS[record.acesso_rua_janelas] || 'Não informado'}</Typography>
                            </InfoCard>
                        </Grid>

                        <Grid size={{ xs: 12, md: 12 }}>
                            <InfoCard
                                icon={<FenceOutlinedIcon color="primary" />}
                                title="Acesso à rua - Muros:"
                            >
                                <Typography variant="body2">{ACESSO_MUROS_LABELS[record.acesso_rua_portoes_muros] || 'Não informado'}</Typography>
                            </InfoCard>
                        </Grid>
                    </Grid>
                </Grid>
            </Grid>

            <Divider sx={{ my: 2 }} />

            {/* --- Seção Observações Finais (2 Colunas) --- */}
            <Typography variant="h6" gutterBottom>Observações Finais</Typography>
            <Grid container spacing={2}>
                <Grid size={{ xs: 12, md: 6 }}>
                    <InfoCard
                        icon={<MonetizationOnOutlinedIcon color="primary" />}
                        title="Renda"
                    >
                        <Typography variant="body2">{RENDA_LABELS[record.renda_familiar] || 'Não informado'}</Typography>
                    </InfoCard>
                </Grid>

                <Grid size={{ xs: 12, md: 6 }}>
                    <InfoCard
                        icon={<ThumbUpOutlinedIcon color="primary" />}
                        title="Aceite e Permissão"
                    >
                        <Typography variant="body2">
                            {formataBool(record.aceita_termos)}
                        </Typography>
                    </InfoCard>
                </Grid>
            </Grid>
        </Box>
    );
};

export const AdocaoEdit = () => (
    <Edit title="Análise de Adoção"
        sx={{ width: '100%', maxWidth: 800, margin: '0 auto', mb: 10 }}
        transform={data => ({
                    ...data,
                    status: data.status = "aprovado",
                })}
    >
        <SimpleForm
            toolbar={
                <Toolbar sx={{ display: "flex", justifyContent: "space-between" }}>
                    <SaveButton
                        alwaysEnable
                        label='Aprovar'
                        icon={<ThumbUpOutlinedIcon />}
                    />
                    <DeleteButton
                        label="Rejeitar"
                        icon={<ThumbDownIcon />}
                    />
                </Toolbar>
            }
        >
            <VisualizacaoDados />
        </SimpleForm>
    </Edit>
);