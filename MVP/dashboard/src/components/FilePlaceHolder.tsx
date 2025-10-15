import { FC } from "react";
import CloudUploadIcon from "@mui/icons-material/CloudUpload";
import { Box, Typography } from "@mui/material";

type FilePlaceholderProps = {
    maxSize?: number; // tamanho máximo em bytes
    accept?: string[]; // tipos aceitos
    multiple?: boolean; // se pode selecionar múltiplos arquivos
};

const formatBytes = (bytes: number) => {
    if (!bytes) return "Ilimitado";
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return `${(bytes / Math.pow(1024, i)).toFixed(1)} ${sizes[i]}`;
};

export const FilePlaceholder: FC<FilePlaceholderProps> = ({
    maxSize,
    accept,
    multiple,
}) => {
    return (
        <Box
            sx={{
                display: "flex",
                flexDirection: "column",
                alignItems: "center",
                justifyContent: "center",
                border: "2px dashed #90caf9",
                borderRadius: 2,
                p: 2,
                backgroundColor: "#fff",
                color: "#1976d2",
                cursor: "pointer",
                textAlign: "center",
                "&:hover": {
                    backgroundColor: "#e3f2fd",
                },
            }}
        >
            <CloudUploadIcon sx={{ fontSize: 40 }} />
            <Typography variant="body1" fontWeight="bold">
                Arraste e solte aqui ou clique para enviar
            </Typography>
            <Typography variant="body2" color="text.secondary">
                {multiple ? "Múltiplos arquivos" : "Apenas um arquivo"}
            </Typography>
            {maxSize && (
                <Typography variant="caption" color="text.secondary">
                    Tamanho máximo: {formatBytes(maxSize)}
                </Typography>
            )}
            {accept && accept.length > 0 && (
                <Typography variant="caption" color="text.secondary">
                    Tipos aceitos: {accept.join(", ")}
                </Typography>
            )}
        </Box>
    );
};
