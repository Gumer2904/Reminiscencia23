import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  TextInput,
  Dimensions,
  RefreshControl,
} from 'react-native';
import { Card, FAB, Searchbar, Chip, Button } from 'react-native-paper';
import Icon from 'react-native-vector-icons/MaterialIcons';
import { LinearGradient } from 'react-native-linear-gradient';

const { width } = Dimensions.get('window');

const InventoryScreen = ({ navigation }) => {
  const [products, setProducts] = useState([
    { id: 1, name: 'Laptop Dell XPS', category: 'Electrónica', stock: 15, price: 1299.99, minStock: 5, sku: 'LP001' },
    { id: 2, name: 'Mouse Logitech', category: 'Accesorios', stock: 3, price: 29.99, minStock: 10, sku: 'MS002' },
    { id: 3, name: 'Teclado Mecánico', category: 'Accesorios', stock: 8, price: 89.99, minStock: 5, sku: 'KB003' },
    { id: 4, name: 'Monitor 24"', category: 'Electrónica', stock: 12, price: 299.99, minStock: 8, sku: 'MN004' },
    { id: 5, name: 'USB 32GB', category: 'Almacenamiento', stock: 45, price: 12.99, minStock: 20, sku: 'US005' },
  ]);
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('Todos');
  const [refreshing, setRefreshing] = useState(false);
  const [sortBy, setSortBy] = useState('name');

  const categories = ['Todos', 'Electrónica', 'Accesorios', 'Almacenamiento'];
  const sortOptions = [
    { key: 'name', label: 'Nombre' },
    { key: 'stock', label: 'Stock' },
    { key: 'price', label: 'Precio' },
  ];

  const onRefresh = React.useCallback(() => {
    setRefreshing(true);
    setTimeout(() => {
      setRefreshing(false);
    }, 2000);
  }, []);

  const filteredProducts = products
    .filter(product => {
      const matchesSearch = product.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                           product.sku.toLowerCase().includes(searchQuery.toLowerCase());
      const matchesCategory = selectedCategory === 'Todos' || product.category === selectedCategory;
      return matchesSearch && matchesCategory;
    })
    .sort((a, b) => {
      if (sortBy === 'name') return a.name.localeCompare(b.name);
      if (sortBy === 'stock') return b.stock - a.stock;
      if (sortBy === 'price') return b.price - a.price;
      return 0;
    });

  const renderProduct = ({ item, index }) => (
    <TouchableOpacity
      style={styles.productCard}
      onPress={() => navigation.navigate('ProductDetail', { product: item })}
      activeOpacity={0.8}
    >
      <View style={styles.productHeader}>
        <View style={styles.productInfo}>
          <Text style={styles.productName}>{item.name}</Text>
          <Text style={styles.productSku}>SKU: {item.sku}</Text>
        </View>
        <View style={[
          styles.stockBadge,
          item.stock <= item.minStock ? styles.lowStock : styles.normalStock
        ]}>
          <Text style={[
            styles.stockText,
            item.stock <= item.minStock ? styles.lowStockText : styles.normalStockText
          ]}>
            {item.stock} uds
          </Text>
        </View>
      </View>
      
      <View style={styles.productDetails}>
        <View style={styles.detailRow}>
          <Text style={styles.detailLabel}>Categoría:</Text>
          <Chip mode="outlined" textStyle={styles.categoryText}>
            {item.category}
          </Chip>
        </View>
        
        <View style={styles.detailRow}>
          <Text style={styles.detailLabel}>Precio:</Text>
          <Text style={styles.price}>${item.price.toFixed(2)}</Text>
        </View>
        
        <View style={styles.detailRow}>
          <Text style={styles.detailLabel}>Stock mínimo:</Text>
          <Text style={styles.minStock}>{item.minStock} uds</Text>
        </View>
      </View>
      
      {item.stock <= item.minStock && (
        <View style={styles.warningBar}>
          <Icon name="warning" size={16} color="#ef4444" />
          <Text style={styles.warningText}>Stock bajo - Necesita reabastecer</Text>
        </View>
      )}
    </TouchableOpacity>
  );

  return (
    <View style={styles.container}>
      {/* Search Bar */}
      <View style={styles.searchContainer}>
        <Searchbar
          placeholder="Buscar producto..."
          onChangeText={setSearchQuery}
          value={searchQuery}
          style={styles.searchBar}
          inputStyle={styles.searchInput}
        />
      </View>

      {/* Categories */}
      <View style={styles.categoriesContainer}>
        <FlatList
          data={categories}
          horizontal
          showsHorizontalScrollIndicator={false}
          keyExtractor={(item) => item}
          renderItem={({ item }) => (
            <TouchableOpacity
              style={[
                styles.categoryChip,
                selectedCategory === item && styles.selectedCategory
              ]}
              onPress={() => setSelectedCategory(item)}
            >
              <Text style={[
                styles.categoryChipText,
                selectedCategory === item && styles.selectedCategoryText
              ]}>
                {item}
              </Text>
            </TouchableOpacity>
          )}
        />
      </View>

      {/* Sort Options */}
      <View style={styles.sortContainer}>
        <Text style={styles.sortLabel}>Ordenar por:</Text>
        <View style={styles.sortButtons}>
          {sortOptions.map((option) => (
            <TouchableOpacity
              key={option.key}
              style={[
                styles.sortButton,
                sortBy === option.key && styles.selectedSortButton
              ]}
              onPress={() => setSortBy(option.key)}
            >
              <Text style={[
                styles.sortButtonText,
                sortBy === option.key && styles.selectedSortButtonText
              ]}>
                {option.label}
              </Text>
            </TouchableOpacity>
          ))}
        </View>
      </View>

      {/* Products List */}
      <FlatList
        data={filteredProducts}
        renderItem={renderProduct}
        keyExtractor={(item) => item.id.toString()}
        contentContainerStyle={styles.productsList}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <Icon name="inventory" size={64} color="#cbd5e1" />
            <Text style={styles.emptyText}>No se encontraron productos</Text>
            <Button
              mode="contained"
              onPress={() => navigation.navigate('Add')}
              style={styles.addButton}
            >
              Agregar Primer Producto
            </Button>
          </View>
        }
      />

      {/* FAB */}
      <FAB
        style={styles.fab}
        icon="plus"
        label="Agregar"
        onPress={() => navigation.navigate('Add')}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f8fafc',
  },
  searchContainer: {
    padding: 15,
    paddingBottom: 10,
  },
  searchBar: {
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
  },
  searchInput: {
    fontSize: 16,
  },
  categoriesContainer: {
    paddingHorizontal: 15,
    paddingBottom: 10,
  },
  categoryChip: {
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 20,
    backgroundColor: '#ffffff',
    marginRight: 8,
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  selectedCategory: {
    backgroundColor: '#3b82f6',
    borderColor: '#3b82f6',
  },
  categoryChipText: {
    fontSize: 14,
    color: '#64748b',
    fontWeight: '500',
  },
  selectedCategoryText: {
    color: '#ffffff',
  },
  sortContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 15,
    paddingBottom: 10,
  },
  sortLabel: {
    fontSize: 14,
    color: '#64748b',
    marginRight: 10,
  },
  sortButtons: {
    flexDirection: 'row',
  },
  sortButton: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 15,
    backgroundColor: '#ffffff',
    marginRight: 8,
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  selectedSortButton: {
    backgroundColor: '#3b82f6',
    borderColor: '#3b82f6',
  },
  sortButtonText: {
    fontSize: 12,
    color: '#64748b',
    fontWeight: '500',
  },
  selectedSortButtonText: {
    color: '#ffffff',
  },
  productsList: {
    paddingHorizontal: 15,
    paddingBottom: 80,
  },
  productCard: {
    backgroundColor: '#ffffff',
    borderRadius: 12,
    padding: 16,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 2,
  },
  productHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 12,
  },
  productInfo: {
    flex: 1,
  },
  productName: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#1e293b',
    marginBottom: 4,
  },
  productSku: {
    fontSize: 12,
    color: '#64748b',
  },
  stockBadge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
  },
  normalStock: {
    backgroundColor: '#dcfce7',
  },
  lowStock: {
    backgroundColor: '#fee2e2',
  },
  stockText: {
    fontSize: 12,
    fontWeight: '600',
  },
  normalStockText: {
    color: '#16a34a',
  },
  lowStockText: {
    color: '#dc2626',
  },
  productDetails: {
    gap: 8,
  },
  detailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  detailLabel: {
    fontSize: 14,
    color: '#64748b',
  },
  categoryText: {
    fontSize: 12,
  },
  price: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#1e293b',
  },
  minStock: {
    fontSize: 14,
    color: '#64748b',
  },
  warningBar: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#fee2e2',
    paddingHorizontal: 8,
    paddingVertical: 6,
    borderRadius: 6,
    marginTop: 8,
  },
  warningText: {
    fontSize: 12,
    color: '#dc2626',
    marginLeft: 6,
  },
  emptyContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 60,
  },
  emptyText: {
    fontSize: 16,
    color: '#64748b',
    marginTop: 16,
    marginBottom: 24,
  },
  addButton: {
    backgroundColor: '#3b82f6',
  },
  fab: {
    position: 'absolute',
    margin: 16,
    right: 0,
    bottom: 0,
    backgroundColor: '#3b82f6',
  },
});

export default InventoryScreen;
