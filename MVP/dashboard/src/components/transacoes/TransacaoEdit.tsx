import { ArrayInput, DateTimeInput, Edit, NumberInput, RadioButtonGroupInput, SimpleForm, TextArrayInput, TextInput, minValue, required } from 'react-admin';

const TransacaoEdit = () => (
    <Edit title="Editar Transação" sx={{ width: 600, margin: '0 auto' }}>
        <SimpleForm>
            <DateTimeInput
                source="data_transacao"
                label="Data da Transação"
                value={new Date()}
                validate={required('A data da transação é obrigatória')}
            />

            <RadioButtonGroupInput
                label="Tipo"
                source="tipo_transacao"
                choices={[
                    { id: 'entrada', name: 'Entrada' },
                    { id: 'saida', name: 'Saída' }
                ]}
                validate={required('O tipo é obrigatório')}
            />

            <NumberInput
                source="valor"
                label="Valor R$"
                validate={[
                    required('O valor é obrigatório'),
                    minValue('O valor deve ser maior que zero', 0)
                ]}
            />

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

export default TransacaoEdit;
