import { ChipField, DataTable, FunctionField, List, TextInput } from 'react-admin';

const filters = [
    <TextInput label="Nome" source="nome" size="small" alwaysOn />,
];
export const LarTempList = () => (
    <List filters={filters}>
        <DataTable rowClick="edit">
            <DataTable.Col source="id" />
            <DataTable.Col source="nome" />
            <DataTable.Col source="telefone" />
            <DataTable.Col source="idade" />
            <DataTable.Col source="situacao" label="Situação" >
                <FunctionField
                    render={(record) => {
                        const color = record.situacao === 'ativo' ? 'forestgreen' : 'red';
                        return <ChipField source="situacao" style={{ backgroundColor: color, color: 'white' }} />
                    }}
                />
            </DataTable.Col>

        </DataTable>
    </List>
);