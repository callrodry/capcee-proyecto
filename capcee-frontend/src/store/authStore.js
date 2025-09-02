import { create } from 'zustand';
import axios from '../api/axios';

const useAuthStore = create((set, get) => ({
  user: null,
  token: localStorage.getItem('token'),
  isAuthenticated: !!localStorage.getItem('token'),
  isLoading: false,

  login: async (credentials) => {
    set({ isLoading: true });
    try {
      const response = await axios.post('/login', credentials);
      const { token, user } = response.data;
      
      localStorage.setItem('token', token);
      set({ 
        user, 
        token, 
        isAuthenticated: true,
        isLoading: false 
      });
      
      return { success: true };
    } catch (error) {
      set({ isLoading: false });
      return { 
        success: false, 
        error: error.response?.data?.message || 'Error al iniciar sesión' 
      };
    }
  },

  logout: async () => {
    try {
      await axios.post('/logout');
    } catch (error) {
      console.error('Error al cerrar sesión:', error);
    } finally {
      localStorage.removeItem('token');
      set({ 
        user: null, 
        token: null, 
        isAuthenticated: false 
      });
    }
  },

  fetchUser: async () => {
    if (!get().token) return;
    
    set({ isLoading: true });
    try {
      const response = await axios.get('/user');
      set({ 
        user: response.data,
        isLoading: false 
      });
    } catch (error) {
      console.error('Error fetching user:', error);
      if (error.response?.status === 401) {
        get().logout();
      }
      set({ isLoading: false });
    }
  },

  checkAuth: () => {
    const token = localStorage.getItem('token');
    if (!token) {
      set({ isAuthenticated: false, user: null });
      return false;
    }
    get().fetchUser();
    return true;
  }
}));

export default useAuthStore;