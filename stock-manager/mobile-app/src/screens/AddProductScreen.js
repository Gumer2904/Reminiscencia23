import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Alert,
  KeyboardAvoidingView,
  Platform,
} from 'react-native';
import { TextInput, Button, Card, Chip, Menu, Divider } from 'react-native-paper';
import Icon from 'react-native-vector-icons/MaterialIcons';
import { launchImageLibrary, launchCamera } from 'react-native-image-picker';
import { LinearGradient } from 'react-native-linear-gradient';

const AddProductScreen = ({ navigation }) => {
  const [product, setProduct] = useState({
    name: '',
    sku: '',
    category: '',
    description: '',
    price: '',
    cost: '',
    stock: '',
    minStock: '',
    supplier: '',
    location: '',
    barcode: '',
    image: null,
  });

  const [categoryMenuVisible, setCategoryMenuVisible] = useState(false);
  const [supplierMenuVisible, setSupplierMenuVisible] = useState(false);
  const [loading, setLoading] = useState(false);

  const categories = ['Electrónica', 'Accesorios', 'Almacenamiento', 'Oficina', 'Otros'];
  const suppliers = ['Tech Supplier', 'Global Components', 'Local Distributor', 'Direct Import'];

  const handleImagePicker = (useCamera) => {
    const options = {
      mediaType: 'photo',
      quality: 0.8,
      maxWidth: 800,
      maxHeight: 600,
    };

    const picker = useCamera ? launchCamera : launchImageLibrary;
    
    picker(options, (response) => {
      if (response.assets && response.assets[0]) {
        setProduct({ ...product, image: response.assets[0] });
      }
    });
  };

  const handleSave = async () => {
    if (!product.name || !product.sku || !product.price || !product.stock) {
      Alert.alert('Error', 'Por favor completa los campos obligatorios');
      return;
    }

    setLoading(true);
    
    try {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 1500));
      
      Alert.alert(
        'Éxito',
        'Producto agregado correctamente',
        [
          { text: 'OK', onPress: () => navigation.goBack() }
        ]
      );
    } catch (error) {
      Alert.alert('Error', 'No se pudo agregar el producto');
    } finally {
      setLoading(false);
    }
  };

  const InputField = ({ label, value, onChangeText, keyboardType = 'default', multiline = false, required = false }) => (
    <View style={styles.inputContainer}>
      <Text style={styles.inputLabel}>
        {label} {required && <Text style={styles.required}>*</Text>}
      </Text>
      <TextInput
        mode="outlined"
        value={value}
        onChangeText={onChangeText}
        keyboardType={keyboardType}
        multiline={multiline}
        style={styles.input}
        outlineColor="#e2e8f0"
        activeOutlineColor="#3b82f6"
      />
    </View>
  );

  return (
    <KeyboardAvoidingView 
      style={{ flex: 1 }} 
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
    >
      <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
        {/* Image Section */}
        <Card style={styles.imageCard}>
          <View style={styles.imageContainer}>
            {product.image ? (
              <View style={styles.imagePreview}>
                <Text style={styles.imageName}>{product.image.fileName}</Text>
                <TouchableOpacity
                  style={styles.changeImageButton}
                  onPress={() => handleImagePicker(false)}
                >
                  <Icon name="edit" size={16} color="#3b82f6" />
                  <Text style={styles.changeImageText}>Cambiar</Text>
                </TouchableOpacity>
              </View>
            ) : (
              <View style={styles.imagePlaceholder}>
                <Icon name="image" size={48} color="#cbd5e1" />
                <Text style={styles.placeholderText}>Sin imagen</Text>
                <Text style={styles.placeholderSubtext}>Agrega una foto del producto</Text>
              </View>
            )}
          </View>
          
          <View style={styles.imageButtons}>
            <TouchableOpacity
              style={styles.imageButton}
              onPress={() => handleImagePicker(false)}
            >
              <Icon name="photo-library" size={20} color="#3b82f6" />
              <Text style={styles.imageButtonText}>Galería</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={styles.imageButton}
              onPress={() => handleImagePicker(true)}
            >
              <Icon name="camera-alt" size={20} color="#3b82f6" />
              <Text style={styles.imageButtonText}>Cámara</Text>
            </TouchableOpacity>
          </View>
        </Card>

        {/* Basic Information */}
        <Card style={styles.card}>
          <Text style={styles.cardTitle}>Información Básica</Text>
          
          <InputField
            label="Nombre del Producto"
            value={product.name}
            onChangeText={(text) => setProduct({ ...product, name: text })}
            required
          />
          
          <InputField
            label="SKU"
            value={product.sku}
            onChangeText={(text) => setProduct({ ...product, sku: text.toUpperCase() })}
            required
          />
          
          <View style={styles.inputContainer}>
            <Text style={styles.inputLabel}>
              Categoría <Text style={styles.required}>*</Text>
            </Text>
            <Menu
              visible={categoryMenuVisible}
              onDismiss={() => setCategoryMenuVisible(false)}
              anchor={
                <TouchableOpacity
                  style={styles.menuButton}
                  onPress={() => setCategoryMenuVisible(true)}
                >
                  <Text style={product.category ? styles.menuText : styles.menuPlaceholder}>
                    {product.category || 'Selecciona una categoría'}
                  </Text>
                  <Icon name="arrow-drop-down" size={24} color="#64748b" />
                </TouchableOpacity>
              }
            >
              {categories.map((category) => (
                <Menu.Item
                  key={category}
                  onPress={() => {
                    setProduct({ ...product, category });
                    setCategoryMenuVisible(false);
                  }}
                  title={category}
                />
              ))}
            </Menu>
          </View>

          <InputField
            label="Descripción"
            value={product.description}
            onChangeText={(text) => setProduct({ ...product, description: text })}
            multiline
            numberOfLines={3}
          />
        </Card>

        {/* Pricing */}
        <Card style={styles.card}>
          <Text style={styles.cardTitle}>Precios</Text>
          
          <InputField
            label="Precio de Venta"
            value={product.price}
            onChangeText={(text) => setProduct({ ...product, price: text })}
            keyboardType="numeric"
            required
          />
          
          <InputField
            label="Costo"
            value={product.cost}
            onChangeText={(text) => setProduct({ ...product, cost: text })}
            keyboardType="numeric"
          />
        </Card>

        {/* Inventory */}
        <Card style={styles.card}>
          <Text style={styles.cardTitle}>Inventario</Text>
          
          <InputField
            label="Stock Actual"
            value={product.stock}
            onChangeText={(text) => setProduct({ ...product, stock: text })}
            keyboardType="numeric"
            required
          />
          
          <InputField
            label="Stock Mínimo"
            value={product.minStock}
            onChangeText={(text) => setProduct({ ...product, minStock: text })}
            keyboardType="numeric"
          />
          
          <InputField
            label="Ubicación"
            value={product.location}
            onChangeText={(text) => setProduct({ ...product, location: text })}
            placeholder="Ej: Almacén A, Estantería 3"
          />
        </Card>

        {/* Supplier */}
        <Card style={styles.card}>
          <Text style={styles.cardTitle}>Proveedor</Text>
          
          <View style={styles.inputContainer}>
            <Text style={styles.inputLabel}>Proveedor</Text>
            <Menu
              visible={supplierMenuVisible}
              onDismiss={() => setSupplierMenuVisible(false)}
              anchor={
                <TouchableOpacity
                  style={styles.menuButton}
                  onPress={() => setSupplierMenuVisible(true)}
                >
                  <Text style={product.supplier ? styles.menuText : styles.menuPlaceholder}>
                    {product.supplier || 'Selecciona un proveedor'}
                  </Text>
                  <Icon name="arrow-drop-down" size={24} color="#64748b" />
                </TouchableOpacity>
              }
            >
              {suppliers.map((supplier) => (
                <Menu.Item
                  key={supplier}
                  onPress={() => {
                    setProduct({ ...product, supplier });
                    setSupplierMenuVisible(false);
                  }}
                  title={supplier}
                />
              ))}
            </Menu>
          </View>

          <InputField
            label="Código de Barras"
            value={product.barcode}
            onChangeText={(text) => setProduct({ ...product, barcode: text })}
            placeholder="Ej: 1234567890123"
          />
        </Card>

        {/* Save Button */}
        <View style={styles.saveContainer}>
          <LinearGradient
            colors={['#3b82f6', '#2563eb']}
            style={styles.saveButton}
          >
            <TouchableOpacity onPress={handleSave} disabled={loading}>
              <View style={styles.saveButtonContent}>
                {loading ? (
                  <Text style={styles.saveButtonText}>Guardando...</Text>
                ) : (
                  <>
                    <Icon name="save" size={20} color="#ffffff" />
                    <Text style={styles.saveButtonText}>Guardar Producto</Text>
                  </>
                )}
              </View>
            </TouchableOpacity>
          </LinearGradient>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f8fafc',
  },
  imageCard: {
    margin: 15,
    marginBottom: 10,
  },
  imageContainer: {
    padding: 20,
    alignItems: 'center',
  },
  imagePreview: {
    alignItems: 'center',
  },
  imageName: {
    fontSize: 14,
    color: '#64748b',
    marginBottom: 8,
  },
  changeImageButton: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  changeImageText: {
    fontSize: 14,
    color: '#3b82f6',
    marginLeft: 4,
  },
  imagePlaceholder: {
    alignItems: 'center',
  },
  placeholderText: {
    fontSize: 16,
    color: '#64748b',
    marginTop: 8,
  },
  placeholderSubtext: {
    fontSize: 12,
    color: '#94a3b8',
    marginTop: 4,
  },
  imageButtons: {
    flexDirection: 'row',
    borderTopWidth: 1,
    borderTopColor: '#e2e8f0',
  },
  imageButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 12,
    borderRightWidth: 1,
    borderRightColor: '#e2e8f0',
  },
  imageButtonText: {
    fontSize: 14,
    color: '#3b82f6',
    marginLeft: 8,
  },
  card: {
    margin: 15,
    marginBottom: 10,
    padding: 20,
  },
  cardTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#1e293b',
    marginBottom: 16,
  },
  inputContainer: {
    marginBottom: 16,
  },
  inputLabel: {
    fontSize: 14,
    fontWeight: '500',
    color: '#374151',
    marginBottom: 8,
  },
  required: {
    color: '#ef4444',
  },
  input: {
    backgroundColor: '#ffffff',
  },
  menuButton: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#e2e8f0',
    borderRadius: 4,
    paddingHorizontal: 12,
    paddingVertical: 12,
    backgroundColor: '#ffffff',
  },
  menuText: {
    fontSize: 16,
    color: '#1e293b',
  },
  menuPlaceholder: {
    fontSize: 16,
    color: '#94a3b8',
  },
  saveContainer: {
    padding: 20,
    paddingBottom: 40,
  },
  saveButton: {
    borderRadius: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 5,
  },
  saveButtonContent: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 16,
  },
  saveButtonText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#ffffff',
    marginLeft: 8,
  },
});

export default AddProductScreen;
