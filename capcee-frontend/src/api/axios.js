import axios from 'axios';
import toast from 'react-hot-toast';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

const axiosInstance = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  withCredentials: true
});

// Interceptor para agregar token
axiosInstance.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Interceptor para manejar respuestas
axiosInstance.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response) {
      switch (error.response.status) {
        case 401:
          localStorage.removeItem('token');
          window.location.href = '/login';
          toast.error('Sesión expirada. Por favor inicie sesión nuevamente.');
          break;
        case 403:
          toast.error('No tiene permisos para realizar esta acción');
          break;
        case 404:
          toast.error('Recurso no encontrado');
          break;
        case 422:
          const errors = error.response.data.errors;
          if (errors) {
            Object.values(errors).flat().forEach(err => toast.error(err));
          } else {
            toast.error('Error de validación');
          }
          break;
        case 429:
          toast.error('Demasiadas solicitudes. Por favor espere un momento.');
          break;
        case 500:
          toast.error('Error del servidor. Por favor intente más tarde.');
          break;
        default:
          toast.error(error.response.data.message || 'Ha ocurrido un error');
      }
    } else if (error.request) {
      toast.error('No se pudo conectar con el servidor');
    } else {
      toast.error('Error al procesar la solicitud');
    }
    return Promise.reject(error);
  }
);

export default axiosInstance;