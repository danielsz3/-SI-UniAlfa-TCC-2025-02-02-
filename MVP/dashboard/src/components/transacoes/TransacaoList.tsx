import { DataTable, DateField, List, NumberField, NumberInput } from 'react-admin';

const filters = [
    <NumberInput label="Valor" source="valor" size="small" alwaysOn />,
];
export const TransacaoList = () => (
    <List filters={filters}>
        <DataTable rowClick="edit">
            <DataTable.Col source="data_transacao" label="Data">
                <DateField source="data_transacao" showTime locales={'pt-BR'}/>
            </DataTable.Col>
            <DataTable.Col source="tipo_transacao" label="Tipo" />
            <DataTable.Col source="valor">
                <NumberField
                    source="valor"
                    options={{ style: 'currency', currency: 'BRL' }}
                />
            </DataTable.Col>
            <DataTable.Col source="descricao" />
            <DataTable.Col source="categoria" />
        </DataTable>
    </List>
);