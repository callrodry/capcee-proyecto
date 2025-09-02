import React, { useState, useEffect } from 'react';
import {
  Box,
  Paper,
  Typography,
  Button,
  Chip,
  IconButton,
  TextField,
  InputAdornment,
  Menu,
  MenuItem,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  LinearProgress,
  Tooltip,
  FormControl,
  InputLabel,
  Select,
  Grid,
  Alert
} from '@mui/material';
import { DataGrid } from '@mui/x-data-grid';
import { DatePicker } from '@mui/x-date-pickers/DatePicker';
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs';
import {
  Search,
  FilterList,
  MoreVert,
  Visibility,
  Refresh as RefreshIcon,
  Delete,
  GetApp,
  CheckCircle,
  Error,
  HourglassEmpty,
  Warning
} from '@mui/icons-material';
import axios from '../api/axios';
import toast from 'react-hot-toast';
import { format } from 'date-fns';
import dayjs from 'dayjs';

function Archivos() {
  const [archivos, setArchivos] = useState([]);
  const [loading, setLoading] = useState(true);
  const [pagination, setPagination] = useState({
    page: 0,
    pageSize: 10,
    total: 0
  });
  const [filters, setFilters] = useState({
    search: '',
    estado: '',
    departamento_id: '',
    fecha_desde: null,
    fecha_hasta: null
  });
  const [selectedRows, setSelectedRows] = useState([]);
  const [anchorEl, setAnchorEl] = useState(null);
  const [selectedArchivo, setSelectedArchivo] = useState(null);
  const [detalleDialog, setDetalleDialog] = useState(false);
  const [archivoDetalle, setArchivoDetalle] = useState(null);

  useEffect(() => {
    cargarArchivos();
  }, [pagination.page, pagination.pageSize, filters]);

  const cargarArchivos = async () => {
    setLoading(true);
    try {
      const params = {
        page: pagination.page + 1,
        per_page: pagination.pageSize,
        ...filters,
        fecha_desde: filters.fecha_desde ? filters.fecha_desde.format('YYYY-MM-DD') : undefined,
        fecha_hasta: filters.fecha_hasta ? filters.fecha_hasta.format('YYYY-MM-DD') : undefined
      };

      const response = await axios.get('/archivos', { params });
      setArchivos(response.data.data.data);
      setPagination(prev => ({
        ...prev,
        total: response.data.data.total
      }));
    } catch (error) {
      toast.error('Error al cargar archivos');
    } finally {
      setLoading(false);
    }
  };

  const handleReintento = async (id) => {
    try {
      await axios.post(`/archivos/${id}/reintento`);
      toast.success('Reprocesamiento iniciado');
      cargarArchivos();
    } catch (error) {
      toast.error('Error al reintentar procesamiento');
    }
  };

  const handleDelete = async (id) => {
    if (!confirm('¿Está seguro de eliminar este archivo?')) return;
    
    try {
      await axios.delete(`/archivos/${id}`);
      toast.success('Archivo eliminado');
      cargarArchivos();
    } catch (error) {
      toast.error('Error al eliminar archivo');
    }
  };

  const verDetalle = async (archivo) => {
    setArchivoDetalle(archivo);
    try {
      const response = await axios.get(`/archivos/${archivo.id}/estado`);
      setArchivoDetalle(prev => ({
        ...prev,
        estadoDetalle: response.data.data
      }));
    } catch (error) {
      console.error('Error al cargar detalle:', error);
    }
    setDetalleDialog(true);
  };

  const getEstadoChip = (estado) => {
    const configs = {
      'pendiente': { color: 'default', icon: <HourglassEmpty /> },
      'en_proceso': { color: 'info', icon: <HourglassEmpty /> },
      'convertido': { color: 'success', icon: <CheckCircle /> },
      'validado': { color: 'success', icon: <CheckCircle /> },
      'error': { color: 'error', icon: <Error /> }
    };
    
    const config = configs[estado] || configs['pendiente'];
    
    return (
      <Chip
        label={estado.replace('_', ' ').toUpperCase()}
        color={config.color}
        size="small"
        icon={config.icon}
      />
    );
  };
  const columns = [
    {
      field: 'nombre_archivo',
      headerName: 'Archivo',
      flex: 1,
      minWidth: 200,
      renderCell: (params) => (
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
          <Typography variant="body2" sx={{ fontWeight: 500 }}>
            {params.value}
          </Typography>
        </Box>
      )
    },
    {
      field: 'departamento',
      headerName: 'Departamento',
      width: 150,
      renderCell: (params) => (
        <Chip 
          label={params.row.departamento?.nombre || 'N/A'} 
          size="small"
          variant="outlined"
        />
      )
    },
    {
      field: 'usuario',
      headerName: 'Usuario',
      width: 150,
      renderCell: (params) => params.row.usuario?.name || 'N/A'
    },
    {
      field: 'fecha_upload',
      headerName: 'Fecha Upload',
      width: 150,
      renderCell: (params) => format(new Date(params.value), 'dd/MM/yyyy HH:mm')
    },
    {
      field: 'registros_totales',
      headerName: 'Registros',
      width: 100,
      align: 'center',
      renderCell: (params) => (
        <Box>
          <Typography variant="body2">
            {params.value || 0}
          </Typography>
          {params.row.registros_exitosos > 0 && (
            <Typography variant="caption" color="success.main">
              ✓ {params.row.registros_exitosos}
            </Typography>
          )}
        </Box>
      )
    },
    {
      field: 'estado',
      headerName: 'Estado',
      width: 130,
      renderCell: (params) => getEstadoChip(params.value)
    },
    {
      field: 'actions',
      headerName: 'Acciones',
      width: 120,
      sortable: false,
      renderCell: (params) => (
        <Box>
          <IconButton
            size="small"
            onClick={() => verDetalle(params.row)}
          >
            <Visibility />
          </IconButton>
          {params.row.estado === 'error' && (
            <IconButton
              size="small"
              onClick={() => handleReintento(params.row.id)}
              color="warning"
            >
              <RefreshIcon />
            </IconButton>
          )}
          <IconButton
            size="small"
            onClick={(e) => {
              setSelectedArchivo(params.row);
              setAnchorEl(e.currentTarget);
            }}
          >
            <MoreVert />
          </IconButton>
        </Box>
      )
    }
  ];

  return (
    <LocalizationProvider dateAdapter={AdapterDayjs}>
      <Box>
        <Typography variant="h4" gutterBottom fontWeight={600}>
          Gestión de Archivos
        </Typography>

        {/* Filtros */}
        <Paper sx={{ p: 2, mb: 3 }}>
          <Grid container spacing={2} alignItems="center">
            <Grid item xs={12} md={3}>
              <TextField
                fullWidth
                size="small"
                placeholder="Buscar archivo..."
                value={filters.search}
                onChange={(e) => setFilters(prev => ({ ...prev, search: e.target.value }))}
                InputProps={{
                  startAdornment: (
                    <InputAdornment position="start">
                      <Search />
                    </InputAdornment>
                  )
                }}
              />
            </Grid>
            <Grid item xs={12} md={2}>
              <FormControl fullWidth size="small">
                <InputLabel>Estado</InputLabel>
                <Select
                  value={filters.estado}
                  label="Estado"
                  onChange={(e) => setFilters(prev => ({ ...prev, estado: e.target.value }))}
                >
                  <MenuItem value="">Todos</MenuItem>
                  <MenuItem value="pendiente">Pendiente</MenuItem>
                  <MenuItem value="en_proceso">En Proceso</MenuItem>
                  <MenuItem value="convertido">Convertido</MenuItem>
                  <MenuItem value="validado">Validado</MenuItem>
                  <MenuItem value="error">Error</MenuItem>
                </Select>
              </FormControl>
            </Grid>
            <Grid item xs={12} md={2}>
              <DatePicker
                label="Desde"
                value={filters.fecha_desde}
                onChange={(date) => setFilters(prev => ({ ...prev, fecha_desde: date }))}
                slotProps={{ textField: { size: 'small', fullWidth: true } }}
              />
            </Grid>
            <Grid item xs={12} md={2}>
              <DatePicker
                label="Hasta"
                value={filters.fecha_hasta}
                onChange={(date) => setFilters(prev => ({ ...prev, fecha_hasta: date }))}
                slotProps={{ textField: { size: 'small', fullWidth: true } }}
              />
            </Grid>
            <Grid item xs={12} md={3}>
              <Box sx={{ display: 'flex', gap: 1 }}>
                <Button
                  variant="contained"
                  startIcon={<FilterList />}
                  onClick={cargarArchivos}
                >
                  Filtrar
                </Button>
                <Button
                  variant="outlined"
                  onClick={() => {
                    setFilters({
                      search: '',
                      estado: '',
                      departamento_id: '',
                      fecha_desde: null,
                      fecha_hasta: null
                    });
                    cargarArchivos();
                  }}
                >
                  Limpiar
                </Button>
                <IconButton onClick={cargarArchivos} color="primary">
                  <RefreshIcon />
                </IconButton>
              </Box>
            </Grid>
          </Grid>
        </Paper>

        {/* Tabla de archivos */}
        <Paper sx={{ height: 600, width: '100%' }}>
          <DataGrid
            rows={archivos}
            columns={columns}
            pageSize={pagination.pageSize}
            rowsPerPageOptions={[10, 25, 50]}
            pagination
            paginationMode="server"
            rowCount={pagination.total}
            loading={loading}
            checkboxSelection
            disableSelectionOnClick
            onPageChange={(page) => setPagination(prev => ({ ...prev, page }))}
            onPageSizeChange={(pageSize) => setPagination(prev => ({ ...prev, pageSize }))}
            onSelectionModelChange={(ids) => setSelectedRows(ids)}
            sx={{
              '& .MuiDataGrid-cell:focus': {
                outline: 'none'
              }
            }}
          />
        </Paper>

        {/* Menú contextual */}
        <Menu
          anchorEl={anchorEl}
          open={Boolean(anchorEl)}
          onClose={() => setAnchorEl(null)}
        >
          <MenuItem onClick={() => {
            verDetalle(selectedArchivo);
            setAnchorEl(null);
          }}>
            <Visibility sx={{ mr: 1 }} fontSize="small" />
            Ver detalle
          </MenuItem>
          <MenuItem onClick={() => {
            window.open(`/api/archivos/${selectedArchivo?.id}/download`, '_blank');
            setAnchorEl(null);
          }}>
            <GetApp sx={{ mr: 1 }} fontSize="small" />
            Descargar
          </MenuItem>
          {selectedArchivo?.estado === 'error' && (
            <MenuItem onClick={() => {
              handleReintento(selectedArchivo.id);
              setAnchorEl(null);
            }}>
              <RefreshIcon sx={{ mr: 1 }} fontSize="small" />
              Reintentar
            </MenuItem>
          )}
          <MenuItem onClick={() => {
            handleDelete(selectedArchivo?.id);
            setAnchorEl(null);
          }} sx={{ color: 'error.main' }}>
            <Delete sx={{ mr: 1 }} fontSize="small" />
            Eliminar
          </MenuItem>
        </Menu>

        {/* Diálogo de detalle */}
        <Dialog
          open={detalleDialog}
          onClose={() => setDetalleDialog(false)}
          maxWidth="md"
          fullWidth
        >
          <DialogTitle>
            Detalle del Archivo
          </DialogTitle>
          <DialogContent dividers>
            {archivoDetalle && (
              <Box>
                <Grid container spacing={2}>
                  <Grid item xs={12} md={6}>
                    <Typography variant="subtitle2" color="text.secondary">
                      Nombre del archivo
                    </Typography>
                    <Typography variant="body1" gutterBottom>
                      {archivoDetalle.nombre_archivo}
                    </Typography>
                  </Grid>
                  <Grid item xs={12} md={6}>
                    <Typography variant="subtitle2" color="text.secondary">
                      Estado
                    </Typography>
                    <Box sx={{ mt: 0.5 }}>
                      {getEstadoChip(archivoDetalle.estado)}
                    </Box>
                  </Grid>
                  <Grid item xs={12} md={6}>
                    <Typography variant="subtitle2" color="text.secondary">
                      Fecha de carga
                    </Typography>
                    <Typography variant="body1" gutterBottom>
                      {format(new Date(archivoDetalle.fecha_upload), 'dd/MM/yyyy HH:mm:ss')}
                    </Typography>
                  </Grid>
                  <Grid item xs={12} md={6}>
                    <Typography variant="subtitle2" color="text.secondary">
                      Usuario
                    </Typography>
                    <Typography variant="body1" gutterBottom>
                      {archivoDetalle.usuario?.name}
                    </Typography>
                  </Grid>
                  <Grid item xs={12} md={6}>
                    <Typography variant="subtitle2" color="text.secondary">
                      Departamento
                    </Typography>
                    <Typography variant="body1" gutterBottom>
                      {archivoDetalle.departamento?.nombre}
                    </Typography>
                  </Grid>
                  <Grid item xs={12} md={6}>
                    <Typography variant="subtitle2" color="text.secondary">
                      Tamaño
                    </Typography>
                    <Typography variant="body1" gutterBottom>
                      {(archivoDetalle.tamano_archivo_kb / 1024).toFixed(2)} MB
                    </Typography>
                  </Grid>
                </Grid>

                {archivoDetalle.estadoDetalle && (
                  <Box sx={{ mt: 3 }}>
                    <Typography variant="h6" gutterBottom>
                      Estadísticas de Procesamiento
                    </Typography>
                    <Grid container spacing={2}>
                      <Grid item xs={6} md={3}>
                        <Paper sx={{ p: 2, textAlign: 'center', bgcolor: 'grey.50' }}>
                          <Typography variant="h4" color="primary">
                            {archivoDetalle.estadoDetalle.progreso.total}
                          </Typography>
                          <Typography variant="caption">
                            Total Registros
                          </Typography>
                        </Paper>
                      </Grid>
                      <Grid item xs={6} md={3}>
                        <Paper sx={{ p: 2, textAlign: 'center', bgcolor: 'success.50' }}>
                          <Typography variant="h4" color="success.main">
                            {archivoDetalle.estadoDetalle.progreso.exitosos}
                          </Typography>
                          <Typography variant="caption">
                            Exitosos
                          </Typography>
                        </Paper>
                      </Grid>
                      <Grid item xs={6} md={3}>
                        <Paper sx={{ p: 2, textAlign: 'center', bgcolor: 'error.50' }}>
                          <Typography variant="h4" color="error.main">
                            {archivoDetalle.estadoDetalle.progreso.fallidos}
                          </Typography>
                          <Typography variant="caption">
                            Fallidos
                          </Typography>
                        </Paper>
                      </Grid>
                      <Grid item xs={6} md={3}>
                        <Paper sx={{ p: 2, textAlign: 'center', bgcolor: 'warning.50' }}>
                          <Typography variant="h4" color="warning.main">
                            {archivoDetalle.estadoDetalle.progreso.duplicados}
                          </Typography>
                          <Typography variant="caption">
                            Duplicados
                          </Typography>
                        </Paper>
                      </Grid>
                    </Grid>

                    <Box sx={{ mt: 2 }}>
                      <Typography variant="subtitle2" gutterBottom>
                        Progreso de Conversión
                      </Typography>
                      <LinearProgress
                        variant="determinate"
                        value={archivoDetalle.estadoDetalle.progreso.porcentaje_exito}
                        sx={{ height: 10, borderRadius: 5 }}
                      />
                      <Typography variant="caption" color="text.secondary">
                        {archivoDetalle.estadoDetalle.progreso.porcentaje_exito}% completado
                      </Typography>
                    </Box>

                    {archivoDetalle.estadoDetalle.errores && 
                     archivoDetalle.estadoDetalle.errores.length > 0 && (
                      <Alert severity="error" sx={{ mt: 2 }}>
                        <Typography variant="subtitle2" gutterBottom>
                          Errores encontrados:
                        </Typography>
                        <ul style={{ margin: 0, paddingLeft: 20 }}>
                          {archivoDetalle.estadoDetalle.errores.map((error, idx) => (
                            <li key={idx}>{error}</li>
                          ))}
                        </ul>
                      </Alert>
                    )}

                    <Typography variant="subtitle2" sx={{ mt: 2 }}>
                      Tiempo de procesamiento: {archivoDetalle.estadoDetalle.tiempo_procesamiento}
                    </Typography>
                  </Box>
                )}
              </Box>
            )}
          </DialogContent>
          <DialogActions>
            <Button onClick={() => setDetalleDialog(false)}>
              Cerrar
            </Button>
            {archivoDetalle?.estado === 'error' && (
              <Button 
                variant="contained" 
                color="warning"
                onClick={() => {
                  handleReintento(archivoDetalle.id);
                  setDetalleDialog(false);
                }}
              >
                Reintentar Procesamiento
              </Button>
            )}
          </DialogActions>
        </Dialog>
      </Box>
    </LocalizationProvider>
  );
}

export default Archivos;