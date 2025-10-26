import {
    Edit,
    TabbedForm,
    FormTab,
    TextInput,
    required,
    EditProps,
    RadioButtonGroupInput,
    useNotify,
    ImageInput,
    ImageField,
    SimpleFormIterator,
    AutocompleteInput,
    ArrayInput,
} from 'react-admin';
import CustomDatePicker from '../datepicker/customDatePicker';
import { useFormContext } from 'react-hook-form';
import { useEffect, useState } from 'react';
import { FilePlaceholder } from '../FilePlaceHolder';

const CepInput = () => {
    const { setValue, watch } = useFormContext();
    const cep = watch("cep"); // observa o campo de CEP
    const notify = useNotify();
    const [helpText, setHelpText] = useState("Digite o CEP para preencher automaticamente o endereço");

    useEffect(() => {
        const fetchAddress = async () => {
            if (cep && /^\d{8}$/.test(cep)) { // ViaCEP espera 8 dígitos
                setHelpText("Buscando endereço...");
                try {
                    const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                    const data = await response.json();
                    if (data.erro) {
                        setHelpText("CEP não encontrado");
                        notify("CEP não encontrado", { type: 'warning' });
                        return;
                    }
                    // Atualiza os campos de endereço automaticamente
                    setValue("endereco.logradouro", data.logradouro || "");
                    setValue("endereco.bairro", data.bairro || "");
                    setValue("endereco.cidade", data.localidade || "");
                    setValue("endereco.uf", data.uf || "");
                    setHelpText("Endereço preenchido automaticamente");
                } catch (error) {
                    console.error("Erro ao buscar o CEP:", error);
                    notify("Erro ao buscar o CEP", { type: 'error' });
                }
            }
        };
        fetchAddress();
    }, [cep, setValue, notify]);

    return (
        <TextInput
            source="cep"
            label="CEP"
            validate={required()}
            helperText={helpText}
        />
    );
};

const OngEdit = (props: EditProps) => {

    return (
        <Edit
            {...props}
            title="Editar ONG"
            resource='ongs'
            sx={{ width: '100%', maxWidth: 600, margin: '0 auto' }}
        >
            <TabbedForm>
                <FormTab label="Informações">
                    <TextInput
                        source="nome"
                        label="Nome da ONG"
                        validate={required('O nome é obrigatório')}
                    />
                    <TextInput
                        source="cnpj"
                        label="CNPJ"
                        validate={required('O CNPJ é obrigatório')}
                    />
                    <TextInput
                        source="descricao"
                        label="Descrição"
                        validate={required("A descrição é obrigátoria")}
                        multiline
                        rows={3}
                    />
                </FormTab>

                <FormTab label="Endereço">
                    <CepInput />
                    <TextInput
                        source="logradouro"
                        label="Logradouro"
                        validate={required()}
                    />
                    <TextInput
                        source="numero"
                        label="Número"
                        validate={required()}
                    />
                    <TextInput source="complemento" label="Complemento" />
                    <TextInput
                        source="bairro"
                        label="Bairro"
                    />
                    <TextInput
                        source="cidade"
                        label="Cidade"
                        validate={required()}
                    />
                    <TextInput source="estado" label="UF" validate={required()} />
                </FormTab>

                <FormTab label="Contatos">
                    <ArrayInput source="contatos_ongs" label="Todos os Contatos">
                        <SimpleFormIterator
                            inline
                            disableReordering
                            disableClear
                            getItemLabel={index => `#${index + 1}`}
                            sx={{ marginTop: 3 }}

                        >
                            <TextInput source="tipo" label="Tipo de Contato" validate={required()} />
                            <TextInput source="contato" label="Contato" validate={required()} />
                            <TextInput source="descricao" label="Contato" validate={required()} />
                        </SimpleFormIterator>
                    </ArrayInput>
                </FormTab>

                <FormTab label="Galeria">
                    <ImageInput
                        source="imagens"
                        label="Imagens da ONG"
                        multiple
                        accept={{ 'image/*': ['.png', '.jpg', '.jpeg', '.gif'] }}
                        maxSize={10_500_000}
                        validate={required('Pelo menos uma imagem é obrigatória')}
                        placeholder={
                            <FilePlaceholder
                                maxSize={10_500_000}
                                accept={['.png', '.jpg', '.jpeg', '.gif']}
                                multiple
                            />
                        }
                        sx={{
                            '& .RaFileInput-dropZone': {
                                p: 0,
                            },
                        }}
                    >
                        <ImageField source="src" title="title" />
                    </ImageInput>
                </FormTab>

                <FormTab label="Dados bancários">
                    <TextInput
                        source="banco"
                        label="Nome do Banco"
                        validate={required('O nome do banco é obrigatório')}
                    />
                    <TextInput
                        source="agencia"
                        label="Agência"
                        validate={required('A agência é obrigatória')}
                    />
                    <TextInput
                        source="numero_conta"
                        label="Número da conta"
                        validate={required('A conta é obrigatória')}
                    />
                    <TextInput
                        source="conta"
                        label="Tipo de Conta"
                        validate={required('O tipo de conta é obrigatório')}
                    />
                    <TextInput
                        source="pix"
                        label="Chave PIX"
                        validate={required('A chave pix é obrigatória')}
                    />
                </FormTab>
            </TabbedForm>
        </Edit>
    );
};

export default OngEdit;
