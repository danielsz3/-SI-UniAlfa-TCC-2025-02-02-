import { DataTable, FileField, FunctionField, List, TextInput } from 'react-admin';

const filters = [
    <TextInput label="Título" source="titulo" size="small" alwaysOn />,
];
// Função para converter bytes em formato legível
const formatFileSize = (bytes: number): string => {
  if (!bytes || bytes === 0) return '0 B';
  
  const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
  const i = Math.floor(Math.log(bytes) / Math.log(1024));
  const size = (bytes / Math.pow(1024, i)).toFixed(1);
  
  return `${size} ${sizes[i]}`;
};

export const ArquivoList = () => (
    <List filters={filters}>
        <DataTable rowClick="edit">
            <DataTable.Col source="titulo" label="Título" />
            <DataTable.Col source="arquivo">
                <FileField source="arquivo" title="tipo"/>
            </DataTable.Col>
            <DataTable.Col source="tamanho">
                <FunctionField
                    render={(record) => formatFileSize(record.tamanho)}
                />
            </DataTable.Col>
            <DataTable.Col source="descricao" />
            <DataTable.Col source="categoria" />
        </DataTable>
    </List>
);