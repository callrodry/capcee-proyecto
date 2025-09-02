import React from 'react';
import { Box, Typography, Paper } from '@mui/material';
import { Business } from '@mui/icons-material';

function Departamentos() {
  return (
    <Box>
      <Typography variant="h4" gutterBottom fontWeight={600}>
        Departamentos
      </Typography>
      <Paper sx={{ p: 4, textAlign: 'center' }}>
        <Business sx={{ fontSize: 64, color: 'primary.main', mb: 2 }} />
        <Typography variant="h6" gutterBottom>
          Gestión de Departamentos
        </Typography>
        <Typography variant="body1" color="text.secondary">
          Esta sección está en desarrollo
        </Typography>
      </Paper>
    </Box>
  );
}

export default Departamentos;