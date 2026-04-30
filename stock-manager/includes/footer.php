<?php if (isset($auth) && $auth->isLoggedIn() && basename($_SERVER['PHP_SELF']) != 'login.php'): ?>
            </div> <!-- Cierre del main content -->
        </div> <!-- Cierre del row -->
    </div> <!-- Cierre del container-fluid -->
<?php endif; ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>

<script>
    // Inicializar DataTables
    $(document).ready(function() {
        $('.datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            pageLength: 25,
            responsive: true
        });
    });
    
    // Confirmación para eliminar
    function confirmDelete(message = '¿Estás seguro de eliminar este registro?') {
        return confirm(message);
    }
</script>
</body>
</html>