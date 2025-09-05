@extends('layouts.app')

@section('title', 'Form Request Demo - Validazione e Autorizzazione')

@section('content')
<div x-data="formRequestDemo()" class="space-y-8">
    <!-- User Registration Form -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Registrazione Utente</h2>
        <form @submit.prevent="createUser()" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nome *</label>
                    <input type="text" x-model="newUser.name" required 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <div x-show="errors.name" class="text-red-500 text-sm mt-1" x-text="errors.name"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email *</label>
                    <input type="email" x-model="newUser.email" required 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <div x-show="errors.email" class="text-red-500 text-sm mt-1" x-text="errors.email"></div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Password *</label>
                    <input type="password" x-model="newUser.password" required 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <div x-show="errors.password" class="text-red-500 text-sm mt-1" x-text="errors.password"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Conferma Password *</label>
                    <input type="password" x-model="newUser.password_confirmation" required 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Ruolo *</label>
                    <select x-model="newUser.role" required 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleziona ruolo</option>
                        <option value="user">Utente</option>
                        <option value="moderator">Moderatore</option>
                        <option value="admin">Amministratore</option>
                    </select>
                    <div x-show="errors.role" class="text-red-500 text-sm mt-1" x-text="errors.role"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Telefono</label>
                    <input type="tel" x-model="newUser.phone" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <div x-show="errors.phone" class="text-red-500 text-sm mt-1" x-text="errors.phone"></div>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Data di Nascita</label>
                <input type="date" x-model="newUser.date_of_birth" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <div x-show="errors.date_of_birth" class="text-red-500 text-sm mt-1" x-text="errors.date_of_birth"></div>
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" x-model="newUser.terms_accepted" required 
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label class="ml-2 block text-sm text-gray-900">
                    Accetto i termini e condizioni *
                </label>
            </div>
            <div x-show="errors.terms_accepted" class="text-red-500 text-sm" x-text="errors.terms_accepted"></div>
            
            <button type="submit" :disabled="loading" 
                    class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 disabled:opacity-50">
                <span x-show="!loading">Crea Utente</span>
                <span x-show="loading">Creazione...</span>
            </button>
        </form>
    </div>

    <!-- User Update Form -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Aggiorna Utente</h2>
        <form @submit.prevent="updateUser()" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Seleziona Utente</label>
                <select x-model="selectedUser" @change="loadUserData()" required 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Seleziona utente da aggiornare</option>
                    <template x-for="user in users" :key="user.id">
                        <option :value="user.id" x-text="user.name + ' (' + user.email + ')'"></option>
                    </template>
                </select>
            </div>
            
            <div x-show="selectedUser" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nome</label>
                        <input type="text" x-model="updateUserData.name" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <div x-show="updateErrors.name" class="text-red-500 text-sm mt-1" x-text="updateErrors.name"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" x-model="updateUserData.email" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <div x-show="updateErrors.email" class="text-red-500 text-sm mt-1" x-text="updateErrors.email"></div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nuova Password</label>
                        <input type="password" x-model="updateUserData.password" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <div x-show="updateErrors.password" class="text-red-500 text-sm mt-1" x-text="updateErrors.password"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Conferma Password</label>
                        <input type="password" x-model="updateUserData.password_confirmation" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ruolo</label>
                        <select x-model="updateUserData.role" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="user">Utente</option>
                            <option value="moderator">Moderatore</option>
                            <option value="admin">Amministratore</option>
                        </select>
                        <div x-show="updateErrors.role" class="text-red-500 text-sm mt-1" x-text="updateErrors.role"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Telefono</label>
                        <input type="tel" x-model="updateUserData.phone" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <div x-show="updateErrors.phone" class="text-red-500 text-sm mt-1" x-text="updateErrors.phone"></div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Data di Nascita</label>
                    <input type="date" x-model="updateUserData.date_of_birth" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <div x-show="updateErrors.date_of_birth" class="text-red-500 text-sm mt-1" x-text="updateErrors.date_of_birth"></div>
                </div>
                
                <div x-show="updateUserData.password">
                    <label class="block text-sm font-medium text-gray-700">Password Attuale *</label>
                    <input type="password" x-model="updateUserData.current_password" required 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <div x-show="updateErrors.current_password" class="text-red-500 text-sm mt-1" x-text="updateErrors.current_password"></div>
                </div>
                
                <button type="submit" :disabled="loading" 
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:opacity-50">
                    <span x-show="!loading">Aggiorna Utente</span>
                    <span x-show="loading">Aggiornamento...</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Users List -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Utenti Registrati</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ruolo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telefono</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Azioni</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="user in users" :key="user.id">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="user.id"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="user.name"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="user.email"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="user.role_display_name"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="user.phone || '-'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <button @click="selectUserForUpdate(user)" 
                                        class="text-blue-600 hover:text-blue-900">Modifica</button>
                                <button @click="deleteUser(user.id)" 
                                        class="text-red-600 hover:text-red-900 ml-2">Elimina</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Messages -->
    <div x-show="message" x-transition class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded shadow-lg">
        <span x-text="message"></span>
    </div>

    <div x-show="error" x-transition class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded shadow-lg">
        <span x-text="error"></span>
    </div>
</div>
@endsection

@section('scripts')
<script>
function formRequestDemo() {
    return {
        loading: false,
        message: '',
        error: '',
        users: @json($users),
        errors: {},
        updateErrors: {},
        selectedUser: '',
        newUser: {
            name: '',
            email: '',
            password: '',
            password_confirmation: '',
            role: '',
            phone: '',
            date_of_birth: '',
            terms_accepted: false
        },
        updateUserData: {
            name: '',
            email: '',
            password: '',
            password_confirmation: '',
            role: '',
            phone: '',
            date_of_birth: '',
            current_password: ''
        },

        async createUser() {
            this.loading = true;
            this.clearMessages();
            this.errors = {};
            
            try {
                const response = await fetch('/users', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.newUser)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.message = data.message;
                    this.users.push(data.user);
                    this.newUser = {
                        name: '',
                        email: '',
                        password: '',
                        password_confirmation: '',
                        role: '',
                        phone: '',
                        date_of_birth: '',
                        terms_accepted: false
                    };
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        this.error = data.message;
                    }
                }
            } catch (error) {
                this.error = 'Errore nella creazione utente: ' + error.message;
            }
            
            this.loading = false;
        },

        async updateUser() {
            if (!this.selectedUser) return;
            
            this.loading = true;
            this.clearMessages();
            this.updateErrors = {};
            
            try {
                const response = await fetch(`/users/${this.selectedUser}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.updateUserData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.message = data.message;
                    const index = this.users.findIndex(u => u.id == this.selectedUser);
                    if (index !== -1) {
                        this.users[index] = data.user;
                    }
                } else {
                    if (data.errors) {
                        this.updateErrors = data.errors;
                    } else {
                        this.error = data.message;
                    }
                }
            } catch (error) {
                this.error = 'Errore nell\'aggiornamento utente: ' + error.message;
            }
            
            this.loading = false;
        },

        async deleteUser(userId) {
            if (!confirm('Sei sicuro di voler eliminare questo utente?')) return;
            
            this.loading = true;
            this.clearMessages();
            
            try {
                const response = await fetch(`/users/${userId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.message = data.message;
                    this.users = this.users.filter(u => u.id != userId);
                } else {
                    this.error = data.message;
                }
            } catch (error) {
                this.error = 'Errore nell\'eliminazione utente: ' + error.message;
            }
            
            this.loading = false;
        },

        loadUserData() {
            if (this.selectedUser) {
                const user = this.users.find(u => u.id == this.selectedUser);
                if (user) {
                    this.updateUserData = {
                        name: user.name,
                        email: user.email,
                        password: '',
                        password_confirmation: '',
                        role: user.role,
                        phone: user.phone || '',
                        date_of_birth: user.date_of_birth || '',
                        current_password: ''
                    };
                }
            }
        },

        selectUserForUpdate(user) {
            this.selectedUser = user.id;
            this.loadUserData();
        },

        clearMessages() {
            this.message = '';
            this.error = '';
        }
    }
}
</script>
@endsection
