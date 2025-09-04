// UploadComponent.jsx
import React, { useState, useCallback } from 'react';
import { Upload, AlertCircle, CheckCircle } from 'lucide-react';

const UploadComponent = () => {
    const [uploading, setUploading] = useState(false);
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);
    const [progress, setProgress] = useState(0);

    const validateFile = (file) => {
        const errors = [];
        
        // Validar tamaño (50MB)
        if (file.size > 50 * 1024 * 1024) {
            errors.push('El archivo excede el límite de 50MB');
        }
        
        // Validar formato
        const validExtensions = ['.xlsx', '.xls'];
        const fileExtension = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();
        if (!validExtensions.includes(fileExtension)) {
            errors.push('Solo se permiten archivos Excel (.xlsx, .xls)');
        }
        
        return errors;
    };

    const handleUpload = useCallback(async (file, departamentoId) => {
        // Validación del lado del cliente
        const validationErrors = validateFile(file);
        if (validationErrors.length > 0) {
            setError(validationErrors.join(', '));
            return;
        }

        setUploading(true);
        setError(null);
        setSuccess(null);

        const formData = new FormData();
        formData.append('file', file);
        formData.append('departamento_id', departamentoId);

        try {
            const response = await fetch('/api/upload/excel', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: formData,
                // Monitorear progreso
                onUploadProgress: (progressEvent) => {
                    const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                    setProgress(percentCompleted);
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.details ? data.details.join(', ') : data.error);
            }

            setSuccess(`Archivo procesado exitosamente. ID: ${data.upload_id}`);
            
            // Si es procesamiento en cola, iniciar polling
            if (data.status === 'EN_PROCESO') {
                pollStatus(data.upload_id);
            }

        } catch (err) {
            console.error('Error en upload:', err);
            setError(err.message || 'Error al subir archivo');
        } finally {
            setUploading(false);
            setProgress(0);
        }
    }, []);

    const pollStatus = async (uploadId) => {
        const interval = setInterval(async () => {
            try {
                const response = await fetch(`/api/upload/status/${uploadId}`);
                const data = await response.json();
                
                if (data.status === 'CONVERTIDO') {
                    setSuccess('Archivo procesado completamente');
                    clearInterval(interval);
                } else if (data.status === 'ERROR') {
                    setError(`Error en procesamiento: ${data.error_mensaje}`);
                    clearInterval(interval);
                }
            } catch (err) {
                console.error('Error en polling:', err);
                clearInterval(interval);
            }
        }, 2000); // Verificar cada 2 segundos
    };

    return (
        <div className="upload-container p-6">
            {error && (
                <div className="alert alert-error mb-4">
                    <AlertCircle className="h-5 w-5" />
                    <span>{error}</span>
                </div>
            )}
            
            {success && (
                <div className="alert alert-success mb-4">
                    <CheckCircle className="h-5 w-5" />
                    <span>{success}</span>
                </div>
            )}

            {uploading && (
                <div className="progress-bar mb-4">
                    <div 
                        className="progress-bar-fill"
                        style={{ width: `${progress}%` }}
                    />
                    <span>{progress}%</span>
                </div>
            )}

            {/* Resto del componente de upload */}
        </div>
    );
};

export default UploadComponent;