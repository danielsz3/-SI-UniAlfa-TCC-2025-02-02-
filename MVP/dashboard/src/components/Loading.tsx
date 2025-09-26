
import { Box } from '@mui/material';
import { Bouncy } from 'ldrs/react';

export const Loading = () => (
    <Box
        sx={{
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center',
            width: '100vw',
            height: '100vh',
            backgroundColor: 'rgba(255, 255, 255, 0.2)',
            backdropFilter: 'blur(8px)',
            zIndex: 9999
        }}
    >
        <Bouncy
            size={45}
            speed={1.75}
            color="#337ab7"
        />
    </Box>
);