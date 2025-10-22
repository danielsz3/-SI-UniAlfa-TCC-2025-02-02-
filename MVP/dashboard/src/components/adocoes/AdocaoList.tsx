import { DataTable, List, SelectInput } from 'react-admin';

const filters = [
    <SelectInput
        label="Situação"
        source="status"
        size="small"
        choices={[
            { id: 'em_aprovacao', name: 'Em Aberto' },
            { id: 'aprovado', name: 'Aprovado' }
        ]}
        alwaysOn
    />
];
export const AdocaoList = () => (
    <List filters={filters}>
        <DataTable rowClick="edit">
            <DataTable.Col source="id" />
            <DataTable.Col source="usuario.nome" disableSort>
            </DataTable.Col>
            <DataTable.Col source="animal.nome" disableSort>
            </DataTable.Col>
            <DataTable.Col source="status" />
        </DataTable>
    </List>
);