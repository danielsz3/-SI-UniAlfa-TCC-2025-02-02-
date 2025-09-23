import { Edit, FileField, FileInput, SimpleForm, TextInput, required } from 'react-admin';
import { FilePlaceholder } from '../FilePlaceHolder';

const ArquivoEdit = () => (
    <Edit
        title="Editar Documento"
        redirect="list"
        sx={{ minWidth: '100%', maxWidth: 600, margin: '0 auto' }}
    >
        <SimpleForm>
            <TextInput
                source="title"
                label="Título"
                validate={required('O título é obrigatório')}
            />

            <FileInput
                source="arquivo"
                label="Arquivo"
                helperText="Deixe vazio para manter o arquivo atual"
                accept={{ 'application/pdf': ['.pdf'] }}
                maxSize={5000000}
                placeholder={
                    <FilePlaceholder
                        maxSize={5_200_000}
                        accept={[".pdf"]}
                    />
                }
                sx={{
                    '& .RaFileInput-dropZone': {
                        backgroundColor: "#fff",
                        p: 0,
                    },
                }}
            >
                <FileField source="src" title="title" />
            </FileInput>

            <TextInput
                source="descricao"
                label="Descrição"
                validate={required('A descrição é obrigatória')}
            />

            <TextInput
                source="categoria"
                label="Categoria"
                validate={required('Pelo menos uma categoria é obrigatória')}
            />

            {/* <TextArrayInput
                source="categoria"
                label="Categorias"
                validate={required('Pelo menos uma categoria é obrigatória')}
            /> */}

        </SimpleForm>
    </Edit>
);

export default ArquivoEdit;
