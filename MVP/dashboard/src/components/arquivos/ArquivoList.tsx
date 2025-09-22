import { DataTable, FileField, List, TextInput } from 'react-admin';

const filters = [
    <TextInput label="Título" source="titulo" size="small" alwaysOn />,
];
export const ArquivoList = () => (
    <List filters={filters}>
        <DataTable rowClick="edit">
            <DataTable.Col source="titulo" label="Título" />
            <DataTable.Col source="valor">
                <FileField source="url_arquivo" />
            </DataTable.Col>
            <DataTable.Col source="descricao" />
            <DataTable.Col source="categoria" />
        </DataTable>
    </List>
);