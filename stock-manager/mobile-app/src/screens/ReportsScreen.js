import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  Dimensions,
  TouchableOpacity,
} from 'react-native';
import { Card, Button, Chip, DataTable } from 'react-native-paper';
import Icon from 'react-native-vector-icons/MaterialIcons';
import { LineChart, BarChart } from 'react-native-charts-wrapper';
import { LinearGradient } from 'react-native-linear-gradient';

const { width } = Dimensions.get('window');

const ReportsScreen = ({ navigation }) => {
  const [selectedPeriod, setSelectedPeriod] = useState('mes');
  const [selectedReport, setSelectedReport] = useState('ventas');
  const [loading, setLoading] = useState(false);

  const periods = [
    { key: 'hoy', label: 'Hoy' },
    { key: 'semana', label: 'Semana' },
    { key: 'mes', label: 'Mes' },
    { key: 'año', label: 'Año' },
  ];

  const reportTypes = [
    { key: 'ventas', label: 'Ventas', icon: 'trending-up' },
    { key: 'inventario', label: 'Inventario', icon: 'inventory' },
    { key: 'productos', label: 'Productos', icon: 'category' },
    { key: 'proveedores', label: 'Proveedores', icon: 'business' },
  ];

  const salesData = {
    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
    datasets: [
      {
        data: [12000, 19000, 15000, 25000, 22000, 30000],
        color: (opacity = 1) => `rgba(59, 130, 246, ${opacity})`,
        strokeWidth: 2,
      },
    ],
  };

  const inventoryData = {
    labels: ['Laptops', 'Mouses', 'Teclados', 'Monitores', 'USBs'],
    datasets: [
      {
        data: [15, 3, 8, 12, 45],
        color: (opacity = 1) => `rgba(16, 185, 129, ${opacity})`,
      },
    ],
  };

  const topProducts = [
    { name: 'Laptop Dell XPS', sales: 45, revenue: 58495.55 },
    { name: 'Monitor 24"', sales: 38, revenue: 11399.62 },
    { name: 'Teclado Mecánico', sales: 32, revenue: 2879.68 },
    { name: 'Mouse Logitech', sales: 28, revenue: 839.72 },
    { name: 'USB 32GB', sales: 25, revenue: 324.75 },
  ];

  const stats = {
    totalSales: 123456.78,
    totalProducts: 2847,
    lowStock: 23,
    totalRevenue: 45678.90,
    growth: 15.3,
  };

  const StatCard = ({ title, value, subtitle, color, icon, trend }) => (
    <Card style={styles.statCard}>
      <LinearGradient colors={color} style={styles.statGradient}>
        <View style={styles.statHeader}>
          <Icon name={icon} size={24} color="#ffffff" />
          {trend && (
            <View style={styles.trend}>
              <Icon 
                name={trend > 0 ? "trending-up" : "trending-down"} 
                size={16} 
                color="#ffffff" 
              />
              <Text style={styles.trendText}>{Math.abs(trend)}%</Text>
            </View>
          )}
        </View>
        <Text style={styles.statValue}>{value}</Text>
        <Text style={styles.statTitle}>{title}</Text>
        {subtitle && <Text style={styles.statSubtitle}>{subtitle}</Text>}
      </LinearGradient>
    </Card>
  );

  const renderSalesReport = () => (
    <View>
      <Card style={styles.chartCard}>
        <Text style={styles.chartTitle}>Ventas por Período</Text>
        <LineChart
          style={styles.chart}
          data={salesData}
          chartDescription={{ text: '' }}
          legend={{ enabled: false }}
          xAxis={{
            valueFormatter: salesData.labels,
            granularityEnabled: true,
            granularity: 1,
          }}
          yAxis={{
            left: { enabled: true },
            right: { enabled: false },
          }}
          viewport={{ width: width - 40, height: 200 }}
          drawGridBackground={false}
          borderColor="#3b82f6"
          drawBorders={false}
        />
      </Card>

      <Card style={styles.tableCard}>
        <Text style={styles.tableTitle}>Productos Más Vendidos</Text>
        <DataTable>
          <DataTable.Header>
            <DataTable.Title>Producto</DataTable.Title>
            <DataTable.Title numeric>Unidades</DataTable.Title>
            <DataTable.Title numeric>Ingresos</DataTable.Title>
          </DataTable.Header>

          {topProducts.map((product, index) => (
            <DataTable.Row key={index}>
              <DataTable.Cell>{product.name}</DataTable.Cell>
              <DataTable.Cell numeric>{product.sales}</DataTable.Cell>
              <DataTable.Cell numeric>${product.revenue.toFixed(2)}</DataTable.Cell>
            </DataTable.Row>
          ))}
        </DataTable>
      </Card>
    </View>
  );

  const renderInventoryReport = () => (
    <View>
      <Card style={styles.chartCard}>
        <Text style={styles.chartTitle}>Estado del Inventario</Text>
        <BarChart
          style={styles.chart}
          data={inventoryData}
          chartDescription={{ text: '' }}
          legend={{ enabled: false }}
          xAxis={{
            valueFormatter: inventoryData.labels,
            granularityEnabled: true,
            granularity: 1,
            labelRotationAngle: 45,
          }}
          yAxis={{
            left: { enabled: true },
            right: { enabled: false },
          }}
          viewport={{ width: width - 40, height: 200 }}
          drawGridBackground={false}
        />
      </Card>

      <Card style={styles.alertCard}>
        <View style={styles.alertHeader}>
          <Icon name="warning" size={24} color="#ef4444" />
          <Text style={styles.alertTitle}>Alertas de Stock</Text>
        </View>
        <Text style={styles.alertText}>Tienes 23 productos con stock bajo que necesitan reabastecimiento urgente.</Text>
        <Button
          mode="contained"
          onPress={() => navigation.navigate('Inventory')}
          style={styles.alertButton}
          color="#ef4444"
        >
          Ver Productos
        </Button>
      </Card>
    </View>
  );

  const renderContent = () => {
    switch (selectedReport) {
      case 'ventas':
        return renderSalesReport();
      case 'inventario':
        return renderInventoryReport();
      default:
        return renderSalesReport();
    }
  };

  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      {/* Header */}
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Reportes y Análisis</Text>
        <Text style={styles.headerSubtitle}>Visualiza el rendimiento de tu negocio</Text>
      </View>

      {/* Period Selector */}
      <View style={styles.periodContainer}>
        <Text style={styles.periodLabel}>Período:</Text>
        <ScrollView horizontal showsHorizontalScrollIndicator={false}>
          <View style={styles.periodButtons}>
            {periods.map((period) => (
              <TouchableOpacity
                key={period.key}
                style={[
                  styles.periodButton,
                  selectedPeriod === period.key && styles.selectedPeriodButton,
                ]}
                onPress={() => setSelectedPeriod(period.key)}
              >
                <Text
                  style={[
                    styles.periodButtonText,
                    selectedPeriod === period.key && styles.selectedPeriodButtonText,
                  ]}
                >
                  {period.label}
                </Text>
              </TouchableOpacity>
            ))}
          </View>
        </ScrollView>
      </View>

      {/* Stats Cards */}
      <View style={styles.statsContainer}>
        <StatCard
          title="Ventas Totales"
          value={`$${stats.totalSales.toLocaleString()}`}
          subtitle="Este período"
          color={['#3b82f6', '#2563eb']}
          icon="attach-money"
          trend={stats.growth}
        />
        <StatCard
          title="Total Productos"
          value={stats.totalProducts.toLocaleString()}
          color={['#10b981', '#059669']}
          icon="inventory"
        />
        <StatCard
          title="Stock Bajo"
          value={stats.lowStock}
          subtitle="Necesitan atención"
          color={['#ef4444', '#dc2626']}
          icon="warning"
        />
        <StatCard
          title="Valor Inventario"
          value={`$${stats.totalRevenue.toLocaleString()}`}
          color={['#f59e0b', '#d97706']}
          icon="account-balance"
        />
      </View>

      {/* Report Type Selector */}
      <View style={styles.reportTypeContainer}>
        <ScrollView horizontal showsHorizontalScrollIndicator={false}>
          <View style={styles.reportTypes}>
            {reportTypes.map((type) => (
              <TouchableOpacity
                key={type.key}
                style={[
                  styles.reportTypeButton,
                  selectedReport === type.key && styles.selectedReportTypeButton,
                ]}
                onPress={() => setSelectedReport(type.key)}
              >
                <Icon
                  name={type.icon}
                  size={20}
                  color={selectedReport === type.key ? '#ffffff' : '#64748b'}
                />
                <Text
                  style={[
                    styles.reportTypeButtonText,
                    selectedReport === type.key && styles.selectedReportTypeButtonText,
                  ]}
                >
                  {type.label}
                </Text>
              </TouchableOpacity>
            ))}
          </View>
        </ScrollView>
      </View>

      {/* Report Content */}
      <View style={styles.contentContainer}>
        {renderContent()}
      </View>

      {/* Export Button */}
      <View style={styles.exportContainer}>
        <TouchableOpacity style={styles.exportButton}>
          <LinearGradient colors={['#3b82f6', '#2563eb']} style={styles.exportGradient}>
            <Icon name="download" size={20} color="#ffffff" />
            <Text style={styles.exportButtonText}>Exportar Reporte</Text>
          </LinearGradient>
        </TouchableOpacity>
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
  periodContainer: {
    paddingHorizontal: 20,
    marginBottom: 20,
  },
  periodLabel: {
    fontSize: 16,
    fontWeight: '500',
    color: '#374151',
    marginBottom: 10,
  },
  periodButtons: {
    flexDirection: 'row',
    gap: 8,
  },
  periodButton: {
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 20,
    backgroundColor: '#ffffff',
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  selectedPeriodButton: {
    backgroundColor: '#3b82f6',
    borderColor: '#3b82f6',
  },
  periodButtonText: {
    fontSize: 14,
    color: '#64748b',
    fontWeight: '500',
  },
  selectedPeriodButtonText: {
    color: '#ffffff',
  },
  statsContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
    paddingHorizontal: 20,
    marginBottom: 20,
  },
  statCard: {
    width: (width - 50) / 2,
    marginBottom: 10,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
  },
  statGradient: {
    padding: 16,
    borderRadius: 8,
  },
  statHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  trend: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 10,
  },
  trendText: {
    fontSize: 12,
    color: '#ffffff',
    marginLeft: 2,
  },
  statValue: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#ffffff',
    marginBottom: 4,
  },
  statTitle: {
    fontSize: 12,
    color: 'rgba(255, 255, 255, 0.8)',
    marginBottom: 2,
  },
  statSubtitle: {
    fontSize: 10,
    color: 'rgba(255, 255, 255, 0.6)',
  },
  reportTypeContainer: {
    paddingHorizontal: 20,
    marginBottom: 20,
  },
  reportTypes: {
    flexDirection: 'row',
    gap: 8,
  },
  reportTypeButton: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderRadius: 12,
    backgroundColor: '#ffffff',
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  selectedReportTypeButton: {
    backgroundColor: '#3b82f6',
    borderColor: '#3b82f6',
  },
  reportTypeButtonText: {
    fontSize: 14,
    color: '#64748b',
    fontWeight: '500',
    marginLeft: 8,
  },
  selectedReportTypeButtonText: {
    color: '#ffffff',
  },
  contentContainer: {
    paddingHorizontal: 20,
    marginBottom: 20,
  },
  chartCard: {
    marginBottom: 16,
    padding: 16,
  },
  chartTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#1e293b',
    marginBottom: 16,
  },
  chart: {
    height: 200,
  },
  tableCard: {
    marginBottom: 16,
  },
  tableTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#1e293b',
    marginBottom: 16,
    paddingHorizontal: 16,
    paddingTop: 16,
  },
  alertCard: {
    padding: 16,
    backgroundColor: '#fef2f2',
    borderColor: '#fecaca',
    borderWidth: 1,
  },
  alertHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  alertTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#991b1b',
    marginLeft: 8,
  },
  alertText: {
    fontSize: 14,
    color: '#7f1d1d',
    marginBottom: 16,
    lineHeight: 20,
  },
  alertButton: {
    backgroundColor: '#ef4444',
  },
  exportContainer: {
    paddingHorizontal: 20,
    paddingBottom: 40,
  },
  exportButton: {
    borderRadius: 12,
    overflow: 'hidden',
  },
  exportGradient: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 16,
    borderRadius: 12,
  },
  exportButtonText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#ffffff',
    marginLeft: 8,
  },
});

export default ReportsScreen;
