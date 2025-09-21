import {SimpleForm, TextInput, required, PasswordInput, Edit } from 'react-admin';
import CustomDatePicker from '../datepicker/customDatePicker';

const UserEdit = () => (
    <Edit title="Editar Usuário" sx={{ width: 600, margin: '0 auto' }}>
        <SimpleForm>
            <TextInput
                source="nome"
                label="Nome"
                validate={required('O nome é obrigatório')}
            />

            <TextInput
                source="cpf"
                label="CPF"
                validate={required('O CPF é obrigatório')}
            />

            <CustomDatePicker
                source='data_nascimento'
                label="Data de Nascimento *"
                validate={required('A data de nascimento é obrigatória')}
            />

            <TextInput
                source="telefone"
                label="Telefone"
                validate={[required('O telefone é obrigatória')
                ]}
            />

            <TextInput
                source="email"
                label="Email"
                validate={[required('O email é obrigatório'),
                (value) => value && !/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i.test(value) && 'O email é inválido'
                ]}
            />

            <PasswordInput
                source="password"
                label="Senha"
                validate={required('A senha é obrigatória')}
            />

            <PasswordInput
                source="password_confirmation"
                label="Confirmar Senha"
                validate={required('A confirmação de senha é obrigatória')}
            />

        </SimpleForm>
    </Edit>
);

export default UserEdit;
