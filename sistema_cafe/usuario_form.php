<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Gestionar Usuarios</h1>
    <form id="usuarioForm" class="mb-4">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" id="idusuario">
        <input type="hidden" id="idpersona" value="<?php echo isset($_GET['idpersona']) ? htmlspecialchars($_GET['idpersona']) : ''; ?>">
        <div class="mb-4">
            <label class="block text-sm font-medium">Nombre de Usuario</label>
            <input type="text" id="nombreu" name="nombreu" class="w-full p-2 border rounded" maxlength="50" required>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium">Contraseña</label>
            <input type="password" id="contrasena" name="contrasena" class="w-full p-2 border rounded" maxlength="50" required>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium">Perfil</label>
            <select id="idperfil" name="idperfil" class="w-full p-2 border rounded" required>
                <option value="">Seleccione un Perfil</option>
            </select>
        </div>
        <button type="submit" class="bg-green-600 text-white p-2 rounded hover:bg-green-700">Guardar</button>
        <button type="button" id="disable" class="bg-red-600 text-white p-2 rounded hover:bg-red-700">Inhabilitar</button>
    </form>
    <table class="w-full border">
        <thead>
            <tr class="bg-gray-200">
                <th class="p-2">ID</th>
                <th class="p-2">Usuario</th>
                <th class="p-2">Perfil</th>
                <th class="p-2">Estado</th>
                <th class="p-2">Acciones</th>
            </tr>
        </thead>
        <tbody id="usuarioTable"></tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        loadUsuarios();
        loadPerfiles();

        function loadPerfiles() {
            $.ajax({
                url: 'backend.php?action=getPerfiles',
                method: 'GET',
                success: function(response) {
                    $('#idperfil').html('<option value="">Seleccione un Perfil</option>');
                    response.forEach(perfil => {
                        if (perfil.estado === 'activo') {
                            $('#idperfil').append(`<option value="${perfil.idperfil}">${perfil.descripc}</option>`);
                        }
                    });
                }
            });
        }

        $('#usuarioForm').submit(function(e) {
            e.preventDefault();
            const idusuario = $('#idusuario').val();
            const idpersona = $('#idpersona').val();
            const nombreu = $('#nombreu').val().trim();
            const contrasena = $('#contrasena').val();
            const idperfil = $('#idperfil').val();
            const csrf_token = $('input[name="csrf_token"]').val();
            if (!nombreu || !contrasena || !idperfil) {
                alert('Por favor, complete los campos obligatorios');
                return;
            }
            $.ajax({
                url: 'backend.php?action=saveUsuario',
                method: 'POST',
                data: { idusuario, idpersona, nombreu, contrasena, idperfil, csrf_token },
                success: function(response) {
                    if (response.success) {
                        loadUsuarios();
                        $('#usuarioForm')[0].reset();
                        $('#idusuario').val('');
                        $('#idpersona').val('');
                    } else {
                        alert(response.message);
                    }
                }
            });
        });

        $('#disable').click(function() {
            const idusuario = $('#idusuario').val();
            const csrf_token = $('input[name="csrf_token"]').val();
            if (!idusuario) {
                alert('Seleccione un usuario para inhabilitar');
                return;
            }
            $.ajax({
                url: 'backend.php?action=disableUsuario',
                method: 'POST',
                data: { idusuario, csrf_token },
                success: function(response) {
                    if (response.success) {
                        loadUsuarios();
                        $('#usuarioForm')[0].reset();
                        $('#idusuario').val('');
                    } else {
                        alert(response.message);
                    }
                }
            });
        });

        function loadUsuarios() {
            $.ajax({
                url: 'backend.php?action=getUsuarios',
                method: 'GET',
                success: function(response) {
                    $('#usuarioTable').html('');
                    response.forEach(usuario => {
                        $('#usuarioTable').append(`
                            <tr>
                                <td class="p-2">${usuario.idusuario}</td>
                                <td class="p-2">${usuario.nombreu}</td>
                                <td class="p-2">${usuario.descripc}</td>
                                <td class="p-2">${usuario.estado}</td>
                                <td class="p-2">
                                    <button onclick="editUsuario(${usuario.idusuario}, ${usuario.idpersona}, '${usuario.nombreu}', ${usuario.idperfil})" class="bg-blue-500 text-white p-1 rounded">Editar</button>
                                </td>
                            </tr>
                        `);
                    });
                }
            });
        }

        window.editUsuario = function(idusuario, idpersona, nombreu, idperfil) {
            $('#idusuario').val(idusuario);
            $('#idpersona').val(idpersona);
            $('#nombreu').val(nombreu);
            $('#idperfil').val(idperfil);
            $('#contrasena').val('');
        };
    });
</script>