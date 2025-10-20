import { SetStateAction, useState } from 'react';
import {
    Create,
    ImageField,
    ImageInput,
    SimpleForm,
    TextInput,
    required,
    useNotify,
} from 'react-admin';
import { FilePlaceholder } from '../FilePlaceHolder';
import CustomDatePicker from '../datepicker/customDatePicker';
import { Dialog, DialogTitle, DialogActions, Button } from '@mui/material';
import { useNavigate } from 'react-router-dom';

interface Evento {
    id: number;
    titulo: string;
    descricao: string;
    data_inicio: string;
    data_fim: string;
    local: string;
    imagem: any;
    imagens: any[];
}

const EventoCreate = () => {
    const [showDialog, setShowDialog] = useState(false);
    const [eventoCriado, setEventoCriado] = useState<Evento | null>(null);
    const navigate = useNavigate();
    const notify = useNotify();

    const handleSuccess = (data: Evento) => {
        setEventoCriado(data);
        setShowDialog(true);
        notify('Evento criado com sucesso!');
    };

    const handleConfirmPost = () => {

        if (!eventoCriado) return; // segurança extra

        const imagens = [
            // adiciona a capa primeiro, se existir
            ...(eventoCriado.imagem ? [{
                ...eventoCriado.imagem,
                src: import.meta.env.VITE_API_URL + '/imagens/' + eventoCriado.imagem || eventoCriado.imagem
            }] : []),
            // depois adiciona as demais imagens
            ...(eventoCriado.imagens || []).map(img => ({
                ...img,
                src: import.meta.env.VITE_API_URL + '/imagens/' + img.caminho
            }))
        ];

        setShowDialog(false);
        navigate('/posts/create', {
            state: {
                defaultValues: {
                    legenda: `Participe do evento "${eventoCriado.titulo}"!\n📅 ${eventoCriado.data_inicio} - ${eventoCriado.data_fim}\n📍 ${eventoCriado.local}\n\n${eventoCriado.descricao}`,
                    imagens: imagens,
                },
            },
        });
    };

    const handleCancel = () => {
        setShowDialog(false);
        navigate('/eventos');
    };

    return (
        <>
            <Create
                title="Criar Novo Evento"
                sx={{ width: '100%', maxWidth: 600, margin: '0 auto', mb: 10 }}
                mutationOptions={{ onSuccess: handleSuccess }}
            >
                <SimpleForm>
                    <TextInput
                        source="titulo"
                        label="Título"
                        validate={required('O título é obrigatório')}
                        fullWidth
                    />

                    <CustomDatePicker
                        source="data_inicio"
                        label="Data de Início"
                        future
                        validate={required('A data inicial é obrigatória')}
                    />

                    <CustomDatePicker
                        source="data_fim"
                        label="Data de Encerramento"
                        future
                        validate={required('A data final é obrigatória')}
                    />

                    <TextInput
                        source="local"
                        label="Local"
                        validate={required('O local é obrigatório')}
                        fullWidth
                    />

                    <TextInput
                        source="descricao"
                        label="Descrição"
                        multiline
                        rows={3}
                        validate={required('A descrição é obrigatória')}
                        fullWidth
                    />

                    <ImageInput
                        source="imagem"
                        label="Imagem de Capa"
                        accept={{ 'image/*': ['.png', '.jpg', '.jpeg', '.gif'] }}
                        maxSize={10_500_000}
                        validate={required('A imagem de capa é obrigatória')}
                        placeholder={
                            <FilePlaceholder
                                maxSize={10_500_000}
                                accept={['.png', '.jpg', '.jpeg', '.gif']}
                            />
                        }
                        sx={{
                            '& .RaFileInput-dropZone': { p: 0 },
                        }}
                    >
                        <ImageField source="src" title="title" />
                    </ImageInput>

                    <ImageInput
                        source="imagens"
                        label="Imagens do Evento"
                        accept={{ 'image/*': ['.png', '.jpg', '.jpeg', '.gif'] }}
                        maxSize={10_500_000}
                        validate={required('Pelo menos uma imagem é obrigatória')}
                        multiple
                        placeholder={
                            <FilePlaceholder
                                maxSize={10_500_000}
                                accept={['.png', '.jpg', '.jpeg', '.gif']}
                                multiple
                            />
                        }
                        sx={{
                            '& .RaFileInput-dropZone': { p: 0 },
                        }}
                    >
                        <ImageField source="src" title="title" />
                    </ImageInput>
                </SimpleForm>
            </Create>

            {/* Dialog de confirmação */}
            <Dialog open={showDialog} onClose={handleCancel}>
                <DialogTitle>
                    Deseja criar um post no Instagram sobre este evento?
                </DialogTitle>
                <DialogActions>
                    <Button onClick={handleCancel} color="secondary">
                        Não
                    </Button>
                    <Button onClick={handleConfirmPost} color="primary" autoFocus>
                        Sim
                    </Button>
                </DialogActions>
            </Dialog>
        </>
    );
};

export default EventoCreate;
