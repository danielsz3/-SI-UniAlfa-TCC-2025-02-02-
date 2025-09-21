import { DataTable, DateField, List, SelectInput, TextInput } from 'react-admin';

const filters = [
    <TextInput label="Nome" source="nome" size="small" alwaysOn />,
];
export const ParceiroList = () => (
    <List filters={filters}>
        <DataTable>
            <DataTable.Col source="id_parceiro" />
            <DataTable.Col source="nome_parceiro" />
            <DataTable.Col source="url_site" />
            <DataTable.Col source="data_nascimento">
                <DateField source="data_nascimento"/>
            </DataTable.Col>
            <DataTable.Col source="telefone"/>
            <DataTable.Col source="role" />
        </DataTable>
    </List>
);