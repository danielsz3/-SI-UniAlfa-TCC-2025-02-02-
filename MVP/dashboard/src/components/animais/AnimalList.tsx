import {
    List,
    useListContext,
    SimpleList,
    TextInput,
    SelectInput,
} from 'react-admin'
import {
    Grid,
    Card,
    CardContent,
    Typography,
    useTheme,
    useMediaQuery,
    Box,
    Chip,
} from '@mui/material'
import { Link } from 'react-router-dom'
import { useCreatePath } from 'react-admin'

const CARD_HEIGHT = 250;

const filters = [
    <TextInput label="Nome" source="nome" size="small" alwaysOn />,
    <SelectInput
        label="Situação"
        source="situacao"
        value="disponivel"
        choices={[
            { id: 'disponivel', name: 'Disponível' },
            { id: 'adotado', name: 'Adotado' },
            { id: 'em_adocao', name: 'Em Adoção' },
            { id: 'em_aprovacao', name: 'Em Aprovação' },
        ]}
        alwaysOn
    />,
]

const chipTipos = {
    disponivel: { label: 'Disponível', bgCor: '#ffffff00', textCor: '#ffffff00' },
    adotado: { label: 'Adotado', bgCor: '#9c27b0', textCor: '#fff' },
    em_adocao: { label: 'Em Adoção', bgCor: '#ffe600', textCor: '#fff' },
    em_aprovacao: { label: 'Em Aprovação', bgCor: '#4caf50', textCor: '#fff' },
}

type Situacao = keyof typeof chipTipos;

function formatarDiferencaData(data: Date | string): string {
    const inicio = new Date(data)
    const agora = new Date()

    // Calcula diferença inicial em anos, meses e dias
    let anos = agora.getFullYear() - inicio.getFullYear()
    let meses = agora.getMonth() - inicio.getMonth()
    let dias = agora.getDate() - inicio.getDate()

    // Ajusta meses/dias negativos
    if (dias < 0) {
        meses -= 1
        const ultimoDiaMesAnterior = new Date(agora.getFullYear(), agora.getMonth(), 0).getDate()
        dias += ultimoDiaMesAnterior
    }

    if (meses < 0) {
        anos -= 1
        meses += 12
    }

    // Monta string final com pluralização
    const partes: string[] = []

    if (anos > 0) partes.push(`${anos} ${anos === 1 ? 'ano' : 'anos'}`)
    if (meses > 0) partes.push(`${meses} ${meses === 1 ? 'mês' : 'meses'}`)
    if (dias > 0 || partes.length === 0)
        partes.push(`${dias} ${dias === 1 ? 'dia' : 'dias'}`)

    // Junta partes com vírgulas e "e" no final
    if (partes.length > 1) {
        const ultimo = partes.pop()
        return partes.join(', ') + ' e ' + ultimo
    }
    return partes[0]
}


const AnimalGrid = () => {
    const { data, isLoading } = useListContext()
    const createPath = useCreatePath()

    if (isLoading || !data) return null

    return (
        <Grid container spacing={3} sx={{ p: 2, backgroundColor: (theme) => theme.palette.background.default }}>
            {data.map((record) => (
                <Grid key={record.id} size={{ xs: 12, xl: 2, lg: 3, md: 4, sm: 6 }} >
                    <Link
                        to={createPath({ resource: 'animais', id: record.id, type: 'edit' })}
                        style={{ textDecoration: 'none' }}
                    >
                        <Card
                            sx={{
                                position: 'relative',
                                height: CARD_HEIGHT,
                                overflow: 'hidden',
                                borderRadius: 2,
                            }}
                        >
                            <Box
                                sx={{
                                    position: 'absolute',
                                    top: 0,
                                    left: 0,
                                    width: '100%',
                                    height: '100%',
                                    backgroundImage: `url(${record.imagens.caminho ||
                                        import.meta.env.VITE_API_URL + '/imagens/' + record.imagens[0]?.caminho})`,
                                    backgroundSize: 'cover',
                                    backgroundPosition: 'center',
                                }}
                            />
                            <CardContent
                                sx={{
                                    position: 'absolute',
                                    bottom: 0,
                                    width: '100%',
                                    color: 'white',
                                    display: 'flex',
                                    flexDirection: 'row',
                                    alignItems: 'flex-start',
                                    justifyContent: 'space-between',
                                    background:
                                        'linear-gradient(to top, rgba(0,0,0,0.7),rgba(0,0,0,0.7),rgba(0,0,0,0.7), rgba(255, 255, 255, 0))',
                                    padding: 2,
                                    pb: "1rem !important",
                                }}
                            >
                                <div>
                                <Typography
                                    variant="body1"
                                    component="div"
                                    sx={{ fontWeight: 'bold' }}
                                >
                                    {record.nome}
                                </Typography>
                                <Typography
                                    variant="body2"
                                    component="div"
                                >
                                    {formatarDiferencaData(record.data_nascimento)}
                                </Typography>
                                </div>
                                <Chip
                                    label={chipTipos[record.situacao as Situacao]?.label ?? 'Indefinido'}
                                    sx={{
                                        mt: 1,
                                        bgcolor: chipTipos[record.situacao as Situacao]?.bgCor ?? '#9e9e9e',
                                        color: chipTipos[record.situacao as Situacao]?.textCor ?? '#333',
                                        fontWeight: 'bold',
                                    }}
                                />
                            </CardContent>
                        </Card>
                    </Link>
                </Grid>
            ))}
        </Grid>
    )
}

const AnimalList = () => {
    const theme = useTheme()
    const isSmall = useMediaQuery(theme.breakpoints.down('sm'))

    return (
        <>
            <List
                filters={filters}
                sx={{
                    '& .RaList-content': {
                        boxShadow: 'none',
                    },
                }}
            >
                {isSmall ? (
                    <SimpleList
                        leftAvatar={(record) =>
                            record.imagens.caminho ||
                            import.meta.env.VITE_API_URL + '/imagens/' + record.imagens[0]?.caminho
                        }
                        primaryText={(record) => record.nome}
                        tertiaryText={(record) => record.tipo_animal}
                        secondaryText={(record) => `${formatarDiferencaData(record.data_nascimento)}`}
                    />
                ) : (
                    <AnimalGrid />
                )}
            </List>
        </>
    )
}

export default AnimalList
