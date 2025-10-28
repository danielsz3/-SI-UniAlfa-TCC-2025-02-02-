import { useState, useEffect } from 'react';
import {
    Box,
    TextField,
    Typography,
    Button,
    Paper,
    Grid,
    Alert,
    IconButton,
    Card,
    CardMedia,
    CircularProgress,
    Chip
} from '@mui/material';
import {
    Delete,
    Clear
} from '@mui/icons-material';
import { useLocation } from 'react-router-dom';
import { FilePlaceholder } from '../FilePlaceHolder';
import { DragDropContext, Droppable, Draggable, DropResult } from '@hello-pangea/dnd';
import { CreateBase, Title, useNotify, useCreate } from 'react-admin';

const FEED_MIN = 4 / 5; // 0.8
const FEED_MAX = 1.91;  // 1.91
const EPSILON = 0.01;

const isValidFeedAspect = (width: number, height: number) => {
    if (!width || !height) return false;
    const ratio = width / height;
    return ratio >= FEED_MIN - EPSILON && ratio <= FEED_MAX + EPSILON;
};

interface ImageData {
    id: string; // Adicionar um ID único para o DND
    file: File;
    src: string;
    width: number;
    height: number;
    isValid: boolean;
    title: string;
}

const PostCreate = () => {
    const [legenda, setLegenda] = useState('');
    const [create, ] = useCreate();
    const [imagens, setImagens] = useState<ImageData[]>([]);
    const [invalidHelper, setInvalidHelper] = useState<string | null>(null);
    const [isDragging, setIsDragging] = useState(false);
    const [loading, setLoading] = useState(false);
    const location = useLocation();
    const notify = useNotify();

    useEffect(() => {
        const defaults = location.state?.defaultValues || {};

        if (defaults.imagens?.length) {
            const validImages: ImageData[] = [];
            let removedCount = 0;

            // Processar as imagens padrão para garantir IDs e validade
            const processDefaultImages = async () => {
                for (const img of defaults.imagens) {
                    const { file, src, width, height, title } = img;
                    // Se for um objeto File real, precisamos revalidar e carregar
                    if (file instanceof File) {
                        try {
                            const imageData = await validateAndLoadImage(file);
                            if (imageData.isValid) {
                                validImages.push({ ...imageData, id: `img-${Date.now()}-${Math.random()}` });
                            } else {
                                removedCount++;
                            }
                        } catch (error) {
                            console.error("Erro ao carregar imagem padrão:", error);
                            removedCount++;
                        }
                    } else if (isValidFeedAspect(width, height)) {
                        validImages.push({
                            id: `img-${Date.now()}-${Math.random()}`,
                            file: file || new File([], title || 'image.png'),
                            src,
                            width,
                            height,
                            isValid: true,
                            title: title || 'Imagem Padrão'
                        });
                    } else {
                        removedCount++;
                    }
                }

                if (removedCount > 0) {
                    const message = `Algumas imagens foram removidas por não estarem no formato ideal de feed. Total: ${removedCount}`;
                    notify(message, { type: 'warning' });
                }
                setImagens(validImages);
                setLegenda(defaults.legenda || '');
            };

            processDefaultImages();
        }
    }, [location.state, notify]); // Dependência para garantir que roda quando o estado muda

    const validateAndLoadImage = async (file: File): Promise<ImageData> => {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    const isValid = isValidFeedAspect(img.width, img.height);
                    resolve({
                        id: `img-${Date.now()}-${Math.random()}`, // Adicionar ID aqui!
                        file,
                        src: e.target?.result as string,
                        width: img.width,
                        height: img.height,
                        isValid,
                        title: file.name
                    });
                };
                img.onerror = () => reject(new Error('Erro ao carregar imagem'));
                img.src = e.target?.result as string;
            };
            reader.onerror = () => reject(new Error('Erro ao ler arquivo'));
            reader.readAsDataURL(file);
        });
    }

    const handleFileSelect = async (files: FileList) => {
        setLoading(true);
        const fileArray = Array.from(files);
        const newValidImages: ImageData[] = [];
        let invalidCount = 0;

        for (const file of fileArray) {
            if (file.size > 10_500_000) {
                invalidCount++;
                continue;
            }
            if (!file.type.startsWith('image/')) {
                invalidCount++;
                continue;
            }

            try {
                const imageData = await validateAndLoadImage(file);
                if (imageData.isValid) {
                    newValidImages.push(imageData);
                } else {
                    invalidCount++;
                }
            } catch (err) {
                console.warn('Erro ao validar imagem:', err);
                invalidCount++;
            }
        }

        if (invalidCount > 0) {
            setInvalidHelper(`${invalidCount} imagem(ns) foram ignoradas — o feed aceita apenas proporções entre 4:5 (retrato) e 1.91:1 (paisagem).`);
        } else {
            setInvalidHelper(null);
        }

        setImagens((prevImagens) => [...prevImagens, ...newValidImages]);
        setLoading(false);
    };

    const handleDragOver = (e: React.DragEvent) => {
        e.preventDefault();
        setIsDragging(true);
    };

    const handleDragLeave = (e: React.DragEvent) => {
        e.preventDefault();
        setIsDragging(false);
    };

    const handleDrop = (e: React.DragEvent) => {
        e.preventDefault();
        setIsDragging(false);
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelect(files);
        }
    };

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const files = e.target.files;
        if (files && files.length > 0) {
            handleFileSelect(files);
        }
    };

    const removeImage = (id: string) => { // Agora remove pelo ID
        setImagens(imagens.filter((img) => img.id !== id));
    };

    // Função para reordenar o array
    const reorder = (list: ImageData[], startIndex: number, endIndex: number) => {
        const result = Array.from(list);
        const [removed] = result.splice(startIndex, 1);
        result.splice(endIndex, 0, removed);
        return result;
    };

    // Handler do Drag and Drop
    const onDragEnd = (result: DropResult) => {
        // dropped outside the list
        if (!result.destination) {
            return;
        }

        const reorderedItems = reorder(
            imagens,
            result.source.index,
            result.destination.index
        );

        setImagens(reorderedItems);
    };


    const handleSubmit = () => {
        if (imagens.length === 0) {
            notify('Pelo menos uma imagem é obrigatória', { type: 'warning' });
            return;
        }

        const postData = {
            legenda: legenda,
            
            imagens: imagens.map(img => ({
                src: img.src,     
                title: img.title, 
                rawFile: img.file
            }))
        };
        create(
            'posts', 
            { data: postData },
            {
                onSuccess: () => {
                    notify('Post criado com sucesso!', { type: 'success' });
                },
                onError: () => {
                    notify('Erro ao criar post', { type: 'error' });
                }
            }
        );
    };

    const handleClear = () => {
        setLegenda('');
        setImagens([]);
        setInvalidHelper(null);
    };

    return (
        <CreateBase
            resource="posts"
        >
            <Title title="Criar Post" />
            <Box sx={{ py: 4, px: 2 }}>
                <Paper
                    elevation={2}
                    sx={{
                        maxWidth: 600,
                        mx: 'auto',
                        p: 3,
                        mb: 10
                    }}
                >
                    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
                        {/* Campo de Legenda */}
                        <TextField
                            label="Legenda"
                            multiline
                            rows={6}
                            fullWidth
                            value={legenda}
                            onChange={(e) => setLegenda(e.target.value)}
                            placeholder="Digite a legenda do post..."
                            variant="outlined"
                        />

                        {/* Área de Upload */}
                        <Box>
                            <Typography variant="subtitle2" fontWeight="medium" gutterBottom>
                                Imagens do Post *
                            </Typography>

                            <Paper
                                elevation={0}
                                onDragOver={handleDragOver}
                                onDragLeave={handleDragLeave}
                                onDrop={handleDrop}
                                component="label"
                                htmlFor="file-input"
                                sx={{
                                    cursor: 'pointer',
                                    border: isDragging ? '2px dashed primary.main' : '2px dashed grey.400',
                                    bgcolor: isDragging ? 'primary.light' : 'transparent',
                                }}
                            >
                                <FilePlaceholder
                                    multiple
                                    accept={['image/png', 'image/jpeg', 'image/jpg']}
                                    maxSize={10_500_000}
                                />
                                <input
                                    type="file"
                                    id="file-input"
                                    multiple
                                    accept="image/png,image/jpeg,image/jpg,image/gif"
                                    onChange={handleInputChange}
                                    style={{ display: 'none' }}
                                />
                            </Paper>

                            {/* Helper Text / Avisos */}
                            <Box sx={{ mt: 1 }}>
                                {invalidHelper ? (
                                    <Alert severity="warning" sx={{ fontSize: '0.875rem' }}>
                                        {invalidHelper}
                                    </Alert>
                                ) : (
                                    <Typography variant="caption" color="text.secondary">
                                        Apenas imagens entre <strong>4:5 (retrato)</strong> e{' '}
                                        <strong>1.91:1 (paisagem)</strong> são aceitas para o feed.
                                    </Typography>
                                )}
                            </Box>

                            {/* Loading */}
                            {loading && (
                                <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', mt: 3 }}>
                                    <CircularProgress size={24} sx={{ mr: 1 }} />
                                    <Typography variant="body2" color="text.secondary">
                                        Processando imagens...
                                    </Typography>
                                </Box>
                            )}

                            {/* Grid de Imagens com Drag and Drop */}
                            {imagens.length > 0 && (
                                <DragDropContext onDragEnd={onDragEnd}>
                                    <Droppable droppableId="image-list" direction="horizontal">
                                        {(provided) => (
                                            <Grid
                                                container
                                                spacing={2}
                                                sx={{ mt: 1 }}
                                                {...provided.droppableProps}
                                                ref={provided.innerRef}
                                            >

                                                {imagens.map((img, index) => (
                                                    <Draggable key={img.id} draggableId={img.id} index={index}>
                                                        {(provided, snapshot) => (
                                                            <Grid size={{ xs: 6, sm: 4, md: 3 }}
                                                                ref={provided.innerRef}
                                                                {...provided.draggableProps}
                                                                {...provided.dragHandleProps}
                                                                sx={{
                                                                    opacity: snapshot.isDragging ? 0.8 : 1,
                                                                    transition: 'opacity 0.2s',
                                                                    zIndex: snapshot.isDragging ? 9999 : 'auto',
                                                                }}
                                                            >
                                                                <Card
                                                                    sx={{
                                                                        position: 'relative',
                                                                        border: snapshot.isDragging ? '2px solid primary.main' : '0px solid transparent',
                                                                        boxShadow: snapshot.isDragging ? 6 : 1,
                                                                        '&:hover .delete-button': {
                                                                            opacity: 1
                                                                        },
                                                                    }}
                                                                >
                                                                    <CardMedia
                                                                        component="img"
                                                                        height="180"
                                                                        image={img.src}
                                                                        alt={img.title}
                                                                        sx={{ objectFit: 'cover' }}
                                                                    />
                                                                    <IconButton
                                                                        className="delete-button"
                                                                        onClick={() => removeImage(img.id)}
                                                                        sx={{
                                                                            position: 'absolute',
                                                                            top: 5,
                                                                            right: 5,
                                                                            bgcolor: 'rgba(255, 0, 0, 0.7)',
                                                                            color: 'white',
                                                                            opacity: 0,
                                                                            transition: 'opacity 0.3s',
                                                                            '&:hover': {
                                                                                bgcolor: 'error.dark'
                                                                            }
                                                                        }}
                                                                        size="small"
                                                                    >
                                                                        <Delete fontSize="small" />
                                                                    </IconButton>
                                                                    <Chip
                                                                        label={index + 1 == 1 ? 'Capa' : `Imagem ${index + 1}`}
                                                                        color="primary"
                                                                        size="small"
                                                                        sx={{
                                                                            position: 'absolute',
                                                                            top: 5,
                                                                            left: 5,
                                                                            fontSize: '0.7rem',
                                                                            fontWeight: 'bold'
                                                                        }}
                                                                    />
                                                                </Card>
                                                            </Grid>
                                                        )}
                                                    </Draggable>
                                                ))}
                                                {provided.placeholder}
                                            </Grid>
                                        )}
                                    </Droppable>
                                </DragDropContext>
                            )}
                        </Box>

                        {imagens.length != 0 && (
                            <Typography variant="caption" fontWeight="medium" sx={{ textAlign: 'center' }}>
                                Arraste para mudar a ordem das imagens
                            </Typography>
                        )}

                        {/* Botões de Ação */}
                        <Box sx={{ display: 'flex', gap: 2 }}>
                            <Button
                                variant="contained"
                                fullWidth
                                onClick={handleSubmit}
                                disabled={loading || imagens.length === 0}
                                size="large"
                            >
                                Criar Post
                            </Button>
                            <Button
                                variant="outlined"
                                onClick={handleClear}
                                startIcon={<Clear />}
                                size="large"
                            >
                                Limpar
                            </Button>
                        </Box>
                    </Box>
                </Paper>
            </Box>
        </CreateBase>
    );
};

export default PostCreate;