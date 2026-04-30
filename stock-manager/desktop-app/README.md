# Stock Manager Desktop App

Aplicación de escritorio para gestión de inventario, desarrollada con Electron.

## 🚀 Características

- ✅ Gestión completa de inventario
- ✅ Sincronización automática en la nube
- ✅ Modo offline con sincronización posterior
- ✅ Exportación a PDF, Excel, CSV
- ✅ Atajos de teclado personalizables
- ✅ Interfaz moderna y responsiva
- ✅ Soporte multiplataforma
- ✅ Actualizaciones automáticas
- ✅ Backup y restauración

## 💻 Requisitos del Sistema

### Windows
- Windows 10 (1903) o superior
- Mínimo 4GB RAM
- 200MB de espacio disponible
- Procesador de 64 bits

### macOS
- macOS 10.14 (Mojave) o superior
- Mínimo 4GB RAM
- 250MB de espacio disponible
- Procesador Intel o Apple Silicon

### Linux
- Ubuntu 18.04, Fedora 30, o equivalentes
- Mínimo 4GB RAM
- 200MB de espacio disponible
- Entorno de escritorio GTK3

## 🛠️ Instalación

### Para Usuarios

#### Windows
1. Descargar `Stock-Manager-Setup.exe` desde [aquí](https://stockmanager.com/desktop/download)
2. Ejecutar el instalador como administrador
3. Seguir las instrucciones del asistente
4. La aplicación se instalará en `C:\Program Files\Stock Manager`

#### macOS
1. Descargar `Stock-Manager.dmg` desde [aquí](https://stockmanager.com/desktop/download)
2. Abrir el archivo DMG
3. Arrastrar Stock Manager a Applications
4. Ejecutar desde Launchpad o Applications

#### Linux
1. Descargar `Stock-Manager.AppImage` desde [aquí](https://stockmanager.com/desktop/download)
2. Dar permisos de ejecución:
   ```bash
   chmod +x Stock-Manager.AppImage
   ```
3. Ejecutar:
   ```bash
   ./Stock-Manager.AppImage
   ```

### Para Desarrolladores

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/stockmanager/desktop-app.git
   cd desktop-app
   ```

2. **Instalar dependencias**
   ```bash
   npm install
   ```

3. **Configurar variables de entorno**
   ```bash
   cp .env.example .env
   # Editar .env con tus credenciales
   ```

4. **Ejecutar en desarrollo**
   ```bash
   npm start
   # o
   npm run dev
   ```

## 🏗️ Arquitectura

```
src/
├── main/               # Proceso principal (Electron)
├── renderer/           # Proceso de renderizado (UI)
│   ├── components/     # Componentes React
│   ├── pages/         # Páginas de la aplicación
│   ├── services/      # Servicios API
│   ├── store/         # Gestión de estado
│   └── utils/         # Utilidades
├── assets/             # Recursos estáticos
└── build/              # Archivos de construcción
```

## 📦 Construcción

### Desarrollo
```bash
npm start
```

### Producción
```bash
# Construir para todas las plataformas
npm run build

# Construir para Windows
npm run build:win

# Construir para macOS
npm run build:mac

# Construir para Linux
npm run build:linux
```

### Empaquetado
```bash
# Crear instaladores
npm run dist

# Solo para Windows
npm run dist:win

# Solo para macOS
npm run dist:mac

# Solo para Linux
npm run dist:linux
```

## 🔧 Configuración

### Variables de Entorno
```env
NODE_ENV=production
API_URL=https://api.stockmanager.com
API_KEY=tu_api_key
UPDATE_SERVER=https://updates.stockmanager.com
```

### Configuración de Base de Datos
La aplicación utiliza SQLite para almacenamiento local:
- Ubicación Windows: `%APPDATA%/Stock Manager/database.sqlite`
- Ubicación macOS: `~/Library/Application Support/Stock Manager/database.sqlite`
- Ubicación Linux: `~/.config/Stock Manager/database.sqlite`

### Atajos de Teclado
- `Ctrl/Cmd + N`: Nuevo producto
- `Ctrl/Cmd + I`: Importar datos
- `Ctrl/Cmd + E`: Exportar datos
- `Ctrl/Cmd + B`: Backup
- `Ctrl/Cmd + R`: Recargar datos
- `Ctrl/Cmd + ,`: Configuración
- `F11`: Pantalla completa

## 🚨 Solución de Problemas

### Problemas Comunes

**La aplicación no se inicia en Windows**
- Verifica que .NET Framework 4.7.2 esté instalado
- Ejecuta como administrador
- Deshabilita temporalmente el antivirus

**Error de actualización en macOS**
- Ve a Seguridad y Privacidad > General
- Permite aplicaciones de desarrolladores no identificados
- O usa: `sudo spctl --master-disable`

**Problemas de renderizado en Linux**
- Instala librerías adicionales:
  ```bash
  sudo apt-get install libgtk-3-0 libnotify4 libnss3 libxss1 libxtst6 xdg-utils libatspi2.0-0 libdrm2 libxcomposite1 libxdamage1 libxrandr2 libgbm1 libxkbcommon0 libasound2
  ```

**Sincronización fallida**
- Verifica conexión a internet
- Confirma credenciales del API
- Revisa firewall y proxy

### Depuración

Para habilitar modo debug:
```bash
npm run debug
```

Consola de desarrollador: `Ctrl/Cmd + Shift + I`

Logs disponibles en:
- Windows: `%APPDATA%/Stock Manager/logs/`
- macOS: `~/Library/Logs/Stock Manager/`
- Linux: `~/.config/Stock Manager/logs/`

## 🔐 Seguridad

- Encriptación de datos local con AES-256
- Comunicación segura via HTTPS/TLS
- Autenticación de dos factores disponible
- Actualizaciones firmadas digitalmente
- Aislamiento de procesos (sandbox)

## 📊 Rendimiento

- Tiempo de inicio: < 3 segundos
- Uso de memoria: < 150MB en reposo
- Tamaño de instalación: ~80MB
- Soporte para hasta 100,000 productos

## 🤝 Contribución

1. Fork del proyecto
2. Crear rama de feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit de cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crear Pull Request

### Guía de Estilo
- Usar TypeScript para nuevo código
- Seguir convención de nomenclatura
- Agregar tests para nuevas funcionalidades
- Documentar APIs y componentes

## 📄 Licencia

Este proyecto está licenciado bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para detalles.

## 📞 Soporte

- 📧 Email: soporte@stockmanager.com
- 📱 Teléfono: +240 555 0123
- 💬 Chat: [stockmanager.com/chat](https://stockmanager.com/chat)
- 📖 Documentación: [docs.stockmanager.com](https://docs.stockmanager.com)
- 🐛 Reportar bugs: [github.com/stockmanager/desktop-app/issues](https://github.com/stockmanager/desktop-app/issues)

## 🔄 Actualizaciones

La aplicación verifica automáticamente actualizaciones cada 24 horas. También puedes verificar manualmente en:
`Ayuda > Verificar actualizaciones`

Las actualizaciones incluyen:
- Nuevas funcionalidades
- Mejoras de rendimiento
- Parches de seguridad
- Corrección de bugs

## 📈 Historial de Versiones

### v1.0.0 (Actual)
- Lanzamiento inicial
- Gestión básica de inventario
- Sincronización en la nube
- Exportación de datos

### Próximas versiones
- v1.1.0: Gestión de proveedores
- v1.2.0: Reportes avanzados
- v1.3.0: Integración POS

---

**Desarrollado con ❤️ por Stock Manager Team**
