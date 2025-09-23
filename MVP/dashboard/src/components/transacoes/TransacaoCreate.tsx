import { ArrayInput, Create, DateTimeInput, NumberInput, RadioButtonGroupInput, SimpleForm, TextArrayInput, TextInput, minValue, required } from 'react-admin';
import CustomDateTimePicker from '../datepicker/customDateTimePicker';

const TransacaoCreate = () => (
    <Create title="Criar Nova Transção" sx={{ width: '100%', maxWidth: 600, margin: '0 auto' }}>
        <SimpleForm>
            <CustomDateTimePicker
                source="data_transacao"
                label="Data da Transação"
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
    </Create>
);

export default TransacaoCreate;
