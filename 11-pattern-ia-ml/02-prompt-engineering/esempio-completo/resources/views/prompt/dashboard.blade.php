<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prompt Engineering Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <h1 class="text-2xl font-bold text-gray-900">Prompt Engineering Dashboard</h1>
                    <div class="flex space-x-4">
                        <button onclick="refreshData()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Aggiorna
                        </button>
                        <a href="/prompt/editor" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            Crea Template
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Analytics Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Generazioni Totali</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $analytics['total_generations'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Successo</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $analytics['success_rate'] ?? 0 }}%</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Qualità Media</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $analytics['average_quality'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Costo Totale</p>
                            <p class="text-2xl font-semibold text-gray-900">${{ $analytics['total_cost'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Template Library -->
            <div class="bg-white rounded-lg shadow mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Template Disponibili</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($templates as $template)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $template['display_name'] }}</h3>
                            <p class="text-sm text-gray-600 mb-4">{{ $template['description'] }}</p>
                            <div class="flex justify-between items-center text-sm text-gray-500">
                                <span>{{ count($template['variables']) }} variabili</span>
                                <span>${{ $template['cost_estimate'] }}</span>
                            </div>
                            <div class="mt-4">
                                <button onclick="testTemplate('{{ $template['name'] }}')" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                    Testa Template
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Test Interface -->
            <div class="bg-white rounded-lg shadow mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Test Template</h2>
                </div>
                <div class="p-6">
                    <form id="testForm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Template</label>
                                <select id="templateSelect" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Seleziona un template</option>
                                    @foreach($templates as $template)
                                    <option value="{{ $template['name'] }}">{{ $template['display_name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Iterazioni</label>
                                <input type="number" id="iterations" value="5" min="1" max="20" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div id="variablesContainer" class="mt-6 hidden">
                            <h3 class="text-md font-medium text-gray-900 mb-4">Variabili</h3>
                            <div id="variablesInputs"></div>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                                Esegui Test
                            </button>
                        </div>
                    </form>
                    
                    <div id="testResults" class="mt-6 hidden">
                        <h3 class="text-md font-medium text-gray-900 mb-4">Risultati Test</h3>
                        <div id="testContent"></div>
                    </div>
                </div>
            </div>

            <!-- A/B Testing -->
            <div class="bg-white rounded-lg shadow mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">A/B Testing</h2>
                </div>
                <div class="p-6">
                    <form id="abTestForm">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Template A</label>
                                <select id="templateA" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Seleziona Template A</option>
                                    @foreach($templates as $template)
                                    <option value="{{ $template['name'] }}">{{ $template['display_name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Template B</label>
                                <select id="templateB" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Seleziona Template B</option>
                                    @foreach($templates as $template)
                                    <option value="{{ $template['name'] }}">{{ $template['display_name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Iterazioni</label>
                                <input type="number" id="abIterations" value="10" min="5" max="50" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div id="abVariablesContainer" class="mt-6 hidden">
                            <h3 class="text-md font-medium text-gray-900 mb-4">Variabili</h3>
                            <div id="abVariablesInputs"></div>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">
                                Esegui A/B Test
                            </button>
                        </div>
                    </form>
                    
                    <div id="abTestResults" class="mt-6 hidden">
                        <h3 class="text-md font-medium text-gray-900 mb-4">Risultati A/B Test</h3>
                        <div id="abTestContent"></div>
                    </div>
                </div>
            </div>

            <!-- Recent Tests -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Test Recenti</h2>
                </div>
                <div class="p-6">
                    @if(count($recentTests) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Template</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Successo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qualità</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($recentTests as $test)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ substr($test['test_id'], 0, 8) }}...</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $test['template_name'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $test['success_rate'] }}%</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $test['average_quality'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($test['created_at'])->format('d/m/Y H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-gray-500 text-center py-8">Nessun test recente</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        // Template selection change
        document.getElementById('templateSelect').addEventListener('change', function() {
            const templateName = this.value;
            if (templateName) {
                loadTemplateVariables(templateName, 'variablesInputs', 'variablesContainer');
            } else {
                document.getElementById('variablesContainer').classList.add('hidden');
            }
        });

        // A/B Test template selection
        document.getElementById('templateA').addEventListener('change', function() {
            const templateName = this.value;
            if (templateName) {
                loadTemplateVariables(templateName, 'abVariablesInputs', 'abVariablesContainer');
            }
        });

        document.getElementById('templateB').addEventListener('change', function() {
            const templateName = this.value;
            if (templateName) {
                loadTemplateVariables(templateName, 'abVariablesInputs', 'abVariablesContainer');
            }
        });

        // Load template variables
        async function loadTemplateVariables(templateName, containerId, containerClass) {
            try {
                const response = await fetch(`/prompt/api/template/${templateName}`);
                const data = await response.json();
                
                if (data.success) {
                    const variables = data.data.variables || [];
                    const container = document.getElementById(containerId);
                    
                    container.innerHTML = '';
                    
                    variables.forEach(variable => {
                        const div = document.createElement('div');
                        div.className = 'mb-4';
                        div.innerHTML = `
                            <label class="block text-sm font-medium text-gray-700 mb-2">${variable}</label>
                            <input type="text" name="${variable}" placeholder="Inserisci ${variable}" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        `;
                        container.appendChild(div);
                    });
                    
                    document.getElementById(containerClass).classList.remove('hidden');
                }
            } catch (error) {
                console.error('Errore nel caricamento delle variabili:', error);
            }
        }

        // Test form submission
        document.getElementById('testForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const variables = {};
            
            // Collect variables
            const variableInputs = document.querySelectorAll('#variablesInputs input');
            variableInputs.forEach(input => {
                variables[input.name] = input.value;
            });
            
            const testData = {
                template: document.getElementById('templateSelect').value,
                variables: variables,
                iterations: parseInt(document.getElementById('iterations').value)
            };
            
            try {
                const response = await fetch('/prompt/api/test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(testData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    displayTestResults(data.data);
                } else {
                    alert('Errore: ' + data.error);
                }
            } catch (error) {
                alert('Errore di rete: ' + error.message);
            }
        });

        // A/B Test form submission
        document.getElementById('abTestForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const variables = {};
            
            // Collect variables
            const variableInputs = document.querySelectorAll('#abVariablesInputs input');
            variableInputs.forEach(input => {
                variables[input.name] = input.value;
            });
            
            const testData = {
                template_a: document.getElementById('templateA').value,
                template_b: document.getElementById('templateB').value,
                variables: variables,
                iterations: parseInt(document.getElementById('abIterations').value)
            };
            
            try {
                const response = await fetch('/prompt/api/ab-test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(testData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    displayABTestResults(data.data);
                } else {
                    alert('Errore: ' + data.error);
                }
            } catch (error) {
                alert('Errore di rete: ' + error.message);
            }
        });

        // Display test results
        function displayTestResults(data) {
            const resultsDiv = document.getElementById('testResults');
            const contentDiv = document.getElementById('testContent');
            
            contentDiv.innerHTML = `
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Risultati Test</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Test Totali:</span>
                            <span class="font-medium">${data.analysis.total_tests}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Successo:</span>
                            <span class="font-medium">${data.analysis.successful_tests}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Qualità Media:</span>
                            <span class="font-medium">${data.analysis.average_quality_score.toFixed(2)}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Success Rate:</span>
                            <span class="font-medium">${data.analysis.success_rate.toFixed(1)}%</span>
                        </div>
                    </div>
                </div>
            `;
            
            resultsDiv.classList.remove('hidden');
        }

        // Display A/B test results
        function displayABTestResults(data) {
            const resultsDiv = document.getElementById('abTestResults');
            const contentDiv = document.getElementById('abTestContent');
            
            const winner = data.analysis.winner;
            const improvement = data.analysis.improvement;
            
            contentDiv.innerHTML = `
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-4">Risultati A/B Test</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="border rounded-lg p-4 ${winner === 'A' ? 'border-green-500 bg-green-50' : 'border-gray-200'}">
                            <h5 class="font-medium text-gray-900 mb-2">Template A</h5>
                            <div class="text-sm space-y-1">
                                <div>Qualità Media: <span class="font-medium">${data.analysis.template_a.average_quality_score.toFixed(2)}</span></div>
                                <div>Success Rate: <span class="font-medium">${data.analysis.template_a.success_rate.toFixed(1)}%</span></div>
                                <div>Test Totali: <span class="font-medium">${data.analysis.template_a.total_tests}</span></div>
                            </div>
                        </div>
                        <div class="border rounded-lg p-4 ${winner === 'B' ? 'border-green-500 bg-green-50' : 'border-gray-200'}">
                            <h5 class="font-medium text-gray-900 mb-2">Template B</h5>
                            <div class="text-sm space-y-1">
                                <div>Qualità Media: <span class="font-medium">${data.analysis.template_b.average_quality_score.toFixed(2)}</span></div>
                                <div>Success Rate: <span class="font-medium">${data.analysis.template_b.success_rate.toFixed(1)}%</span></div>
                                <div>Test Totali: <span class="font-medium">${data.analysis.template_b.total_tests}</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 text-center">
                        <span class="text-lg font-medium text-gray-900">Vincitore: Template ${winner}</span>
                        <span class="text-sm text-gray-500 ml-2">(Miglioramento: ${(improvement * 100).toFixed(1)}%)</span>
                    </div>
                </div>
            `;
            
            resultsDiv.classList.remove('hidden');
        }

        // Test single template
        function testTemplate(templateName) {
            document.getElementById('templateSelect').value = templateName;
            document.getElementById('templateSelect').dispatchEvent(new Event('change'));
        }

        // Refresh data
        function refreshData() {
            location.reload();
        }
    </script>
</body>
</html>
