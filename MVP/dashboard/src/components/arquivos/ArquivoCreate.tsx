import { Create, FileField, FileInput, SimpleForm, TextInput, required } from 'react-admin';

const ArquivoCreate = () => (
    <Create title="Criar Novo Documento" sx={{ width: 600, margin: '0 auto' }}>
        <SimpleForm>
            <TextInput
                source="titulo"
                label="Título"
                validate={required('O título é obrigatório')}
            />

            <FileInput
                source="url_arquivo"
                label="Arquivo"
                accept={{ 'application/pdf': ['.pdf']}}
                maxSize={5000000}
                placeholder="Selecione o arquivo"
                validate={required('O arquivo é obrigatório')}
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
