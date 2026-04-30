import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Switch,
  Alert,
} from 'react-native';
import { Card, Button, List, Divider } from 'react-native-paper';
import Icon from 'react-native-vector-icons/MaterialIcons';
import { LinearGradient } from 'react-native-linear-gradient';

const SettingsScreen = ({ navigation }) => {
  const [notifications, setNotifications] = useState(true);
  const [darkMode, setDarkMode] = useState(false);
  const [autoBackup, setAutoBackup] = useState(true);
  const [lowStockAlerts, setLowStockAlerts] = useState(true);
  const [salesReports, setSalesReports] = useState(false);

  const settingsSections = [
    {
      title: 'Notificaciones',
      icon: 'notifications',
      items: [
        {
          key: 'notifications',
          title: 'Notificaciones Push',
          description: 'Recibir alertas en tiempo real',
          value: notifications,
          onToggle: setNotifications,
        },
        {
          key: 'lowStock',
          title: 'Alertas de Stock Bajo',
          description: 'Notificar cuando el stock sea bajo',
          value: lowStockAlerts,
          onToggle: setLowStockAlerts,
        },
        {
          key: 'salesReports',
          title: 'Reportes de Ventas',
          description: 'Resumen semanal de ventas',
          value: salesReports,
          onToggle: setSalesReports,
        },
      ],
    },
    {
      title: 'Datos y Almacenamiento',
      icon: 'storage',
      items: [
        {
          key: 'autoBackup',
          title: 'Backup Automático',
          description: 'Sincronizar datos con la nube',
          value: autoBackup,
          onToggle: setAutoBackup,
        },
      ],
    },
    {
      title: 'Apariencia',
      icon: 'palette',
      items: [
        {
          key: 'darkMode',
          title: 'Modo Oscuro',
          description: 'Tema oscuro para la aplicación',
          value: darkMode,
          onToggle: setDarkMode,
        },
      ],
    },
  ];

  const handleBackup = () => {
    Alert.alert(
      'Backup de Datos',
      '¿Deseas realizar un backup completo de tus datos?',
      [
        { text: 'Cancelar', style: 'cancel' },
        {
          text: 'Backup',
          onPress: () => {
            // Simulate backup process
            Alert.alert('Éxito', 'Backup completado correctamente');
          },
        },
      ]
    );
  };

  const handleExport = () => {
    Alert.alert(
      'Exportar Datos',
      'Selecciona el formato de exportación:',
      [
        { text: 'Cancelar', style: 'cancel' },
        { text: 'Excel', onPress: () => Alert.alert('Éxito', 'Datos exportados a Excel') },
        { text: 'PDF', onPress: () => Alert.alert('Éxito', 'Datos exportados a PDF') },
        { text: 'CSV', onPress: () => Alert.alert('Éxito', 'Datos exportados a CSV') },
      ]
    );
  };

  const handleClearCache = () => {
    Alert.alert(
      'Limpiar Caché',
      'Esto eliminará archivos temporales. ¿Continuar?',
      [
        { text: 'Cancelar', style: 'cancel' },
        {
          text: 'Limpiar',
          onPress: () => Alert.alert('Éxito', 'Caché limpiado correctamente'),
        },
      ]
    );
  };

  const handleResetData = () => {
    Alert.alert(
      'Restablecer Datos',
      '¡ADVERTENCIA! Esta acción eliminará todos tus datos y no se puede deshacer.',
      [
        { text: 'Cancelar', style: 'cancel' },
        {
          text: 'Restablecer',
          style: 'destructive',
          onPress: () => Alert.alert('Datos Restablecidos', 'Todos los datos han sido eliminados'),
        },
      ]
    );
  };

  const SettingItem = ({ item }) => (
    <View style={styles.settingItem}>
      <View style={styles.settingContent}>
        <Text style={styles.settingTitle}>{item.title}</Text>
        <Text style={styles.settingDescription}>{item.description}</Text>
      </View>
      <Switch
        value={item.value}
        onValueChange={item.onToggle}
        trackColor={{ false: '#e2e8f0', true: '#bfdbfe' }}
        thumbColor={item.value ? '#3b82f6' : '#ffffff'}
        ios_backgroundColor="#e2e8f0"
      />
    </View>
  );

  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      {/* Header */}
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Configuración</Text>
        <Text style={styles.headerSubtitle}>Personaliza tu experiencia</Text>
      </View>

      {/* User Profile */}
      <Card style={styles.profileCard}>
        <View style={styles.profileContent}>
          <View style={styles.avatar}>
            <Text style={styles.avatarText}>SM</Text>
          </View>
          <View style={styles.profileInfo}>
            <Text style={styles.profileName}>Stock Manager</Text>
            <Text style={styles.profileEmail}>admin@stockmanager.com</Text>
            <Text style={styles.profilePlan}>Plan Premium</Text>
          </View>
        </View>
      </Card>

      {/* Settings Sections */}
      {settingsSections.map((section, index) => (
        <Card key={index} style={styles.sectionCard}>
          <View style={styles.sectionHeader}>
            <Icon name={section.icon} size={24} color="#3b82f6" />
            <Text style={styles.sectionTitle}>{section.title}</Text>
          </View>
          <Divider style={styles.divider} />
          {section.items.map((item, itemIndex) => (
            <SettingItem key={item.key} item={item} />
          ))}
        </Card>
      ))}

      {/* Data Management */}
      <Card style={styles.sectionCard}>
        <View style={styles.sectionHeader}>
          <Icon name="settings" size={24} color="#3b82f6" />
          <Text style={styles.sectionTitle}>Gestión de Datos</Text>
        </View>
        <Divider style={styles.divider} />
        
        <TouchableOpacity style={styles.actionItem} onPress={handleBackup}>
          <View style={styles.actionContent}>
            <Icon name="backup" size={20} color="#10b981" />
            <View style={styles.actionText}>
              <Text style={styles.actionTitle}>Realizar Backup</Text>
              <Text style={styles.actionDescription}>Copia de seguridad completa</Text>
            </View>
          </View>
          <Icon name="chevron-right" size={20} color="#94a3b8" />
        </TouchableOpacity>

        <TouchableOpacity style={styles.actionItem} onPress={handleExport}>
          <View style={styles.actionContent}>
            <Icon name="download" size={20} color="#3b82f6" />
            <View style={styles.actionText}>
              <Text style={styles.actionTitle}>Exportar Datos</Text>
              <Text style={styles.actionDescription}>Excel, PDF, CSV</Text>
            </View>
          </View>
          <Icon name="chevron-right" size={20} color="#94a3b8" />
        </TouchableOpacity>

        <TouchableOpacity style={styles.actionItem} onPress={handleClearCache}>
          <View style={styles.actionContent}>
            <Icon name="cleaning-services" size={20} color="#f59e0b" />
            <View style={styles.actionText}>
              <Text style={styles.actionTitle}>Limpiar Caché</Text>
              <Text style={styles.actionDescription}>Liberar espacio de almacenamiento</Text>
            </View>
          </View>
          <Icon name="chevron-right" size={20} color="#94a3b8" />
        </TouchableOpacity>
      </Card>

      {/* About */}
      <Card style={styles.sectionCard}>
        <View style={styles.sectionHeader}>
          <Icon name="info" size={24} color="#3b82f6" />
          <Text style={styles.sectionTitle}>Acerca de</Text>
        </View>
        <Divider style={styles.divider} />
        
        <View style={styles.aboutItem}>
          <Text style={styles.aboutLabel}>Versión</Text>
          <Text style={styles.aboutValue}>1.0.0</Text>
        </View>
        
        <View style={styles.aboutItem}>
          <Text style={styles.aboutLabel}>Desarrollador</Text>
          <Text style={styles.aboutValue}>Stock Manager Team</Text>
        </View>
        
        <View style={styles.aboutItem}>
          <Text style={styles.aboutLabel}>Licencia</Text>
          <Text style={styles.aboutValue}>MIT License</Text>
        </View>
      </Card>

      {/* Danger Zone */}
      <Card style={styles.dangerCard}>
        <View style={styles.sectionHeader}>
          <Icon name="warning" size={24} color="#ef4444" />
          <Text style={[styles.sectionTitle, { color: '#ef4444' }]}>Zona de Peligro</Text>
        </View>
        <Divider style={styles.divider} />
        
        <TouchableOpacity style={styles.dangerAction} onPress={handleResetData}>
          <Icon name="restore" size={20} color="#ef4444" />
          <View style={styles.dangerText}>
            <Text style={styles.dangerTitle}>Restablecer Datos</Text>
            <Text style={styles.dangerDescription}>Eliminar todos los datos permanentemente</Text>
          </View>
        </TouchableOpacity>
      </Card>

      {/* Footer */}
      <View style={styles.footer}>
        <Text style={styles.footerText}>© 2024 Stock Manager</Text>
        <Text style={styles.footerSubtext}>Todos los derechos reservados</Text>
      </View>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f8fafc',
  },
  header: {
    padding: 20,
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#1e293b',
    marginBottom: 5,
  },
  headerSubtitle: {
    fontSize: 14,
    color: '#64748b',
  },
  profileCard: {
    margin: 15,
    marginBottom: 10,
  },
  profileContent: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 20,
  },
  avatar: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: '#3b82f6',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 16,
  },
  avatarText: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  profileInfo: {
    flex: 1,
  },
  profileName: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#1e293b',
    marginBottom: 4,
  },
  profileEmail: {
    fontSize: 14,
    color: '#64748b',
    marginBottom: 4,
  },
  profilePlan: {
    fontSize: 12,
    color: '#10b981',
    fontWeight: '500',
  },
  sectionCard: {
    margin: 15,
    marginBottom: 10,
  },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    paddingBottom: 12,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#1e293b',
    marginLeft: 12,
  },
  divider: {
    backgroundColor: '#e2e8f0',
    marginHorizontal: 16,
  },
  settingItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
  },
  settingContent: {
    flex: 1,
    marginRight: 16,
  },
  settingTitle: {
    fontSize: 16,
    fontWeight: '500',
    color: '#1e293b',
    marginBottom: 4,
  },
  settingDescription: {
    fontSize: 14,
    color: '#64748b',
  },
  actionItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
  },
  actionContent: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  actionText: {
    marginLeft: 12,
    flex: 1,
  },
  actionTitle: {
    fontSize: 16,
    fontWeight: '500',
    color: '#1e293b',
    marginBottom: 4,
  },
  actionDescription: {
    fontSize: 14,
    color: '#64748b',
  },
  aboutItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
  },
  aboutLabel: {
    fontSize: 16,
    color: '#64748b',
  },
  aboutValue: {
    fontSize: 16,
    fontWeight: '500',
    color: '#1e293b',
  },
  dangerCard: {
    margin: 15,
    marginBottom: 10,
    borderColor: '#fecaca',
    borderWidth: 1,
  },
  dangerAction: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
  },
  dangerText: {
    marginLeft: 12,
    flex: 1,
  },
  dangerTitle: {
    fontSize: 16,
    fontWeight: '500',
    color: '#ef4444',
    marginBottom: 4,
  },
  dangerDescription: {
    fontSize: 14,
    color: '#991b1b',
  },
  footer: {
    alignItems: 'center',
    padding: 20,
    paddingBottom: 40,
  },
  footerText: {
    fontSize: 14,
    color: '#64748b',
    marginBottom: 4,
  },
  footerSubtext: {
    fontSize: 12,
    color: '#94a3b8',
  },
});

export default SettingsScreen;
