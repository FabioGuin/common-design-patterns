@extends('layouts.app')

@section('title', 'Crea Nuovo Articolo')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-plus-circle text-primary me-2"></i>
                    Crea Nuovo Articolo
                </h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('articles.store') }}" id="articleForm">
                    @csrf

                    <!-- Title -->
                    <div class="mb-4">
                        <label for="title" class="form-label fw-semibold">
                            Titolo <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('title') is-invalid @enderror" 
                               id="title" 
                               name="title" 
                               value="{{ old('title') }}" 
                               placeholder="Inserisci il titolo dell'articolo"
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <span id="titleCount">0</span>/255 caratteri
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="mb-4">
                        <label for="content" class="form-label fw-semibold">
                            Contenuto <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control @error('content') is-invalid @enderror" 
                                  id="content" 
                                  name="content" 
                                  rows="10" 
                                  placeholder="Scrivi il contenuto dell'articolo qui..."
                                  required>{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <span id="contentCount">0</span> caratteri (minimo 50)
                        </div>
                    </div>

                    <!-- Excerpt -->
                    <div class="mb-4">
                        <label for="excerpt" class="form-label fw-semibold">
                            Excerpt
                        </label>
                        <textarea class="form-control @error('excerpt') is-invalid @enderror" 
                                  id="excerpt" 
                                  name="excerpt" 
                                  rows="3" 
                                  placeholder="Breve descrizione dell'articolo (opzionale)">{{ old('excerpt') }}</textarea>
                        @error('excerpt')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <span id="excerptCount">0</span>/500 caratteri
                        </div>
                    </div>

                    <!-- Author and Status -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="user_id" class="form-label fw-semibold">
                                Autore <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('user_id') is-invalid @enderror" 
                                    id="user_id" 
                                    name="user_id" 
                                    required>
                                <option value="">Seleziona un autore</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" 
                                            {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->role }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label fw-semibold">
                                Stato <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status" 
                                    required>
                                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Bozza</option>
                                <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Pubblicato</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Published Date (only for published articles) -->
                    <div class="mb-4" id="publishedDateField" style="display: none;">
                        <label for="published_at" class="form-label fw-semibold">
                            Data di Pubblicazione
                        </label>
                        <input type="datetime-local" 
                               class="form-control @error('published_at') is-invalid @enderror" 
                               id="published_at" 
                               name="published_at" 
                               value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}">
                        @error('published_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Lascia vuoto per pubblicare immediatamente
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('articles.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Annulla
                        </a>
                        <div>
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-save me-1"></i>
                                Salva Articolo
                            </button>
                            <button type="button" class="btn btn-outline-primary" onclick="previewArticle()">
                                <i class="fas fa-eye me-1"></i>
                                Anteprima
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview Modal -->
        <div class="modal fade" id="previewModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Anteprima Articolo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="previewContent">
                            <!-- Preview content will be inserted here -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Character counters
    function updateCounter(elementId, maxLength = null) {
        const element = document.getElementById(elementId);
        const counter = document.getElementById(elementId + 'Count');
        const length = element.value.length;
        
        counter.textContent = length;
        
        if (maxLength) {
            if (length > maxLength) {
                counter.classList.add('text-danger');
            } else {
                counter.classList.remove('text-danger');
            }
        }
    }

    // Initialize counters
    document.addEventListener('DOMContentLoaded', function() {
        // Title counter
        const titleInput = document.getElementById('title');
        titleInput.addEventListener('input', function() {
            updateCounter('title', 255);
        });

        // Content counter
        const contentInput = document.getElementById('content');
        contentInput.addEventListener('input', function() {
            updateCounter('content');
        });

        // Excerpt counter
        const excerptInput = document.getElementById('excerpt');
        excerptInput.addEventListener('input', function() {
            updateCounter('excerpt', 500);
        });

        // Status change handler
        const statusSelect = document.getElementById('status');
        const publishedDateField = document.getElementById('publishedDateField');
        
        statusSelect.addEventListener('change', function() {
            if (this.value === 'published') {
                publishedDateField.style.display = 'block';
            } else {
                publishedDateField.style.display = 'none';
            }
        });

        // Trigger initial state
        statusSelect.dispatchEvent(new Event('change'));
    });

    // Preview function
    function previewArticle() {
        const title = document.getElementById('title').value;
        const content = document.getElementById('content').value;
        const excerpt = document.getElementById('excerpt').value;
        const status = document.getElementById('status').value;
        
        if (!title || !content) {
            alert('Inserisci almeno titolo e contenuto per vedere l\'anteprima');
            return;
        }

        const previewContent = `
            <div class="article-preview">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="badge bg-${status === 'published' ? 'success' : 'warning'}">
                        ${status === 'published' ? 'Pubblicato' : 'Bozza'}
                    </span>
                    <small class="text-muted">${new Date().toLocaleDateString()}</small>
                </div>
                
                <h1 class="display-6 fw-bold mb-3">${title}</h1>
                
                ${excerpt ? `
                    <div class="alert alert-light border-start border-primary border-4 mb-4">
                        <p class="mb-0 fst-italic">${excerpt}</p>
                    </div>
                ` : ''}
                
                <div class="article-content">
                    ${content.replace(/\n/g, '<br>')}
                </div>
            </div>
        `;

        document.getElementById('previewContent').innerHTML = previewContent;
        
        const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
        previewModal.show();
    }

    // Form validation
    document.getElementById('articleForm').addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        const content = document.getElementById('content').value.trim();
        const user_id = document.getElementById('user_id').value;
        const status = document.getElementById('status').value;

        if (!title) {
            e.preventDefault();
            alert('Il titolo è obbligatorio');
            document.getElementById('title').focus();
            return;
        }

        if (!content) {
            e.preventDefault();
            alert('Il contenuto è obbligatorio');
            document.getElementById('content').focus();
            return;
        }

        if (!user_id) {
            e.preventDefault();
            alert('Seleziona un autore');
            document.getElementById('user_id').focus();
            return;
        }

        if (content.length < 50) {
            e.preventDefault();
            alert('Il contenuto deve essere di almeno 50 caratteri');
            document.getElementById('content').focus();
            return;
        }
    });
</script>
@endpush
