import { useLocation } from 'react-router-dom';
import { useState, useEffect } from 'react';
import {
    Create,
    SimpleForm,
    TextInput,
    ImageInput,
    ImageField,
    useNotify,
    required
} from 'react-admin';
import { Typography } from '@mui/material';
import { FilePlaceholder } from '../FilePlaceHolder';

const FEED_MIN = 4 / 5; // 0.8
const FEED_MAX = 1.91;  // 1.91
const EPSILON = 0.01;

const isValidFeedAspect = (width: number, height: number) => {
    if (!width || !height) return false;
    const ratio = width / height;
    return ratio >= FEED_MIN - EPSILON && ratio <= FEED_MAX + EPSILON;
};

const PostCreate = () => {
    const location = useLocation();
    const notify = useNotify();
    const [filteredDefaults, setFilteredDefaults] = useState({});
    const [invalidHelper, setInvalidHelper] = useState<string | null>(null);

    // 🔹 Filtra imagens de defaultValues com base no width/height fornecido
    useEffect(() => {
        const defaults = location.state?.defaultValues || {};
        if (defaults.imagens?.length) {
            const validImages = defaults.imagens.filter((img: any) => {
                const { width, height } = img;
                if (isValidFeedAspect(width, height)) return true;
                console.warn(`Imagem fora do padrão Feed: ${img.src}`);
                return false;
            });

            if (validImages.length < defaults.imagens.length) {
                notify(
                    'Algumas imagens foram removidas por não estarem no formato ideal de feed (4:5 a 1.91:1).',
                    { type: 'warning' }
                );
            }

            setFilteredDefaults({
                ...defaults,
                imagens: validImages
            });
        } else {
            setFilteredDefaults(defaults);
        }
    }, [location.state]);

    // 🔹 Validação para novas imagens
    const handleValidateImages = async (files: any[]) => {
        if (!Array.isArray(files)) return files;

        const validFiles: any[] = [];
        let invalidCount = 0;

        for (const file of files) {
            // Ignora se não for um File real
            if (!(file instanceof File)) {
                validFiles.push(file);
                continue;
            }

            try {
                const img = await new Promise<HTMLImageElement>((resolve, reject) => {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const image = new Image();
                        image.onload = () => resolve(image);
                        image.onerror = reject;
                        image.src = e.target?.result as string;
                    };
                    reader.onerror = reject;
                    reader.readAsDataURL(file);
                });

                if (isValidFeedAspect(img.width, img.height)) {
                    validFiles.push(file);
                } else {
                    invalidCount++;
                }
            } catch (err) {
                console.warn('Erro ao validar imagem:', err);
            }
        }

        if (invalidCount > 0) {
            setInvalidHelper(`⚠️ ${invalidCount} imagem(ns) foram ignoradas — o feed aceita apenas proporções entre 4:5 (retrato) e 1.91:1 (paisagem).`);
        } else {
            setInvalidHelper(null);
        }

        return validFiles;
    };

    return (
        <Create
            title="Criar Novo Post"
            resource='posts'
            sx={{ width: '100%', maxWidth: 600, margin: '0 auto', mb: 10 }}
        >
            <SimpleForm defaultValues={filteredDefaults}>
                <TextInput source="legenda" label="Legenda" multiline fullWidth />

                <ImageInput
                    source="imagens"
                    label="Imagens do Evento"
                    accept={{ 'image/*': ['.png', '.jpg', '.jpeg', '.gif'] }}
                    maxSize={10_500_000}
                    validate={required('Pelo menos uma imagem é obrigatória')}
                    multiple
                    parse={async (files) => await handleValidateImages(files)}
                    helperText={
                        invalidHelper || (
                            <Typography variant="caption" color="text.secondary" sx={{mb: 2}}>
                                Apenas imagens entre <strong>4:5 (retrato)</strong> e <strong>1.91:1 (paisagem)</strong> são aceitas para o feed.
                            </Typography>
                        )
                    }
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
    );
};

export default PostCreate;
