<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Gestionar Perfiles</h1>
    <form id="perfilForm" class="mb-4">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" id="idperfil">
        <div class="mb-4">
            <label class="block text-sm font-medium">Descripción</label>
            <input type="text" id="descripc" name="descripc" class="w-full p-2 border rounded" maxlength="255" required>
        </div>
        <button type="submit" class="bg-green-600 text-white p-2 rounded hover:bg-green-700">Guardar</button>
        <button type="button" id="disable" class="bg-red-600 text-white p-2 rounded hover:bg-red-700">Inhabilitar</button>
    </form>
    <table class="w-full border">
        <thead>
            <tr class="bg-gray-200">
                <th class="p-2">ID</th>
                <th class="p-2">Descripción</th>
                <th class="p-2">Estado</th>
                <th class="p-2">Acciones</th>
            </tr>
        </thead>
        <tbody id="perfilTable"></tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        loadPerfiles();

        $('#perfilForm').submit(function(e) {
            e.preventDefault();
            const idperfil = $('#idperfil').val();
            const descripc = $('#descripc').val().trim();
            const csrf_token = $('input[name="csrf_token"]').val();
            if (!descripc) {
                alert('La descripción es obligatoria');
                return;
            }
            $.ajax({
                url: 'backend.php?action=savePerfil',
                method: 'POST',
                data: { idperfil, descripc, csrf_token },
                success: function(response) {
                    if (response.success) {
                        loadPerfiles();
                        $('#perfilForm')[0].reset();
                        $('#idperfil').val('');
                    } else {
                        alert(response.message);
                    }
                }
            });
        });

        $('#disable').click(function() {
            const idperfil = $('#idperfil').val();
            const csrf_token = $('input[name="csrf_token"]').val();
            if (!idperfil) {
                alert('Seleccione un perfil para inhabilitar');
                return;
            }
            $.ajax({
                url: 'backend.php?action=disablePerfil',
                method: 'POST',
                data: { idperfil, csrf_token },
                success: function(response) {
                    if (response.success) {
                        loadPerfiles();
                        $('#perfilForm')[0].reset();
                        $('#idperfil').val('');
                    } else {
                        alert(response.message);
                    }
                }
            });
        });

        function loadPerfiles() {
            $.ajax({
                url: 'backend.php?action=getPerfiles',
                method: 'GET',
                success: function(response) {
                    $('#perfilTable').html('');
                    response.forEach(perfil => {
                        $('#perfilTable').append(`
                            <tr>
                                <td class="p-2">${perfil.idperfil}</td>
                                <td class="p-2">${perfil.descripc}</td>
                                <td class="p-2">${perfil.estado}</td>
                                <td class="p-2">
                                    <button onclick="editPerfil(${perfil.idperfil}, '${perfil.descripc}')" class="bg-blue-500 text-white p-1 rounded">Editar</button>
                                </td>
                            </tr>
                        `);
                    });
                }
            });
        }

        window.editPerfil = function(idperfil, descripc) {
            $('#idperfil').val(idperfil);
            $('#descripc').val(descripc);
        };
    });
</script>