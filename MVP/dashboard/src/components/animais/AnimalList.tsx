import {
    List,
    useListContext,
    SimpleList,
    TextInput,
} from 'react-admin'
import {
    Grid,
    Card,
    CardContent,
    Typography,
    useTheme,
    useMediaQuery,
    Box,
    Tabs,
    Tab,
} from '@mui/material'
import { Link } from 'react-router-dom'
import { useCreatePath } from 'react-admin'
import { SetStateAction, useState } from 'react'

const filters = [
    <TextInput label="Nome" source="nome" size="small" alwaysOn />,
]

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
                <Grid key={record.id} size={{ xs: 12, lg: 3, md: 4, sm: 6 }} >
                    <Link
                        to={createPath({ resource: 'animais', id: record.id, type: 'edit' })}
                        style={{ textDecoration: 'none' }}
                    >
                        <Card
                            sx={{
                                position: 'relative',
                                height: 200,
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
                                    background:
                                        'linear-gradient(to top, rgba(0,0,0,0.7),rgba(0,0,0,0.7),rgba(0,0,0,0.7), rgba(255, 255, 255, 0))',
                                    padding: 2,
                                    pb: "1rem !important",
                                }}
                            >
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

    // Estado para controlar a aba ativa
    const [situacao, setSituacao] = useState('disponivel')

    const handleChange = (_event: unknown, newValue: SetStateAction<string>) => {
        setSituacao(newValue)
    }

    return (
        <>
            {/* Abas de Situação */}
            <Tabs
                value={situacao}
                onChange={handleChange}
                variant="fullWidth"
                scrollButtons="auto"
                allowScrollButtonsMobile
                sx={{
                    borderBottom: 2,
                    borderColor: 'divider',
                    '& .MuiTab-root': {
                        textTransform: 'none',
                        fontSize: { xs: '0.8rem', sm: '0.9rem' },
                        fontWeight: 'semibold',
                    },
                }}
            >
                <Tab label="Disponíveis" value="disponivel" />
                <Tab label="Adotados" value="adotado" />
                <Tab label="Em Adoção" value="em_adocao" />
                <Tab label="Aprovação" value="em_aprovacao" />
            </Tabs>
            <List
                filters={filters}
                filter={{ situacao }}
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
