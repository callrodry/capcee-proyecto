import React from 'react';
import { Box, Typography, Paper, Button } from '@mui/material';
import { Assessment } from '@mui/icons-material';

function Reportes() {
  return (
    <Box>
      <Typography variant="h4" gutterBottom fontWeight={600}>
        Reportes
      </Typography>
      <Paper sx={{ p: 4, textAlign: 'center' }}>
        <Assessment sx={{ fontSize: 64, color: 'primary.main', mb: 2 }} />
        <Typography variant="h6" gutterBottom>
          Módulo de Reportes
        </Typography>
        <Typography variant="body1" color="text.secondary" gutterBottom>
          Esta sección está en desarrollo
        </Typography>
        <Button variant="contained" sx={{ mt: 2 }}>
          Generar Reporte de Prueba
        </Button>
      </Paper>
    </Box>
  );
}

export default Reportes;