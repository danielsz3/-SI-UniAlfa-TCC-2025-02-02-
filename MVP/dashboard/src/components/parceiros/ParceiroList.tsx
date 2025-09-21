import { DataTable, List, TextInput } from 'react-admin';

const filters = [
    <TextInput label="Nome" source="nome" size="small" alwaysOn />,
];
export const ParceiroList = () => (
    <List filters={filters}>
        <DataTable rowClick="edit">
            <DataTable.Col source="id" />
            <DataTable.Col source="nome" />
            <DataTable.Col source="url_site" />
            <DataTable.Col source="url_logo" />
            <DataTable.Col source="descricao" />
        </DataTable>
    </List>
);