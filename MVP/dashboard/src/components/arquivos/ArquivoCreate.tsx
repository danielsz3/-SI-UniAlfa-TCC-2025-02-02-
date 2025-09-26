import { Create, FileField, FileInput, SimpleForm, TextInput, required } from 'react-admin';
import { FilePlaceholder } from '../FilePlaceHolder';

const ArquivoCreate = () => (
    <Create
        title="Criar Novo Documento"
        sx={{ width: '100%', maxWidth: 600, margin: '0 auto' }}
        redirect="list"
    >
        <SimpleForm>
            <TextInput
                source="titulo"
                label="Título"
                validate={required('O título é obrigatório')}
            />

            <FileInput
                source="arquivo"
                label="Arquivo"
                accept={{ 'application/pdf': ['.pdf'] }}
                maxSize={5000000}
                placeholder={
                    <FilePlaceholder
                        maxSize={5_200_000}
                        accept={[".pdf"]}
                    />
                }
                validate={required('O arquivo é obrigatório')}
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
    </Create>
);

export default ArquivoCreate;
