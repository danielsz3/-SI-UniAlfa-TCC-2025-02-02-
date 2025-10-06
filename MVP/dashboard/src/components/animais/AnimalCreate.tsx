import { BooleanInput, Create, FormTab, ImageField, ImageInput, RadioButtonGroupInput, required, SelectInput, TabbedForm, TextInput, useRecordContext } from "react-admin";
import { FilePlaceholder } from "../FilePlaceHolder";

const AnimalCreate = () => (
    <Create
        title="Cadastrar Animal"
        sx={{ width: '100%', maxWidth: 600, margin: '0 auto', mb: 10 }}
        redirect="list"
    >
        <TabbedForm>
            <FormTab label="Informações">
                <TextInput
                    source="nome"
                    label="Nome"
                    validate={required('O nome é obrigatório')}
                />
                <RadioButtonGroupInput
                    label="Sexo"
                    source="sexo"
                    choices={[
                        { id: 'macho', name: 'Macho' },
                        { id: 'femea', name: 'Femêa' }
                    ]}
                    defaultValue={'ativo'}
                    validate={required('A situação é obrigatório')}
                />
                <TextInput
                    source="idade"
                    label="Idade"
                    validate={required('A idade é obrigatória')}
                />
                <BooleanInput
                    label="O Animal é castrado?"
                    source="castrado"
                />
                <BooleanInput
                    label="Tem Vale castração?"
                    source="vale_castracao"
                />
                <TextInput
                    source="descricao"
                    label="Descrição"
                    multiline
                    rows={3}
                    validate={required('A descrição é obrigatória')}
                />
            </FormTab>

            <FormTab label="Galeria">
                <ImageInput
                    source="imagens"
                    label="Imagens do Animal"
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

            <FormTab label="Perfil">
                <RadioButtonGroupInput
                    source="nivel_energia"
                    label="Nível de Energia"
                    choices={[
                        { id: 'baixa', name: 'Calmo / Tranquilo' },
                        { id: 'moderada', name: 'Ativo / Brincalhão' },
                        { id: 'alta', name: 'Muito Energético' },
                    ]}
                    validate={required('O nível é obrigatório')}
                />

                <RadioButtonGroupInput
                    source="tamanho"
                    label="Tamanho/Porte"
                    choices={[
                        { id: 'pequeno', name: 'Pequeno (até 10kg)' },
                        { id: 'medio', name: 'Médio (10kg a 25kg)' },
                        { id: 'grande', name: 'Grande (acima de 25kg)' },
                    ]}
                    validate={required('O tamanho é obrigatório')}
                />

                <SelectInput
                    source="tempo_necessario"
                    label="Necessidade de tempo e cuidado do animal"
                    choices={[
                        { id: 'pouco_tempo', name: 'Pouco tempo (independente, se adapta bem sozinho)' },
                        { id: 'tempo_moderado', name: 'Tempo moderado (gosta de companhia e passeios diários)' },
                        { id: 'muito_tempo', name: 'Muito tempo (precisa de atenção constante e interação frequente)' },
                    ]}
                    validate={required('O tempo é obrigatório')}
                />

                <RadioButtonGroupInput
                    source="ambiente_ideal"
                    label="Ambiente Ideal"
                    choices={[
                        { id: 'area_pequena', name: 'Área pequena (ambiente interno, como apartamento)' },
                        { id: 'area_media', name: 'Área média (casa com quintal pequeno ou espaço limitado)' },
                        { id: 'area_externa', name: 'Área externa ampla (quintal grande, sítio ou espaço aberto)' },
                    ]}
                    validate={required('O ambiente é obrigatório')}
                />

            </FormTab>
        </TabbedForm>
    </Create>
)

export default AnimalCreate;