import { ArrayField, ChipField, DataTable, DateField, List, NumberField, NumberInput, SingleFieldList, TextInput } from 'react-admin';

const filters = [
    <NumberInput label="Valor" source="valor" size="small" alwaysOn />,
];
export const TransacaoList = () => (
    <List filters={filters}>
        <DataTable rowClick="edit">
            <DataTable.Col source="data_transacao">
                <DateField source="data_transacao" />
            </DataTable.Col>
            <DataTable.Col source="tipo_transacao" />
            <DataTable.Col source="valor">
                <NumberField source="valor" />
            </DataTable.Col>
            <DataTable.Col source="descricao" />
            <DataTable.Col source="categoria">
                <ArrayField source="categoria">
                    <SingleFieldList linkType={false}>
                        <ChipField source="categoria" size="small" />
                    </SingleFieldList>
                </ArrayField>
            </DataTable.Col>

        </DataTable>
    </List>
);