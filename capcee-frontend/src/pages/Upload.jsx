import React, { useState, useCallback } from 'react';
import { useDropzone } from 'react-dropzone';
import {
  Box,
  Paper,
  Typography,
  Button,
  LinearProgress,
  Alert,
  List,
  ListItem,
  ListItemIcon,
  ListItemText,
  ListItemSecondaryAction,
  IconButton,
  Chip,
  Grid,
  Card,
  CardContent,
  Stepper,
  Step,
  StepLabel,
  Collapse,
  AlertTitle
} from '@mui/material';
import {
  CloudUpload,
  InsertDriveFile,
  Delete,
  CheckCircle,
  Error,
  Warning,
  Info,
  Send,
  Clear
} from '@mui/icons-material';
import axios from '../api/axios';
import toast from 'react-hot-toast';
import useAuthStore from '../store/authStore';

const MAX_FILES = 20;
const MAX_SIZE_MB = 50;

function Upload() {
  const [files, setFiles] = useState([]);
  const [uploading, setUploading] = useState(false);
  const [uploadProgress, setUploadProgress] = useState({});
  const [uploadResults, setUploadResults] = useState(null);
  const { user } = useAuthStore();

  const onDrop = useCallback((acceptedFiles, rejectedFiles) => {
    // Validar archivos rechazados
    if (rejectedFiles.length > 0) {
      rejectedFiles.forEach(file => {
        const errors = file.errors.map(e => e.message).join(', ');
        toast.error(`${file.file.name}: ${errors}`);
      });
    }

    // Validar límite de archivos
    if (files.length + acceptedFiles.length > MAX_FILES) {
      toast.error(`Máximo ${MAX_FILES} archivos permitidos`);
      return;
    }

    // Agregar archivos aceptados
    const newFiles = acceptedFiles.map(file => ({
      file,
      id: Math.random().toString(36).substr(2, 9),
      status: 'pending',
      progress: 0
    }));

    setFiles(prev => [...prev, ...newFiles]);
    toast.success(`${acceptedFiles.length} archivo(s) agregado(s)`);
  }, [files]);

  const { getRootProps, getInputProps, isDragActive } = useDropzone({
    onDrop,
    accept: {
      'application/vnd.ms-excel': ['.xls'],
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': ['.xlsx']
    },
    maxSize: MAX_SIZE_MB * 1024 * 1024,
    multiple: true
  });

  const removeFile = (fileId) => {
    setFiles(prev => prev.filter(f => f.id !== fileId));
  };

  const clearAll = () => {
    setFiles([]);
    setUploadProgress({});
    setUploadResults(null);
  };

  const uploadFiles = async () => {
    if (files.length === 0) {
      toast.error('No hay archivos para subir');
      return;
    }

    setUploading(true);
    setUploadResults(null);

    const formData = new FormData();
    files.forEach((fileObj, index) => {
      formData.append(`archivos[${index}]`, fileObj.file);
    });
    formData.append('departamento_id', user?.departamento?.id || 1);

    try {
      const response = await axios.post('/archivos/upload', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        },
        onUploadProgress: (progressEvent) => {
          const percentCompleted = Math.round(
            (progressEvent.loaded * 100) / progressEvent.total
          );
          setUploadProgress({ total: percentCompleted });
        }
      });

      setUploadResults(response.data);
      
      if (response.data.success) {
        toast.success(`${response.data.archivos_subidos} archivo(s) subido(s) exitosamente`);
        
        // Limpiar archivos exitosos
        setTimeout(() => {
          setFiles([]);
          setUploadProgress({});
        }, 2000);
      }

      // Mostrar errores si hay
      if (response.data.errores && response.data.errores.length > 0) {
        response.data.errores.forEach(error => toast.error(error));
      }

    } catch (error) {
      console.error('Error uploading files:', error);
      toast.error('Error al subir archivos');
    } finally {
      setUploading(false);
    }
  };

  const getFileIcon = (status) => {
    switch (status) {
      case 'success':
        return <CheckCircle color="success" />;
      case 'error':
        return <Error color="error" />;
      case 'uploading':
        return <CloudUpload color="primary" />;
      default:
        return <InsertDriveFile />;
    }
  };

  const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
  };

  return (
    <Box>
      <Typography variant="h4" gutterBottom fontWeight={600}>
        Subir Archivos Excel
      </Typography>
      
      <Grid container spacing={3}>
        {/* Información del departamento */}
        <Grid item xs={12}>
          <Alert severity="info" sx={{ mb: 2 }}>
            <AlertTitle>Límites del Departamento</AlertTitle>
            <Box sx={{ display: 'flex', gap: 3, mt: 1 }}>
              <Typography variant="body2">
                <strong>Departamento:</strong> {user?.departamento?.nombre || 'No asignado'}
              </Typography>
              <Typography variant="body2">
                <strong>Archivos hoy:</strong> {user?.departamento?.archivos_hoy || 0}/{user?.departamento?.limite_diario || 100}
              </Typography>
              <Typography variant="body2">
                <strong>Tamaño máximo:</strong> {MAX_SIZE_MB}MB por archivo
              </Typography>
              <Typography variant="body2">
                <strong>Máximo simultáneo:</strong> {MAX_FILES} archivos
              </Typography>
            </Box>
          </Alert>
        </Grid>

        {/* Zona de drop */}
        <Grid item xs={12} md={7}>
          <Paper
            {...getRootProps()}
            sx={{
              p: 4,
              border: '2px dashed',
              borderColor: isDragActive ? 'primary.main' : 'grey.300',
              bgcolor: isDragActive ? 'action.hover' : 'background.paper',
              cursor: 'pointer',
              transition: 'all 0.3s',
              textAlign: 'center',
              minHeight: 300,
              display: 'flex',
              flexDirection: 'column',
              justifyContent: 'center',
              alignItems: 'center',
              '&:hover': {
                borderColor: 'primary.main',
                bgcolor: 'action.hover'
              }
            }}
          >
            <input {...getInputProps()} />
            <CloudUpload sx={{ fontSize: 64, color: 'primary.main', mb: 2 }} />
            <Typography variant="h6" gutterBottom>
              {isDragActive
                ? 'Suelta los archivos aquí'
                : 'Arrastra archivos Excel aquí o haz clic para seleccionar'}
            </Typography>
            <Typography variant="body2" color="text.secondary">
              Formatos soportados: .xlsx, .xls (Máximo {MAX_SIZE_MB}MB por archivo)
            </Typography>
            <Button
              variant="contained"
              startIcon={<CloudUpload />}
              sx={{ mt: 2 }}
              onClick={(e) => e.stopPropagation()}
            >
              Seleccionar Archivos
            </Button>
          </Paper>
        </Grid>

        {/* Panel de información */}
        <Grid item xs={12} md={5}>
          <Card>
            <CardContent>
              <Typography variant="h6" gutterBottom>
                Proceso de Carga
              </Typography>
              <Stepper activeStep={files.length > 0 ? 1 : 0} orientation="vertical">
                <Step>
                  <StepLabel>Seleccionar archivos Excel</StepLabel>
                </Step>
                <Step>
                  <StepLabel>Validar archivos</StepLabel>
                </Step>
                <Step>
                  <StepLabel>Subir al servidor</StepLabel>
                </Step>
                <Step>
                  <StepLabel>Procesamiento automático</StepLabel>
                </Step>
              </Stepper>
              
              <Box sx={{ mt: 3 }}>
                <Alert severity="warning">
                  <Typography variant="body2">
                    <strong>Importante:</strong>
                  </Typography>
                  <ul style={{ margin: '8px 0', paddingLeft: 20 }}>
                    <li>Los archivos serán procesados automáticamente</li>
                    <li>Recibirás notificaciones del progreso</li>
                    <li>No cierres la ventana durante la carga</li>
                  </ul>
                </Alert>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        {/* Lista de archivos */}
        {files.length > 0 && (
          <Grid item xs={12}>
            <Paper sx={{ p: 2 }}>
              <Box sx={{ 
                display: 'flex', 
                justifyContent: 'space-between', 
                alignItems: 'center',
                mb: 2
              }}>
                <Typography variant="h6">
                  Archivos seleccionados ({files.length})
                </Typography>
                <Box sx={{ display: 'flex', gap: 1 }}>
                  <Button
                    size="small"
                    startIcon={<Clear />}
                    onClick={clearAll}
                    disabled={uploading}
                  >
                    Limpiar todo
                  </Button>
                  <Button
                    variant="contained"
                    startIcon={<Send />}
                    onClick={uploadFiles}
                    disabled={uploading || files.length === 0}
                  >
                    Subir archivos
                  </Button>
                </Box>
              </Box>

              {uploading && (
                <Box sx={{ mb: 2 }}>
                  <Typography variant="body2" gutterBottom>
                    Subiendo archivos... {uploadProgress.total}%
                  </Typography>
                  <LinearProgress 
                    variant="determinate" 
                    value={uploadProgress.total || 0} 
                  />
                </Box>
              )}

              <List>
                {files.map((fileObj) => (
                  <ListItem key={fileObj.id} sx={{ 
                    bgcolor: 'grey.50', 
                    mb: 1, 
                    borderRadius: 1 
                  }}>
                    <ListItemIcon>
                      {getFileIcon(fileObj.status)}
                    </ListItemIcon>
                    <ListItemText
                      primary={fileObj.file.name}
                      secondary={
                        <Box sx={{ display: 'flex', gap: 2, alignItems: 'center' }}>
                          <Chip 
                            label={formatFileSize(fileObj.file.size)} 
                            size="small" 
                            variant="outlined"
                          />
                          {fileObj.status === 'error' && (
                            <Chip 
                              label="Error" 
                              size="small" 
                              color="error"
                            />
                          )}
                          {fileObj.status === 'success' && (
                            <Chip 
                              label="Subido" 
                              size="small" 
                              color="success"
                            />
                          )}
                        </Box>
                      }
                    />
                    <ListItemSecondaryAction>
                      <IconButton 
                        edge="end" 
                        onClick={() => removeFile(fileObj.id)}
                        disabled={uploading}
                      >
                        <Delete />
                      </IconButton>
                    </ListItemSecondaryAction>
                  </ListItem>
                ))}
              </List>

              {/* Resultados del upload */}
              {uploadResults && (
                <Collapse in={true}>
                  <Alert 
                    severity={uploadResults.success ? 'success' : 'error'}
                    sx={{ mt: 2 }}
                  >
                    <AlertTitle>Resultados del proceso</AlertTitle>
                    <Typography variant="body2">
                      Archivos subidos: {uploadResults.archivos_subidos}
                    </Typography>
                    {uploadResults.errores && uploadResults.errores.length > 0 && (
                      <Box sx={{ mt: 1 }}>
                        <Typography variant="body2" color="error">
                          Errores encontrados:
                        </Typography>
                        <ul style={{ margin: 0, paddingLeft: 20 }}>
                          {uploadResults.errores.map((error, idx) => (
                            <li key={idx}>{error}</li>
                          ))}
                        </ul>
                      </Box>
                    )}
                  </Alert>
                </Collapse>
              )}
            </Paper>
          </Grid>
        )}
      </Grid>
    </Box>
  );
}

export default Upload;