import React, { useState, useEffect } from 'react';
import {
  Box,
  Grid,
  Paper,
  Typography,
  Card,
  CardContent,
  LinearProgress,
  Chip,
  IconButton,
  Select,
  MenuItem,
  FormControl,
  InputLabel,
  Button,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Avatar,
  AvatarGroup,
  Tooltip
} from '@mui/material';
import {
  TrendingUp,
  TrendingDown,
  Folder,
  CheckCircle,
  Error,
  HourglassEmpty,
  Refresh,
  GetApp,
  Description,
  AttachMoney,
  Engineering,
  LocationCity
} from '@mui/icons-material';
import {
  AreaChart,
  Area,
  BarChart,
  Bar,
  LineChart,
  Line,
  PieChart,
  Pie,
  Cell,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip as RechartsTooltip,
  Legend,
  ResponsiveContainer
} from 'recharts';
import axios from '../api/axios';
import toast from 'react-hot-toast';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';

const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884D8'];

function Dashboard() {
  const [loading, setLoading] = useState(true);
  const [periodo, setPeriodo] = useState('hoy');
  const [metricas, setMetricas] = useState(null);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    cargarMetricas();
  }, [periodo]);

  const cargarMetricas = async () => {
    setLoading(true);
    try {
      const response = await axios.get('/dashboard/metricas', {
        params: { periodo }
      });
      setMetricas(response.data.data);
    } catch (error) {
      toast.error('Error al cargar métricas');
    } finally {
      setLoading(false);
    }
  };

  const handleRefresh = async () => {
    setRefreshing(true);
    await cargarMetricas();
    setRefreshing(false);
    toast.success('Dashboard actualizado');
  };

  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('es-MX', {
      style: 'currency',
      currency: 'MXN',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(amount || 0);
  };

  const MetricCard = ({ title, value, icon, color, trend, subtitle }) => (
    <Card sx={{ height: '100%' }}>
      <CardContent>
        <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 2 }}>
          <Avatar sx={{ bgcolor: `${color}.light`, color: `${color}.main` }}>
            {icon}
          </Avatar>
          {trend && (
            <Chip
              size="small"
              icon={trend > 0 ? <TrendingUp /> : <TrendingDown />}
              label={`${Math.abs(trend)}%`}
              color={trend > 0 ? 'success' : 'error'}
              variant="outlined"
            />
          )}
        </Box>
        <Typography color="text.secondary" gutterBottom variant="body2">
          {title}
        </Typography>
        <Typography variant="h4" component="div" fontWeight={600}>
          {value}
        </Typography>
        {subtitle && (
          <Typography variant="caption" color="text.secondary" sx={{ mt: 1 }}>
            {subtitle}
          </Typography>
        )}
      </CardContent>
    </Card>
  );

  if (loading && !metricas) {
    return (
      <Box sx={{ 
        display: 'flex', 
        justifyContent: 'center', 
        alignItems: 'center', 
        minHeight: '60vh' 
      }}>
        <LinearProgress sx={{ width: '50%' }} />
      </Box>
    );
  }

  return (
    <Box>
      {/* Header */}
      <Box sx={{ 
        display: 'flex', 
        justifyContent: 'space-between', 
        alignItems: 'center',
        mb: 3 
      }}>
        <Box>
          <Typography variant="h4" fontWeight={600}>
            Dashboard
          </Typography>
          <Typography variant="body2" color="text.secondary">
            Sistema de Control de Archivos Excel - CAPCEE
          </Typography>
        </Box>
        <Box sx={{ display: 'flex', gap: 2 }}>
          <FormControl size="small" sx={{ minWidth: 120 }}>
            <InputLabel>Período</InputLabel>
            <Select
              value={periodo}
              label="Período"
              onChange={(e) => setPeriodo(e.target.value)}
            >
              <MenuItem value="hoy">Hoy</MenuItem>
              <MenuItem value="semana">Esta Semana</MenuItem>
              <MenuItem value="mes">Este Mes</MenuItem>
            </Select>
          </FormControl>
          <Button
            variant="outlined"
            startIcon={<GetApp />}
            onClick={() => toast.info('Exportando reporte...')}
          >
            Exportar
          </Button>
          <IconButton 
            onClick={handleRefresh}
            disabled={refreshing}
            color="primary"
          >
            <Refresh className={refreshing ? 'rotating' : ''} />
          </IconButton>
        </Box>
      </Box>

      {/* Métricas principales */}
      <Grid container spacing={3} sx={{ mb: 3 }}>
        <Grid item xs={12} sm={6} md={3}>
          <MetricCard
            title="Archivos Totales"
            value={metricas?.metricas?.archivos_totales || 0}
            icon={<Folder />}
            color="primary"
            trend={12}
            subtitle="Procesados este período"
          />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <MetricCard
            title="Archivos Procesados"
            value={metricas?.metricas?.archivos_procesados || 0}
            icon={<CheckCircle />}
            color="success"
            subtitle="Completados exitosamente"
          />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <MetricCard
            title="Archivos Pendientes"
            value={metricas?.metricas?.archivos_pendientes || 0}
            icon={<HourglassEmpty />}
            color="warning"
            subtitle="En cola de procesamiento"
          />
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <MetricCard
            title="Archivos con Error"
            value={metricas?.metricas?.archivos_error || 0}
            icon={<Error />}
            color="error"
            trend={-8}
            subtitle="Requieren revisión"
          />
        </Grid>
      </Grid>

      {/* Métricas financieras */}
      <Grid container spacing={3} sx={{ mb: 3 }}>
        <Grid item xs={12} md={4}>
          <Card>
            <CardContent>
              <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                <AttachMoney sx={{ mr: 1, color: 'success.main' }} />
                <Typography variant="h6">Montos Autorizados</Typography>
              </Box>
              <Typography variant="h4" color="success.main" fontWeight={600}>
                {formatCurrency(metricas?.metricas?.monto_total_autorizado)}
              </Typography>
              <LinearProgress 
                variant="determinate" 
                value={75} 
                sx={{ mt: 2, height: 8, borderRadius: 4 }}
                color="success"
              />
              <Typography variant="caption" color="text.secondary">
                75% del presupuesto anual
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} md={4}>
          <Card>
            <CardContent>
              <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                <AttachMoney sx={{ mr: 1, color: 'warning.main' }} />
                <Typography variant="h6">Montos Pagados</Typography>
              </Box>
              <Typography variant="h4" color="warning.main" fontWeight={600}>
                {formatCurrency(metricas?.metricas?.monto_total_pagado)}
              </Typography>
              <LinearProgress 
                variant="determinate" 
                value={60} 
                sx={{ mt: 2, height: 8, borderRadius: 4 }}
                color="warning"
              />
              <Typography variant="caption" color="text.secondary">
                60% del monto autorizado
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} md={4}>
          <Card>
            <CardContent>
              <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                <Description sx={{ mr: 1, color: 'info.main' }} />
                <Typography variant="h6">Registros Totales</Typography>
              </Box>
              <Typography variant="h4" color="info.main" fontWeight={600}>
                {metricas?.metricas?.registros_totales?.toLocaleString() || 0}
              </Typography>
              <Typography variant="caption" color="text.secondary" display="block" sx={{ mt: 2 }}>
                Registros en base de datos
              </Typography>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      {/* Gráficos - Continuación en siguiente mensaje por límite de caracteres... */}

      {/* CONTINÚA... */}
    </Box>
  );
}

export default Dashboard;