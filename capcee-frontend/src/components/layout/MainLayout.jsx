import React, { useState } from 'react';
import { Outlet, useNavigate, useLocation } from 'react-router-dom';
import {
  Box,
  Drawer,
  AppBar,
  Toolbar,
  Typography,
  IconButton,
  List,
  ListItem,
  ListItemIcon,
  ListItemText,
  ListItemButton,
  Divider,
  Avatar,
  Menu,
  MenuItem,
  Badge,
  Chip
} from '@mui/material';
import {
  Menu as MenuIcon,
  Dashboard,
  CloudUpload,
  Folder,
  Assessment,
  Settings,
  Logout,
  Person,
  Notifications,
  Business
} from '@mui/icons-material';
import useAuthStore from '../../store/authStore';
import { useEffect, useState as useNotificationState } from 'react';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const drawerWidth = 280;

const menuItems = [
  { 
    text: 'Dashboard', 
    icon: <Dashboard />, 
    path: '/dashboard',
    color: '#1976d2'
  },
  { 
    text: 'Subir Archivos', 
    icon: <CloudUpload />, 
    path: '/upload',
    color: '#388e3c'
  },
  { 
    text: 'Archivos', 
    icon: <Folder />, 
    path: '/archivos',
    color: '#f57c00'
  },
  { 
    text: 'Reportes', 
    icon: <Assessment />, 
    path: '/reportes',
    color: '#7b1fa2'
  },
  { 
    text: 'Departamentos', 
    icon: <Business />, 
    path: '/departamentos',
    color: '#0288d1'
  }
];

function MainLayout() {
  const [mobileOpen, setMobileOpen] = useState(false);
  const [anchorEl, setAnchorEl] = useState(null);
  const [notifications, setNotifications] = useNotificationState([]);
  const navigate = useNavigate();
  const location = useLocation();
  const { user, logout } = useAuthStore();

  // Configurar WebSockets para notificaciones en tiempo real
  useEffect(() => {
    if (user) {
      window.Pusher = Pusher;
      
      window.Echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_KEY,
        cluster: import.meta.env.VITE_PUSHER_CLUSTER,
        wsHost: import.meta.env.VITE_PUSHER_HOST,
        wsPort: import.meta.env.VITE_PUSHER_PORT,
        forceTLS: false,
        disableStats: true
      });

      // Escuchar eventos de archivos procesados
      window.Echo.channel('archivos')
        .listen('ArchivoProcesado', (e) => {
          setNotifications(prev => [...prev, {
            id: Date.now(),
            message: `Archivo ${e.archivo.nombre_archivo} procesado`,
            type: e.resultado ? 'success' : 'error'
          }]);
        });
    }

    return () => {
      if (window.Echo) {
        window.Echo.disconnect();
      }
    };
  }, [user]);

  const handleDrawerToggle = () => {
    setMobileOpen(!mobileOpen);
  };

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  const handleMenuClick = (event) => {
    setAnchorEl(event.currentTarget);
  };

  const handleMenuClose = () => {
    setAnchorEl(null);
  };

  const drawer = (
    <Box>
      <Box sx={{ p: 2, display: 'flex', alignItems: 'center', gap: 2 }}>
        <img 
          src="/logo-capcee.png" 
          alt="CAPCEE" 
          style={{ width: 40, height: 40 }}
        />
        <Box>
          <Typography variant="h6" sx={{ fontWeight: 600 }}>
            CAPCEE
          </Typography>
          <Typography variant="caption" color="text.secondary">
            Sistema de Gestión
          </Typography>
        </Box>
      </Box>
      <Divider />
      
      <List sx={{ px: 2, py: 1 }}>
        {menuItems.map((item) => (
          <ListItem key={item.text} disablePadding sx={{ mb: 0.5 }}>
            <ListItemButton
              onClick={() => navigate(item.path)}
              selected={location.pathname === item.path}
              sx={{
                borderRadius: 2,
                '&.Mui-selected': {
                  bgcolor: `${item.color}15`,
                  '&:hover': {
                    bgcolor: `${item.color}25`,
                  },
                  '& .MuiListItemIcon-root': {
                    color: item.color,
                  },
                  '& .MuiListItemText-primary': {
                    color: item.color,
                    fontWeight: 600,
                  },
                },
                '&:hover': {
                  bgcolor: 'action.hover',
                },
              }}
            >
              <ListItemIcon sx={{ minWidth: 40 }}>
                {item.icon}
              </ListItemIcon>
              <ListItemText primary={item.text} />
            </ListItemButton>
          </ListItem>
        ))}
      </List>
      
      <Box sx={{ flexGrow: 1 }} />
      
      <Divider />
      <Box sx={{ p: 2 }}>
        <Box sx={{ 
          p: 2, 
          bgcolor: 'grey.100', 
          borderRadius: 2,
          textAlign: 'center'
        }}>
          <Typography variant="caption" display="block" gutterBottom>
            Departamento
          </Typography>
          <Chip 
            label={user?.departamento?.nombre || 'Sin asignar'}
            size="small"
            color="primary"
            variant="outlined"
          />
          <Typography variant="caption" display="block" sx={{ mt: 1 }}>
            Archivos hoy: {user?.departamento?.archivos_hoy || 0}/{user?.departamento?.limite_diario || 100}
          </Typography>
        </Box>
      </Box>
    </Box>
  );

  return (
    <Box sx={{ display: 'flex', minHeight: '100vh' }}>
      <AppBar
        position="fixed"
        sx={{
          width: { sm: `calc(100% - ${drawerWidth}px)` },
          ml: { sm: `${drawerWidth}px` },
          bgcolor: 'white',
          color: 'text.primary',
          boxShadow: 1
        }}
      >
        <Toolbar>
          <IconButton
            edge="start"
            onClick={handleDrawerToggle}
            sx={{ mr: 2, display: { sm: 'none' } }}
          >
            <MenuIcon />
          </IconButton>
          
          <Typography variant="h6" noWrap component="div" sx={{ flexGrow: 1 }}>
            {menuItems.find(item => item.path === location.pathname)?.text || 'CAPCEE'}
          </Typography>
          
          <IconButton 
            size="large" 
            sx={{ mr: 1 }}
            onClick={() => navigate('/notificaciones')}
          >
            <Badge badgeContent={notifications.length} color="error">
              <Notifications />
            </Badge>
          </IconButton>
          
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
            <Typography variant="body2" sx={{ mr: 1 }}>
              {user?.name || 'Usuario'}
            </Typography>
            <IconButton onClick={handleMenuClick}>
              <Avatar sx={{ width: 32, height: 32, bgcolor: 'primary.main' }}>
                {user?.name?.charAt(0).toUpperCase() || 'U'}
              </Avatar>
            </IconButton>
          </Box>
          
          <Menu
            anchorEl={anchorEl}
            open={Boolean(anchorEl)}
            onClose={handleMenuClose}
          >
            <MenuItem onClick={() => { handleMenuClose(); navigate('/perfil'); }}>
              <ListItemIcon><Person fontSize="small" /></ListItemIcon>
              Mi Perfil
            </MenuItem>
            <MenuItem onClick={() => { handleMenuClose(); navigate('/configuracion'); }}>
              <ListItemIcon><Settings fontSize="small" /></ListItemIcon>
              Configuración
            </MenuItem>
            <Divider />
            <MenuItem onClick={handleLogout}>
              <ListItemIcon><Logout fontSize="small" /></ListItemIcon>
              Cerrar Sesión
            </MenuItem>
          </Menu>
        </Toolbar>
      </AppBar>
      
      <Box
        component="nav"
        sx={{ width: { sm: drawerWidth }, flexShrink: { sm: 0 } }}
      >
        <Drawer
          variant="temporary"
          open={mobileOpen}
          onClose={handleDrawerToggle}
          ModalProps={{ keepMounted: true }}
          sx={{
            display: { xs: 'block', sm: 'none' },
            '& .MuiDrawer-paper': { 
              boxSizing: 'border-box', 
              width: drawerWidth 
            },
          }}
        >
          {drawer}
        </Drawer>
        <Drawer
          variant="permanent"
          sx={{
            display: { xs: 'none', sm: 'block' },
            '& .MuiDrawer-paper': { 
              boxSizing: 'border-box', 
              width: drawerWidth,
              borderRight: '1px solid rgba(0, 0, 0, 0.08)'
            },
          }}
          open
        >
          {drawer}
        </Drawer>
      </Box>
      
      <Box
        component="main"
        sx={{
          flexGrow: 1,
          p: 3,
          width: { sm: `calc(100% - ${drawerWidth}px)` },
          mt: 8,
          bgcolor: '#f5f5f7',
          minHeight: '100vh'
        }}
      >
        <Outlet />
      </Box>
    </Box>
  );
}

export default MainLayout;