import { useInput, type InputProps } from 'react-admin';
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import { AdapterDateFns } from '@mui/x-date-pickers/AdapterDateFns';
import { ptBR } from 'date-fns/locale/pt-BR';
import { DateTimePicker } from '@mui/x-date-pickers';

type CustomDateTimePicker = InputProps & {
    label: string;
};

const CustomDateTimePicker: React.FC<CustomDateTimePicker> = ({ source, label, ...props }) => {
    const { field, fieldState } = useInput({ source, ...props });

    const cleanMessage = (msg?: string) =>
        msg ? msg.replace(/^@@react-admin@@/, '').replace(/"/g, '') : undefined;

    return (
        <LocalizationProvider dateAdapter={AdapterDateFns} adapterLocale={ptBR}>
            <DateTimePicker
                disableFuture
                label={label}
                value={field.value ? new Date(field.value) : null}
                onChange={field.onChange}
                sx={{ mb: 0}}
                slotProps={{
                    textField: {
                        fullWidth: true,
                        error: !!fieldState.error,
                        helperText: cleanMessage(fieldState.error?.message) || " ",
                        size: 'small',
                    },
                    

                }}
                
            />
        </LocalizationProvider>
    );
};

export default CustomDateTimePicker;