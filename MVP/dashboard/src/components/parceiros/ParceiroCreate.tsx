import { Create, SimpleForm, TextInput, required } from 'react-admin';

const ParceiroCreate = () => (
    <Create title="Criar Novo Parceiro" sx={{ width: 600, margin: '0 auto' }}>
        <SimpleForm>
            <TextInput
                source="nome_parceiro"
                label="Nome"
                validate={required('O nome é obrigatório')}
            />

            <TextInput
                source="url_site"
                label="Site da Empresa"
                validate={required('O site é obrigatório')}
            />

            <TextInput
                source="url_logo"
                label="Logo da Empresa"
                validate={required('O logo é obrigatório')}
            />

            <TextInput
                source="descricao"
                label="Descrição"
                validate={[required('A descrição é obrigatória')]}
            />

        </SimpleForm>
    </Create>
);

export default ParceiroCreate;
