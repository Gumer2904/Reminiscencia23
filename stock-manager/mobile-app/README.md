# Stock Manager Mobile App

Aplicación móvil de gestión de inventario para Stock Manager, desarrollada con React Native.

## 🚀 Características

- ✅ Gestión completa de inventario
- ✅ Escaneo de códigos de barras
- ✅ Sincronización en tiempo real
- ✅ Modo offline
- ✅ Notificaciones push
- ✅ Reportes y análisis
- ✅ Interfaz moderna e intuitiva
- ✅ Soporte para Android e iOS

## 📱 Requisitos del Sistema

### Android
- Android 8.0 (API nivel 26) o superior
- Mínimo 2GB RAM
- 50MB de espacio disponible

### iOS
- iOS 12.0 o superior
- iPhone 6s o superior
- 100MB de espacio disponible

## 🛠️ Instalación

### Para Desarrolladores

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/stockmanager/mobile-app.git
   cd mobile-app
   ```

2. **Instalar dependencias**
   ```bash
   npm install
   # o
   yarn install
   ```

3. **Configurar variables de entorno**
   ```bash
   cp .env.example .env
   # Editar .env con tus credenciales
   ```

4. **Ejecutar en desarrollo**
   
   Para Android:
   ```bash
   npm run android
   ```
   
   Para iOS:
   ```bash
   npm run ios
   ```

### Para Usuarios

1. Descargar el APK desde [aquí](https://stockmanager.com/mobile/download)
2. Habilitar "Fuentes desconocidas" en configuración de Android
3. Instalar el APK
4. Abrir la aplicación y registrarse/iniciar sesión

## 🏗️ Arquitectura

```
src/
├── components/          # Componentes reutilizables
├── screens/            # Pantallas de la aplicación
├── navigation/         # Configuración de navegación
├── services/           # Servicios API
├── utils/              # Utilidades y helpers
├── store/              # Gestión de estado
└── assets/             # Imágenes y recursos
```

## 📦 Construcción

### Android APK
```bash
npm run build:android
```

### iOS IPA
```bash
npm run build:ios
```

## 🔧 Configuración

### Variables de Entorno
```env
API_URL=https://api.stockmanager.com
API_KEY=tu_api_key
PUSH_NOTIFICATION_KEY=tu_push_key
```

### Configuración de Base de Datos
La aplicación utiliza SQLite para almacenamiento local y se sincroniza con el backend.

## 🚨 Solución de Problemas

### Problemas Comunes

**La aplicación no se instala en Android**
- Asegúrate de haber habilitado fuentes desconocidas
- Verifica que tengas suficiente espacio de almacenamiento
- Intenta con una versión más reciente de Android

**Error de sincronización**
- Verifica tu conexión a internet
- Confirma que tus credenciales son correctas
- Reinicia la aplicación

**Notificaciones no funcionan**
- Asegúrate de haber dado permisos de notificación
- Revisa la configuración de notificaciones del sistema
- Verifica que la aplicación esté en segundo plano

### Depuración

Para habilitar el modo debug:
```bash
npm run debug
```

Logs disponibles en:
- Android: `adb logcat`
- iOS: Console.app

## 🤝 Contribución

1. Fork del proyecto
2. Crear rama de feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit de cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crear Pull Request

## 📄 Licencia

Este proyecto está licenciado bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para detalles.

## 📞 Soporte

- 📧 Email: soporte@stockmanager.com
- 📱 Teléfono: +240 555 0123
- 💬 Chat: [stockmanager.com/chat](https://stockmanager.com/chat)
- 📖 Documentación: [docs.stockmanager.com](https://docs.stockmanager.com)

## 🔄 Actualizaciones

La aplicación verifica automáticamente actualizaciones cada 24 horas. También puedes verificar manualmente en:
`Configuración > Acerca de > Verificar actualizaciones`

## 📊 Estadísticas

- ⭐ 4.8/5 en Google Play Store
- ⭐ 4.7/5 en App Store
- 📥 50,000+ descargas
- 🌍 Disponible en 15+ países

---

**Desarrollado con ❤️ por Stock Manager Team**
