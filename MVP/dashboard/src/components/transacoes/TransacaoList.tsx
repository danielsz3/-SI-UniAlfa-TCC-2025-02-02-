import { ChipField, DataTable, DateField, FunctionField, List, NumberField, NumberInput } from 'react-admin';

const filters = [
    <NumberInput label="Valor" source="valor" size="small" alwaysOn />,
];
export const TransacaoList = () => (
    <List filters={filters}>
        <DataTable rowClick="edit">
            <DataTable.Col source="data_transacao" label="Data">
                <DateField source="data_transacao" showTime locales={'pt-BR'} />
            </DataTable.Col>
            <DataTable.Col source="tipo_transacao" label="Tipo">
                <FunctionField
                    render={(record) => {
                        const color = record.tipo_transacao === 'entrada' ? 'green' : 'red';
                        return <ChipField source="tipo_transacao" style={{ backgroundColor: color, color: 'white' }} />
                    }}
                />
            </DataTable.Col>
            <DataTable.Col source="valor">
                <NumberField
                    source="valor"
                    options={{ style: 'currency', currency: 'BRL' }}
                    sx={{ textAlign: 'right' }}
                />
            </DataTable.Col>
            <DataTable.Col source="descricao" />
            <DataTable.Col source="categoria" />
        </DataTable>
    </List>
);