<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iterator Pattern - Esempio Completo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <h1 class="text-center mb-4">
            <i class="fas fa-list text-primary"></i>
            Iterator Pattern - Esempio Completo
        </h1>
        <p class="text-center text-muted mb-5">
            Sistema di iterazione uniforme su diverse collezioni di dati
        </p>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Array Iterator</h5>
                    </div>
                    <div class="card-body">
                        <p>Iterazione standard su array di dati</p>
                        <div id="array-result" class="mt-3"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Reverse Iterator</h5>
                    </div>
                    <div class="card-body">
                        <p>Iterazione in ordine inverso</p>
                        <div id="reverse-result" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simula iterazione su array
        const data = ['Item 1', 'Item 2', 'Item 3', 'Item 4', 'Item 5'];
        
        // Array Iterator
        let arrayResult = '<ul class="list-group">';
        data.forEach((item, index) => {
            arrayResult += `<li class="list-group-item">${index}: ${item}</li>`;
        });
        arrayResult += '</ul>';
        document.getElementById('array-result').innerHTML = arrayResult;
        
        // Reverse Iterator
        let reverseResult = '<ul class="list-group">';
        data.slice().reverse().forEach((item, index) => {
            reverseResult += `<li class="list-group-item">${index}: ${item}</li>`;
        });
        reverseResult += '</ul>';
        document.getElementById('reverse-result').innerHTML = reverseResult;
    </script>
</body>
</html>
