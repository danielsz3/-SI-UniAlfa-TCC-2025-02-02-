import { Edit, SimpleForm, TextInput, required } from 'react-admin';

const ParceiroEdit = () => (
    <Edit
        title="Editar Parceiro"
        sx={{ width: '100%', maxWidth: 600, margin: '0 auto' }}
        redirect="list"
    >
        <SimpleForm>
            <TextInput
                source="nome"
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
    </Edit>
);

export default ParceiroEdit;
