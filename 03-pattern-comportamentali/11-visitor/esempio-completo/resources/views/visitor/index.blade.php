<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Pattern - Esempio Completo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <h1 class="text-center mb-4">
            <i class="fas fa-folder-open text-primary"></i>
            Visitor Pattern - Esempio Completo
        </h1>
        <p class="text-center text-muted mb-5">
            Sistema di operazioni su strutture di file e directory
        </p>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">File System Structure</h5>
                    </div>
                    <div class="card-body">
                        <div id="file-system">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <i class="fas fa-folder text-warning"></i> Documents
                                    <ul class="list-group list-group-flush ms-3">
                                        <li class="list-group-item">
                                            <i class="fas fa-file text-info"></i> document1.pdf (2.5 MB)
                                        </li>
                                        <li class="list-group-item">
                                            <i class="fas fa-file text-info"></i> document2.docx (1.8 MB)
                                        </li>
                                    </ul>
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-folder text-warning"></i> Images
                                    <ul class="list-group list-group-flush ms-3">
                                        <li class="list-group-item">
                                            <i class="fas fa-file text-info"></i> image1.jpg (5.2 MB)
                                        </li>
                                        <li class="list-group-item">
                                            <i class="fas fa-file text-info"></i> image2.png (3.1 MB)
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Visitor Operations</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <button class="btn btn-primary me-2" id="size-calculator">Calculate Total Size</button>
                            <button class="btn btn-success me-2" id="file-counter">Count Files</button>
                            <button class="btn btn-info me-2" id="list-files">List All Files</button>
                        </div>
                        <div id="visitor-results">
                            <p class="text-muted">No operations performed yet</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simula Visitor Pattern
        class FileSystemElement {
            accept(visitor) {
                throw new Error('Method must be implemented');
            }
        }
        
        class File extends FileSystemElement {
            constructor(name, size) {
                super();
                this.name = name;
                this.size = size;
            }
            
            accept(visitor) {
                return visitor.visitFile(this);
            }
            
            getName() {
                return this.name;
            }
            
            getSize() {
                return this.size;
            }
        }
        
        class Directory extends FileSystemElement {
            constructor(name) {
                super();
                this.name = name;
                this.children = [];
            }
            
            addChild(element) {
                this.children.push(element);
            }
            
            accept(visitor) {
                return visitor.visitDirectory(this);
            }
            
            getName() {
                return this.name;
            }
            
            getChildren() {
                return this.children;
            }
        }
        
        class SizeCalculatorVisitor {
            constructor() {
                this.totalSize = 0;
            }
            
            visitFile(file) {
                this.totalSize += file.getSize();
                return file.getSize();
            }
            
            visitDirectory(directory) {
                let directorySize = 0;
                directory.getChildren().forEach(child => {
                    directorySize += child.accept(this);
                });
                return directorySize;
            }
            
            getTotalSize() {
                return this.totalSize;
            }
        }
        
        class FileCountVisitor {
            constructor() {
                this.fileCount = 0;
            }
            
            visitFile(file) {
                this.fileCount++;
                return 1;
            }
            
            visitDirectory(directory) {
                let count = 0;
                directory.getChildren().forEach(child => {
                    count += child.accept(this);
                });
                return count;
            }
            
            getFileCount() {
                return this.fileCount;
            }
        }
        
        class FileListerVisitor {
            constructor() {
                this.files = [];
            }
            
            visitFile(file) {
                this.files.push(file.getName());
                return [file.getName()];
            }
            
            visitDirectory(directory) {
                let files = [];
                directory.getChildren().forEach(child => {
                    files = files.concat(child.accept(this));
                });
                return files;
            }
            
            getFiles() {
                return this.files;
            }
        }
        
        // Crea la struttura del file system
        const documents = new Directory('Documents');
        documents.addChild(new File('document1.pdf', 2.5));
        documents.addChild(new File('document2.docx', 1.8));
        
        const images = new Directory('Images');
        images.addChild(new File('image1.jpg', 5.2));
        images.addChild(new File('image2.png', 3.1));
        
        const root = new Directory('Root');
        root.addChild(documents);
        root.addChild(images);
        
        // Event listeners
        document.getElementById('size-calculator').addEventListener('click', function() {
            const visitor = new SizeCalculatorVisitor();
            const totalSize = root.accept(visitor);
            displayResult(`Total size: ${totalSize.toFixed(1)} MB`);
        });
        
        document.getElementById('file-counter').addEventListener('click', function() {
            const visitor = new FileCountVisitor();
            const fileCount = root.accept(visitor);
            displayResult(`Total files: ${fileCount}`);
        });
        
        document.getElementById('list-files').addEventListener('click', function() {
            const visitor = new FileListerVisitor();
            const files = root.accept(visitor);
            displayResult(`Files: ${files.join(', ')}`);
        });
        
        function displayResult(message) {
            const resultsDiv = document.getElementById('visitor-results');
            const resultDiv = document.createElement('div');
            resultDiv.className = 'alert alert-info alert-dismissible fade show';
            resultDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            resultsDiv.insertBefore(resultDiv, resultsDiv.firstChild);
        }
    </script>
</body>
</html>
