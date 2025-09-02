import React, { useEffect } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { ThemeProvider, createTheme } from '@mui/material/styles';
import CssBaseline from '@mui/material/CssBaseline';
import { Toaster } from 'react-hot-toast';
import useAuthStore from './store/authStore';

// Layouts
import MainLayout from './components/layout/MainLayout';

// Pages
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import Upload from './pages/Upload';
import Archivos from './pages/Archivos';
import Reportes from './pages/Reportes';
import Departamentos from './pages/Departamentos';

// Componente de ruta protegida
const ProtectedRoute = ({ children }) => {
  const { isAuthenticated, checkAuth } = useAuthStore();
  
  useEffect(() => {
    checkAuth();
  }, []);

  if (!isAuthenticated) {
    return <Navigate to="/login" />;
  }

  return children;
};

// Tema personalizado
const theme = createTheme({
  palette: {
    primary: {
      main: '#667eea',
      light: '#8b9af3',
      dark: '#4c5fcf'
    },
    secondary: {
      main: '#764ba2',
      light: '#9b6cc7',
      dark: '#5a3980'
    },
    success: {
      main: '#48bb78',
      50: '#f0fdf4'
    },
    error: {
      main: '#f56565',
      50: '#fef2f2'
    },
    warning: {
      main: '#ed8936',
      50: '#fffbeb'
    },
    info: {
      main: '#4299e1',
      50: '#ebf8ff'
    }
  },
  typography: {
    fontFamily: '"Inter", "Roboto", "Helvetica", "Arial", sans-serif',
    h4: {
      fontWeight: 600
    },
    h5: {
      fontWeight: 600
    },
    h6: {
      fontWeight: 600
    }
  },
  shape: {
    borderRadius: 8
  },
  components: {
    MuiButton: {
      styleOverrides: {
        root: {
          textTransform: 'none',
          fontWeight: 500
        }
      }
    },
    MuiPaper: {
      styleOverrides: {
        root: {
          boxShadow: '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)'
        }
      }
    }
  }
});

function App() {
  return (
    <ThemeProvider theme={theme}>
      <CssBaseline />
      <BrowserRouter>
        <Routes>
          {/* Ruta pública */}
          <Route path="/login" element={<Login />} />
          
          {/* Rutas protegidas */}
          <Route
            path="/"
            element={
              <ProtectedRoute>
                <MainLayout />
              </ProtectedRoute>
            }
          >
            <Route index element={<Navigate to="/dashboard" />} />
            <Route path="dashboard" element={<Dashboard />} />
            <Route path="upload" element={<Upload />} />
            <Route path="archivos" element={<Archivos />} />
            <Route path="reportes" element={<Reportes />} />
            <Route path="departamentos" element={<Departamentos />} />
          </Route>
          
          {/* Ruta 404 */}
          <Route path="*" element={<Navigate to="/dashboard" />} />
        </Routes>
      </BrowserRouter>
      
      <Toaster
        position="top-right"
        toastOptions={{
          duration: 4000,
          style: {
            background: '#363636',
            color: '#fff',
          },
          success: {
            style: {
              background: theme.palette.success.main,
            }
          },
          error: {
            style: {
              background: theme.palette.error.main,
            }
          }
        }}
      />
    </ThemeProvider>
  );
}

export default App;