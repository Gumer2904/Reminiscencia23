import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Dimensions,
  Animated,
} from 'react-native';
import { Card, Button, FAB, Portal, Modal } from 'react-native-paper';
import Icon from 'react-native-vector-icons/MaterialIcons';
import { LinearGradient } from 'react-native-linear-gradient';

const { width } = Dimensions.get('window');

const HomeScreen = ({ navigation }) => {
  const [stats, setStats] = useState({
    totalProducts: 2847,
    lowStock: 23,
    totalValue: 45678.90,
    todaySales: 1234.56,
  });
  const [recentProducts, setRecentProducts] = useState([
    { id: 1, name: 'Laptop Dell XPS', stock: 15, price: 1299.99 },
    { id: 2, name: 'Mouse Logitech', stock: 3, price: 29.99 },
    { id: 3, name: 'Teclado Mecánico', stock: 8, price: 89.99 },
  ]);

  const fadeAnim = new Animated.Value(0);
  const slideAnim = new Animated.Value(50);

  useEffect(() => {
    Animated.parallel([
      Animated.timing(fadeAnim, {
        toValue: 1,
        duration: 1000,
        useNativeDriver: true,
      }),
      Animated.timing(slideAnim, {
        toValue: 0,
        duration: 800,
        useNativeDriver: true,
      }),
    ]).start();
  }, []);

  const StatCard = ({ icon, title, value, color, onPress }) => (
    <TouchableOpacity onPress={onPress} activeOpacity={0.8}>
      <Animated.View
        style={[
          styles.statCard,
          {
            opacity: fadeAnim,
            transform: [{ translateY: slideAnim }],
            backgroundColor: color,
          },
        ]}
      >
        <View style={styles.statIcon}>
          <Icon name={icon} size={24} color="#ffffff" />
        </View>
        <Text style={styles.statValue}>{value}</Text>
        <Text style={styles.statTitle}>{title}</Text>
      </Animated.View>
    </TouchableOpacity>
  );

  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      {/* Header */}
      <LinearGradient
        colors={['#3b82f6', '#2563eb']}
        style={styles.header}
      >
        <View style={styles.headerContent}>
          <Text style={styles.headerTitle}>Stock Manager</Text>
          <Text style={styles.headerSubtitle}>Gestión Inteligente de Inventario</Text>
        </View>
      </LinearGradient>

      {/* Stats Grid */}
      <View style={styles.statsContainer}>
        <View style={styles.statsRow}>
          <StatCard
            icon="inventory"
            title="Total Productos"
            value={stats.totalProducts.toLocaleString()}
            color="#3b82f6"
            onPress={() => navigation.navigate('Inventory')}
          />
          <StatCard
            icon="warning"
            title="Stock Bajo"
            value={stats.lowStock}
            color="#ef4444"
            onPress={() => navigation.navigate('Inventory')}
          />
        </View>
        <View style={styles.statsRow}>
          <StatCard
            icon="attach-money"
            title="Valor Total"
            value={`$${stats.totalValue.toLocaleString()}`}
            color="#10b981"
            onPress={() => navigation.navigate('Reports')}
          />
          <StatCard
            icon="trending-up"
            title="Ventas Hoy"
            value={`$${stats.todaySales.toLocaleString()}`}
            color="#f59e0b"
            onPress={() => navigation.navigate('Reports')}
          />
        </View>
      </View>

      {/* Recent Products */}
      <View style={styles.section}>
        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>Productos Recientes</Text>
          <TouchableOpacity onPress={() => navigation.navigate('Inventory')}>
            <Text style={styles.seeAll}>Ver todos</Text>
          </TouchableOpacity>
        </View>
        
        {recentProducts.map((product, index) => (
          <Animated.View
            key={product.id}
            style={[
              styles.productCard,
              {
                opacity: fadeAnim,
                transform: [{ translateY: slideAnim }],
                delay: index * 100,
              },
            ]}
          >
            <View style={styles.productInfo}>
              <Text style={styles.productName}>{product.name}</Text>
              <Text style={styles.productPrice}>${product.price}</Text>
            </View>
            <View style={styles.stockInfo}>
              <Text style={[
                styles.stockText,
                product.stock <= 5 ? { color: '#ef4444' } : { color: '#10b981' }
              ]}>
                Stock: {product.stock}
              </Text>
              {product.stock <= 5 && (
                <Icon name="warning" size={16} color="#ef4444" />
              )}
            </View>
          </Animated.View>
        ))}
      </View>

      {/* Quick Actions */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Acciones Rápidas</Text>
        <View style={styles.actionsGrid}>
          <TouchableOpacity
            style={styles.actionButton}
            onPress={() => navigation.navigate('Add')}
          >
            <LinearGradient colors={['#3b82f6', '#2563eb']} style={styles.actionGradient}>
              <Icon name="add" size={24} color="#ffffff" />
              <Text style={styles.actionText}>Agregar Producto</Text>
            </LinearGradient>
          </TouchableOpacity>
          
          <TouchableOpacity
            style={styles.actionButton}
            onPress={() => navigation.navigate('Reports')}
          >
            <LinearGradient colors={['#10b981', '#059669']} style={styles.actionGradient}>
              <Icon name="bar-chart" size={24} color="#ffffff" />
              <Text style={styles.actionText}>Ver Reportes</Text>
            </LinearGradient>
          </TouchableOpacity>
        </View>
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
    paddingTop: 40,
    borderBottomLeftRadius: 20,
    borderBottomRightRadius: 20,
  },
  headerContent: {
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#ffffff',
    marginBottom: 5,
  },
  headerSubtitle: {
    fontSize: 16,
    color: '#e0e7ff',
  },
  statsContainer: {
    padding: 20,
  },
  statsRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 15,
  },
  statCard: {
    width: width * 0.42,
    padding: 20,
    borderRadius: 15,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 5,
  },
  statIcon: {
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 10,
  },
  statValue: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#ffffff',
    marginBottom: 5,
  },
  statTitle: {
    fontSize: 12,
    color: 'rgba(255, 255, 255, 0.8)',
    textAlign: 'center',
  },
  section: {
    padding: 20,
    paddingTop: 0,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 15,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#1e293b',
  },
  seeAll: {
    fontSize: 14,
    color: '#3b82f6',
    fontWeight: '500',
  },
  productCard: {
    backgroundColor: '#ffffff',
    padding: 15,
    borderRadius: 10,
    marginBottom: 10,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 2,
  },
  productInfo: {
    flex: 1,
  },
  productName: {
    fontSize: 16,
    fontWeight: '600',
    color: '#1e293b',
    marginBottom: 5,
  },
  productPrice: {
    fontSize: 14,
    color: '#64748b',
  },
  stockInfo: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  stockText: {
    fontSize: 14,
    fontWeight: '500',
    marginRight: 5,
  },
  actionsGrid: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  actionButton: {
    width: width * 0.42,
    borderRadius: 15,
    overflow: 'hidden',
  },
  actionGradient: {
    padding: 20,
    alignItems: 'center',
    borderRadius: 15,
  },
  actionText: {
    color: '#ffffff',
    fontSize: 14,
    fontWeight: '600',
    marginTop: 8,
  },
});

export default HomeScreen;
