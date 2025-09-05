<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <h1 class="text-3xl font-bold text-gray-900">Authentication Demo</h1>
                    <div class="flex space-x-4">
                        <button @click="getAuthStats()" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Auth Stats
                        </button>
                        <button @click="logout()" 
                                x-show="isLoggedIn"
                                class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div x-data="authenticationDemo()" class="space-y-8">
                <!-- Authentication Statistics -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Authentication Statistics</h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="font-medium text-blue-900">Total Users</h3>
                            <p class="text-blue-700" x-text="authStats.total_users || 'Loading...'"></p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="font-medium text-green-900">Active Users</h3>
                            <p class="text-green-700" x-text="authStats.active_users || 'Loading...'"></p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h3 class="font-medium text-purple-900">Verified Users</h3>
                            <p class="text-purple-700" x-text="authStats.verified_users || 'Loading...'"></p>
                        </div>
                        <div class="bg-orange-50 p-4 rounded-lg">
                            <h3 class="font-medium text-orange-900">Recent Logins</h3>
                            <p class="text-orange-700" x-text="authStats.recent_logins || 'Loading...'"></p>
                        </div>
                    </div>
                </div>

                <!-- Authentication Forms -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Login Form -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold mb-4">Login</h2>
                        <form @submit.prevent="login()" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input x-model="loginForm.email" 
                                       type="email" 
                                       required
                                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Password</label>
                                <input x-model="loginForm.password" 
                                       type="password" 
                                       required
                                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div class="flex items-center">
                                <input x-model="loginForm.remember" 
                                       type="checkbox" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label class="ml-2 block text-sm text-gray-900">Remember me</label>
                            </div>
                            <button type="submit" 
                                    :disabled="loading.login"
                                    class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50">
                                <span x-show="!loading.login">Login</span>
                                <span x-show="loading.login">Logging in...</span>
                            </button>
                        </form>
                        <div x-show="results.login" class="mt-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-2">Response Time: <span x-text="results.login.responseTime + 'ms'"></span></p>
                                <p class="text-sm text-gray-600">Status: <span x-text="results.login.success ? 'Success' : 'Failed'"></span></p>
                                <p x-show="results.login.message" class="text-sm text-gray-600">Message: <span x-text="results.login.message"></span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Register Form -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold mb-4">Register</h2>
                        <form @submit.prevent="register()" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Name</label>
                                <input x-model="registerForm.name" 
                                       type="text" 
                                       required
                                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input x-model="registerForm.email" 
                                       type="email" 
                                       required
                                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Password</label>
                                <input x-model="registerForm.password" 
                                       type="password" 
                                       required
                                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                <input x-model="registerForm.password_confirmation" 
                                       type="password" 
                                       required
                                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div class="flex items-center">
                                <input x-model="registerForm.terms" 
                                       type="checkbox" 
                                       required
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label class="ml-2 block text-sm text-gray-900">I accept the terms and conditions</label>
                            </div>
                            <button type="submit" 
                                    :disabled="loading.register"
                                    class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 disabled:opacity-50">
                                <span x-show="!loading.register">Register</span>
                                <span x-show="loading.register">Registering...</span>
                            </button>
                        </form>
                        <div x-show="results.register" class="mt-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-2">Response Time: <span x-text="results.register.responseTime + 'ms'"></span></p>
                                <p class="text-sm text-gray-600">Status: <span x-text="results.register.success ? 'Success' : 'Failed'"></span></p>
                                <p x-show="results.register.message" class="text-sm text-gray-600">Message: <span x-text="results.register.message"></span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current User Info -->
                <div x-show="isLoggedIn" class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Current User</h2>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600">Name: <span x-text="currentUser.name || 'Loading...'"></span></p>
                        <p class="text-sm text-gray-600">Email: <span x-text="currentUser.email || 'Loading...'"></span></p>
                        <p class="text-sm text-gray-600">Last Login: <span x-text="currentUser.last_login || 'Never'"></span></p>
                    </div>
                </div>

                <!-- Security Features -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Security Features</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="font-medium text-blue-900">Password Hashing</h3>
                            <p class="text-blue-700 text-sm">Passwords are hashed using bcrypt</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="font-medium text-green-900">CSRF Protection</h3>
                            <p class="text-green-700 text-sm">All forms are protected with CSRF tokens</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h3 class="font-medium text-purple-900">Session Security</h3>
                            <p class="text-purple-700 text-sm">Sessions are regenerated on login</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function authenticationDemo() {
            return {
                loading: {
                    login: false,
                    register: false
                },
                results: {
                    login: null,
                    register: null
                },
                loginForm: {
                    email: 'user@example.com',
                    password: 'password123',
                    remember: false
                },
                registerForm: {
                    name: 'John Doe',
                    email: 'john@example.com',
                    password: 'Password123!',
                    password_confirmation: 'Password123!',
                    terms: false
                },
                authStats: {},
                currentUser: {},
                isLoggedIn: false,

                async init() {
                    await this.getAuthStats();
                    await this.getCurrentUser();
                },

                async getAuthStats() {
                    try {
                        const response = await fetch('/auth/stats');
                        const data = await response.json();
                        this.authStats = data.data;
                    } catch (error) {
                        console.error('Error loading auth stats:', error);
                    }
                },

                async getCurrentUser() {
                    try {
                        const response = await fetch('/auth/me');
                        const data = await response.json();
                        if (data.success) {
                            this.currentUser = data.user;
                            this.isLoggedIn = true;
                        }
                    } catch (error) {
                        console.error('Error loading current user:', error);
                    }
                },

                async login() {
                    this.loading.login = true;
                    const startTime = performance.now();
                    
                    try {
                        const response = await fetch('/auth/login', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(this.loginForm)
                        });
                        const data = await response.json();
                        const endTime = performance.now();
                        
                        this.results.login = {
                            ...data,
                            responseTime: Math.round(endTime - startTime)
                        };
                        
                        if (data.success) {
                            this.isLoggedIn = true;
                            this.currentUser = data.user;
                        }
                    } catch (error) {
                        console.error('Error logging in:', error);
                    } finally {
                        this.loading.login = false;
                    }
                },

                async register() {
                    this.loading.register = true;
                    const startTime = performance.now();
                    
                    try {
                        const response = await fetch('/auth/register', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(this.registerForm)
                        });
                        const data = await response.json();
                        const endTime = performance.now();
                        
                        this.results.register = {
                            ...data,
                            responseTime: Math.round(endTime - startTime)
                        };
                        
                        if (data.success) {
                            this.isLoggedIn = true;
                            this.currentUser = data.user;
                        }
                    } catch (error) {
                        console.error('Error registering:', error);
                    } finally {
                        this.loading.register = false;
                    }
                },

                async logout() {
                    try {
                        const response = await fetch('/auth/logout', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        const data = await response.json();
                        
                        if (data.success) {
                            this.isLoggedIn = false;
                            this.currentUser = {};
                        }
                    } catch (error) {
                        console.error('Error logging out:', error);
                    }
                }
            }
        }
    </script>
</body>
</html>
