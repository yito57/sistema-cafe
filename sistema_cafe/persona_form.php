<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Gestionar Personas</h1>
    <form id="personaForm" class="mb-4">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" id="idpersona">
        <div class="mb-4">
            <label class="block text-sm font-medium">Primer Nombre</label>
            <input type="text" id="nom1" name="nom1" class="w-full p-2 border rounded" maxlength="50" required>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium">Segundo Nombre</label>
            <input type="text" id="nom2" name="nom2" class="w-full p-2 border rounded" maxlength="50">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium">Primer Apellido</label>
            <input type="text" id="apell1" name="apell1" class="w-full p-2 border rounded" maxlength="50" required>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium">Segundo Apellido</label>
            <input type="text" id="apell2" name="apell2" class="w-full p-2 border rounded" maxlength="50">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium">Dirección</label>
            <input type="text" id="direccion" name="direccion" class="w-full p-2 border rounded" maxlength="255">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium">Teléfono Fijo</label>
            <input type="text" id="tele" name="tele" class="w-full p-2 border rounded" maxlength="20">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium">Teléfono Móvil</label>
            <input type="text" id="movil" name="movil" class="w-full p-2 border rounded" maxlength="20">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium">Correo Electrónico</label>
            <input type="email" id="correo" name="correo" class="w-full p-2 border rounded" maxlength="100" required>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium">Fecha de Nacimiento</label>
            <input type="date" id="fecha_nac" name="fecha_nac" class="w-full p-2 border rounded">
        </div>
        <button type="submit" class="bg-green-600 text-white p-2 rounded hover:bg-green-700">Guardar</button>
        <button type="button" id="disable" class="bg-red-600 text-white p-2 rounded hover:bg-red-700">Inhabilitar</button>
        <button type="button" id="addUsuario" class="bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Agregar Usuario</button>
    </form>
    <table class="w-full border">
        <thead>
            <tr class="bg-gray-200">
                <th class="p-2">ID</th>
                <th class="p-2">Primer Nombre</th>
                <th class="p-2">Primer Apellido</th>
                <th class="p-2">Correo</th>
                <th class="p-2">Estado</th>
                <th class="p-2">Acciones</th>
            </tr>
        </thead>
        <tbody id="personaTable"></tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        loadPersonas();

        $('#personaForm').submit(function(e) {
            e.preventDefault();
            const idpersona = $('#idpersona').val();
            const nom1 = $('#nom1').val().trim();
            const nom2 = $('#nom2').val().trim();
            const apell1 = $('#apell1').val().trim();
            const apell2 = $('#apell2').val().trim();
            const direccion = $('#direccion').val().trim();
            const tele = $('#tele').val().trim();
            const movil = $('#movil').val().trim();
            const correo = $('#correo').val().trim();
            const fecha_nac = $('#fecha_nac').val();
            const csrf_token = $('input[name="csrf_token"]').val();
            if (!nom1 || !apell1 || !correo) {
                alert('Por favor, complete los campos obligatorios');
                return;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
                alert('Por favor, ingrese un correo válido');
                return;
            }
            $.ajax({
                url: 'backend.php?action=savePersona',
                method: 'POST',
                data: { idpersona, nom1, nom2, apell1, apell2, direccion, tele, movil, correo, fecha_nac, csrf_token },
                success: function(response) {
                    if (response.success) {
                        loadPersonas();
                        $('#personaForm')[0].reset();
                        $('#idpersona').val('');
                    } else {
                        alert(response.message);
                    }
                }
            });
        });

        $('#disable').click(function() {
            const idpersona = $('#idpersona').val();
            const csrf_token = $('input[name="csrf_token"]').val();
            if (!idpersona) {
                alert('Seleccione una persona para inhabilitar');
                return;
            }
            $.ajax({
                url: 'backend.php?action=disablePersona',
                method: 'POST',
                data: { idpersona, csrf_token },
                success: function(response) {
                    if (response.success) {
                        loadPersonas();
                        $('#personaForm')[0].reset();
                        $('#idpersona').val('');
                    } else {
                        alert(response.message);
                    }
                }
            });
        });

        $('#addUsuario').click(function() {
            const idpersona = $('#idpersona').val();
            if (!idpersona) {
                alert('Seleccione una persona para agregar un usuario');
                return;
            }
            window.location.href = `dashboard.php?section=usuario&idpersona=${idpersona}`;
        });

        function loadPersonas() {
            $.ajax({
                url: 'backend.php?action=getPersonas',
                method: 'GET',
                success: function(response) {
                    $('#personaTable').html('');
                    response.forEach(persona => {
                        $('#personaTable').append(`
                            <tr>
                                <td class="p-2">${persona.idpersona}</td>
                                <td class="p-2">${persona.nom1}</td>
                                <td class="p-2">${persona.apell1}</td>
                                <td class="p-2">${persona.correo}</td>
                                <td class="p-2">${persona.estado}</td>
                                <td class="p-2">
                                    <button onclick="editPersona(${persona.idpersona}, '${persona.nom1}', '${persona.nom2 || ''}', '${persona.apell1}', '${persona.apell2 || ''}', '${persona.direccion || ''}', '${persona.tele || ''}', '${persona.movil || ''}', '${persona.correo}', '${persona.fecha_nac || ''}')" class="bg-blue-500 text-white p-1 rounded">Editar</button>
                                </td>
                            </tr>
                        `);
                    });
                }
            });
        }

        window.editPersona = function(idpersona, nom1, nom2, apell1, apell2, direccion, tele, movil, correo, fecha_nac) {
            $('#idpersona').val(idpersona);
            $('#nom1').val(nom1);
            $('#nom2').val(nom2);
            $('#apell1').val(apell1);
            $('#apell2').val(apell2);
            $('#direccion').val(direccion);
            $('#tele').val(tele);
            $('#movil').val(movil);
            $('#correo').val(correo);
            $('#fecha_nac').val(fecha_nac);
        };
    });
</script>