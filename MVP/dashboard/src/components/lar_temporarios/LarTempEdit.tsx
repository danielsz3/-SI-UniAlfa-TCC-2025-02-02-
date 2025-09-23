import {
    Edit,
    TabbedForm,
    FormTab,
    TextInput,
    required,
    // useDataProvider,
    // useRedirect,
    EditProps,
    // RaRecord,
    // UseEditMutateParams,
    // useNotify,
    RadioButtonGroupInput,
} from 'react-admin';
import CustomDatePicker from '../datepicker/customDatePicker';

// Tipos dos dados
// interface LarTemporario extends RaRecord {
//     nome: string;
//     telefone: string;
//     data_nascimento: string;
//     situacao: string;
// }

// interface Endereco extends RaRecord {
//     lar_temporario_id: number;
//     cep: string;
//     logradouro: string;
//     numero: string;
//     complemento?: string;
//     bairro: string;
//     cidade: string;
//     uf: string;
// }

const LarTempEdit = (props: EditProps) => {
    // const dataProvider = useDataProvider();
    // const redirect = useRedirect();
    // const notify = useNotify();
    // const id_usuario = localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user') as string).id : '';

    // const onSuccess = async (
    //     data: LarTemporario,
    //     variables: UseEditMutateParams<LarTemporario & Endereco>
    // ) => {
    //     try {
    //         const values = variables.data as Endereco;

    //         await dataProvider.Edit<Endereco>('enderecos', {
    //             data: {
    //                 id_usuario: id_usuario,
    //                 lar_temporario_id: data.id,
    //                 cep: values.cep,
    //                 logradouro: values.logradouro,
    //                 numero: values.numero,
    //                 complemento: values.complemento,
    //                 bairro: values.bairro,
    //                 cidade: values.cidade,
    //                 uf: values.uf,
    //             },
    //         });

    //         redirect('/lar-temporario');
    //     } catch (error) {
    //         notify('Erro ao criar endereço:' + error);
    //     }
    // };

    return (
        <Edit
            {...props}
            title="Editar Lar Temporário"
            sx={{ width: 600, margin: '0 auto' }}
        >
            <TabbedForm>
                <FormTab label="Responsável">
                    <RadioButtonGroupInput
                        label="Situação"
                        source="situacao"
                        choices={[
                            { id: 'ativo', name: 'Ativo' },
                            { id: 'inativo', name: 'Inativo' }
                        ]}
                        validate={required('A situação é obrigatório')}
                    />

                    <TextInput
                        source="nome"
                        label="Nome Completo"
                        validate={required('O nome é obrigatório')}
                    />
                    <TextInput
                        source="telefone"
                        label="Telefone"
                        validate={required('O telefone é obrigatório')}
                    />
                    <CustomDatePicker
                        source="data_nascimento"
                        label="Data de Nascimento"
                        validate={required('A data de nascimento é obrigatória')}
                    />
                </FormTab>

                <FormTab label="Endereço" disabled>
                    <TextInput source="cep" label="CEP"/>
                    <TextInput
                        source="logradouro"
                        label="Logradouro"
                    />
                    <TextInput
                        source="numero"
                        label="Número"
                    />
                    <TextInput source="complemento" label="Complemento" />
                    <TextInput
                        source="bairro"
                        label="Bairro"
                    />
                    <TextInput
                        source="cidade"
                        label="Cidade"
                    />
                    <TextInput source="uf" label="UF"/>
                </FormTab>
            </TabbedForm>
        </Edit>
    );
};

export default LarTempEdit;
