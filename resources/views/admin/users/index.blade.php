@extends(request()->ajax() ? 'layouts.ajax' : 'layouts.admin')

@section('title', 'Usuarios - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 text-dark">
    <h2 class="fw-bold m-0" style="color: #333;">Gestión de Usuarios</h2>
    <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#createUserModal">
        <i class="bi bi-person-plus-fill"></i> Nuevo Usuario
    </button>
</div>

<div class="card bg-white border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary">
                    <tr>
                        <th class="ps-4 border-0">USUARIO</th>
                        <th class="border-0">EMAIL</th>
                        <th class="border-0">ROL</th>
                        <th class="pe-4 border-0 text-end">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary fw-bold border" style="width: 40px; height: 40px;">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <span class="ms-3 fw-bold text-dark">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="text-secondary">{{ $user->email }}</td>
                        <td>
                            @if($user->role === 'admin')
                                <span class="badge bg-gold text-dark border-0 rounded-pill px-3">Administrador</span>
                            @else
                                <span class="badge bg-light text-secondary border rounded-pill px-3">Usuario</span>
                            @endif
                        </td>
                        <td class="pe-4 text-end">
                            <button class="btn btn-sm btn-light text-warning me-1" onclick="editUser({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->email }}', '{{ $user->role }}')">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            
                            @if(auth()->id() !== $user->id)
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="confirmDelete(event, '{{ addslashes($user->name) }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light text-danger">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('users.store') }}" method="POST" autocomplete="off">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">NOMBRE COMPLETO</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ej: Admin General">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">EMAIL</label>
                        <input type="email" name="email" class="form-control" required placeholder="correo@ejemplo.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">ROL</label>
                        <select name="role" class="form-select bg-light border-0 fw-bold" required>
                            <option value="user">Usuario Estándar</option>
                            <option value="admin">Administrador (Acceso Total)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">CONTRASEÑA</label>
                        <input type="password" name="password" id="create_password" class="form-control" required minlength="8" autocomplete="new-password">
                        <!-- Checklist Create -->
                        <div class="mt-2">
                            <ul class="list-unstyled mb-0 small ps-1" id="create-password-requirements">
                                <li data-req="length" class="text-muted"><i class="bi bi-circle me-1" style="font-size: 0.6rem;"></i> Mínimo 8 caracteres</li>
                                <li data-req="upper" class="text-muted"><i class="bi bi-circle me-1" style="font-size: 0.6rem;"></i> Una mayúscula</li>
                                <li data-req="lower" class="text-muted"><i class="bi bi-circle me-1" style="font-size: 0.6rem;"></i> Una minúscula</li>
                                <li data-req="number" class="text-muted"><i class="bi bi-circle me-1" style="font-size: 0.6rem;"></i> Un número</li>
                                <li data-req="special" class="text-muted"><i class="bi bi-circle me-1" style="font-size: 0.6rem;"></i> Un símbolo (@$!%*?&.)</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-gold px-4 fw-bold">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Editar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST" autocomplete="off">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">NOMBRE COMPLETO</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">EMAIL</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">ROL</label>
                        <select name="role" id="edit_role" class="form-select bg-light border-0 fw-bold" required>
                            <option value="user">Usuario Estándar</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">NUEVA CONTRASEÑA (Opcional)</label>
                        <input type="password" name="password" id="edit_password" class="form-control" placeholder="Dejar en blanco para mantener actual" minlength="8" autocomplete="new-password">
                        <!-- Checklist Edit -->
                        <div class="mt-2">
                             <small class="text-muted d-block mb-1">Requisitos (Solo si cambias contraseña):</small>
                            <ul class="list-unstyled mb-0 small ps-1" id="edit-password-requirements">
                                <li data-req="length" class="text-muted"><i class="bi bi-circle me-1" style="font-size: 0.6rem;"></i> Mínimo 8 caracteres</li>
                                <li data-req="upper" class="text-muted"><i class="bi bi-circle me-1" style="font-size: 0.6rem;"></i> Una mayúscula</li>
                                <li data-req="lower" class="text-muted"><i class="bi bi-circle me-1" style="font-size: 0.6rem;"></i> Una minúscula</li>
                                <li data-req="number" class="text-muted"><i class="bi bi-circle me-1" style="font-size: 0.6rem;"></i> Un número</li>
                                <li data-req="special" class="text-muted"><i class="bi bi-circle me-1" style="font-size: 0.6rem;"></i> Un símbolo (@$!%*?&.)</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-gold px-4 fw-bold">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let editModal;
    document.addEventListener('DOMContentLoaded', () => {
        editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
        
        window.editUser = function(id, name, email, role) {
            document.getElementById('editForm').action = `/users/${id}`;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role').value = role;
            document.getElementById('edit_password').value = ''; // Reset password field
             // Reset requirements visuals
             resetChecklist('edit-password-requirements');
            editModal.show();
        };

        // Initialize Password Validation
        setupPasswordChecklist('create_password', 'create-password-requirements');
        setupPasswordChecklist('edit_password', 'edit-password-requirements');
    });

    function confirmDelete(e, name) {
        e.preventDefault();
        const form = e.target;
        
        Swal.fire({
            title: '¿Eliminar usuario?',
            text: `Vas a eliminar a ${name}. Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#CBD5E1',
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }

    // Reusable Password Checklist Logic
    function setupPasswordChecklist(inputId, listId) {
        const input = document.getElementById(inputId);
        const list = document.getElementById(listId);
        if (!input || !list) return;

        input.addEventListener('input', function() {
             const val = input.value;
             if(val.length === 0 && inputId.includes('edit')) {
                 resetChecklist(listId); // If empty on edit, reset to gray
                 return;
             }
             updateChecklist(list, val);
        });
    }

    function updateChecklist(listElement, value) {
        const items = listElement.querySelectorAll('li');
        
        items.forEach(li => {
            const type = li.getAttribute('data-req');
            let isValid = false;

            if (type === 'length') isValid = value.length >= 8;
            if (type === 'upper') isValid = /[A-Z]/.test(value);
            if (type === 'lower') isValid = /[a-z]/.test(value);
            if (type === 'number') isValid = /[0-9]/.test(value);
            if (type === 'special') isValid = /[@$!%*?&.]/.test(value);

            const icon = li.querySelector('i');
            if (isValid) {
                li.classList.remove('text-muted');
                li.classList.add('text-success', 'text-decoration-line-through', 'fw-bold');
                icon.classList.remove('bi-circle');
                icon.classList.add('bi-check-circle-fill');
            } else {
                li.classList.add('text-muted');
                li.classList.remove('text-success', 'text-decoration-line-through', 'fw-bold');
                icon.classList.add('bi-circle');
                icon.classList.remove('bi-check-circle-fill');
            }
        });
    }

    function resetChecklist(listId) {
        const list = document.getElementById(listId);
        if(!list) return;
        const items = list.querySelectorAll('li');
        items.forEach(li => {
            li.classList.add('text-muted');
            li.classList.remove('text-success', 'text-decoration-line-through', 'fw-bold');
            const icon = li.querySelector('i');
            icon.classList.add('bi-circle');
            icon.classList.remove('bi-check-circle-fill');
        });
    }
</script>
@endpush
@endsection
