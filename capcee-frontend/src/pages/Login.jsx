import React, { useState } from 'react';
import {
  Box,
  Paper,
  TextField,
  Button,
  Typography,
  Alert,
  InputAdornment,
  IconButton,
  LinearProgress
} from '@mui/material';
import {
  Visibility,
  VisibilityOff,
  Person,
  Lock
} from '@mui/icons-material';
import { useNavigate } from 'react-router-dom';
import useAuthStore from '../store/authStore';
import toast from 'react-hot-toast';

function Login() {
  const navigate = useNavigate();
  const { login, isLoading } = useAuthStore();
  const [showPassword, setShowPassword] = useState(false);
  const [formData, setFormData] = useState({
    email: '',
    password: ''
  });
  const [error, setError] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');

    if (!formData.email || !formData.password) {
      setError('Por favor complete todos los campos');
      return;
    }

    const result = await login(formData);
    
    if (result.success) {
      toast.success('Inicio de sesión exitoso');
      navigate('/dashboard');
    } else {
      setError(result.error);
    }
  };

  const handleChange = (e) => {
    setFormData(prev => ({
      ...prev,
      [e.target.name]: e.target.value
    }));
  };

  return (
    <Box
      sx={{
        minHeight: '100vh',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        padding: 2
      }}
    >
      <Paper
        elevation={10}
        sx={{
          width: '100%',
          maxWidth: 400,
          p: 4,
          borderRadius: 2
        }}
      >
        <Box sx={{ textAlign: 'center', mb: 4 }}>
          <img 
            src="/logo-capcee.png" 
            alt="CAPCEE" 
            style={{ width: 80, height: 80, marginBottom: 16 }}
          />
          <Typography variant="h5" fontWeight={600}>
            Sistema CAPCEE
          </Typography>
          <Typography variant="body2" color="text.secondary" sx={{ mt: 1 }}>
            Gestión de Archivos Excel a SQL
          </Typography>
        </Box>

        {error && (
          <Alert severity="error" sx={{ mb: 2 }}>
            {error}
          </Alert>
        )}

        <form onSubmit={handleSubmit}>
          <TextField
            fullWidth
            name="email"
            type="email"
            label="Correo electrónico"
            value={formData.email}
            onChange={handleChange}
            sx={{ mb: 2 }}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <Person />
                </InputAdornment>
              )
            }}
          />
          
          <TextField
            fullWidth
            name="password"
            type={showPassword ? 'text' : 'password'}
            label="Contraseña"
            value={formData.password}
            onChange={handleChange}
            sx={{ mb: 3 }}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <Lock />
                </InputAdornment>
              ),
              endAdornment: (
                <InputAdornment position="end">
                  <IconButton
                    onClick={() => setShowPassword(!showPassword)}
                    edge="end"
                  >
                    {showPassword ? <VisibilityOff /> : <Visibility />}
                  </IconButton>
                </InputAdornment>
              )
            }}
          />

          {isLoading && <LinearProgress sx={{ mb: 2 }} />}
          
          <Button
            fullWidth
            type="submit"
            variant="contained"
            size="large"
            disabled={isLoading}
            sx={{
              py: 1.5,
              background: 'linear-gradient(45deg, #667eea 30%, #764ba2 90%)',
              '&:hover': {
                background: 'linear-gradient(45deg, #5a67d8 30%, #6b4199 90%)'
              }
            }}
          >
            {isLoading ? 'Iniciando sesión...' : 'Iniciar Sesión'}
          </Button>
        </form>

        <Box sx={{ mt: 3, textAlign: 'center' }}>
          <Typography variant="caption" color="text.secondary">
            © 2024 CAPCEE - Todos los derechos reservados
          </Typography>
        </Box>
      </Paper>
    </Box>
  );
}

export default Login;