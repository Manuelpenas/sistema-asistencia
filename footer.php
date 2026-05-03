    </div>
    <script>
        function confirmDelete(msg) {
            return confirm(msg || '¿Está seguro de eliminar este registro?');
        }
        function toggleAll(source) {
            checkboxes = document.getElementsByName('asistentes[]');
            for(var i=0; i<checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
        }
        function selectColaborador(dni, nombre) {
            document.getElementById('dni_seleccionado').value = dni;
            document.getElementById('nombre_seleccionado').textContent = nombre;
        }
    </script>
</body>
</html>
