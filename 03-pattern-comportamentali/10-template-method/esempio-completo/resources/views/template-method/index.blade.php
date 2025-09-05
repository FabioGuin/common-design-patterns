<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template Method Pattern - Esempio Completo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <h1 class="text-center mb-4">
            <i class="fas fa-file-alt text-primary"></i>
            Template Method Pattern - Esempio Completo
        </h1>
        <p class="text-center text-muted mb-5">
            Sistema di generazione report con template personalizzabili
        </p>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Report Generator</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="report-type" class="form-label">Report Type:</label>
                            <select class="form-select" id="report-type">
                                <option value="pdf">PDF Report</option>
                                <option value="html">HTML Report</option>
                                <option value="csv">CSV Report</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="report-data" class="form-label">Report Data (JSON):</label>
                            <textarea class="form-control" id="report-data" rows="5" placeholder='[{"title": "Report Item 1", "content": "Content 1"}, {"title": "Report Item 2", "content": "Content 2"}]'>[{"title": "Report Item 1", "content": "Content 1"}, {"title": "Report Item 2", "content": "Content 2"}]</textarea>
                        </div>
                        <button class="btn btn-primary" id="generate-report">Generate Report</button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Generated Report</h5>
                    </div>
                    <div class="card-body">
                        <div id="generated-report">
                            <p class="text-muted">No report generated yet</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simula Template Method Pattern
        class ReportGenerator {
            generateReport(data) {
                this.validateData(data);
                const formattedData = this.formatData(data);
                const header = this.generateHeader();
                const body = this.generateBody(formattedData);
                const footer = this.generateFooter();
                
                return this.combineSections(header, body, footer);
            }
            
            validateData(data) {
                if (!Array.isArray(data) || data.length === 0) {
                    throw new Error('Data must be a non-empty array');
                }
            }
            
            formatData(data) {
                throw new Error('Method must be implemented');
            }
            
            generateHeader() {
                throw new Error('Method must be implemented');
            }
            
            generateBody(data) {
                throw new Error('Method must be implemented');
            }
            
            generateFooter() {
                throw new Error('Method must be implemented');
            }
            
            combineSections(header, body, footer) {
                return header + '\n' + body + '\n' + footer;
            }
        }
        
        class PDFReportGenerator extends ReportGenerator {
            formatData(data) {
                return data.map(item => ({
                    title: item.title.toUpperCase(),
                    content: item.content.replace(/\n/g, ' ')
                }));
            }
            
            generateHeader() {
                return `=== PDF REPORT ===\n${new Date().toLocaleString()}`;
            }
            
            generateBody(data) {
                let body = '';
                data.forEach(item => {
                    body += `Title: ${item.title}\nContent: ${item.content}\n\n`;
                });
                return body;
            }
            
            generateFooter() {
                return '=== END OF PDF REPORT ===';
            }
        }
        
        class HTMLReportGenerator extends ReportGenerator {
            formatData(data) {
                return data.map(item => ({
                    title: item.title,
                    content: item.content.replace(/\n/g, '<br>')
                }));
            }
            
            generateHeader() {
                return `<h1>HTML Report</h1><p>Generated on: ${new Date().toLocaleString()}</p>`;
            }
            
            generateBody(data) {
                let body = '<div class="report-body">';
                data.forEach(item => {
                    body += `<div class="report-item"><h2>${item.title}</h2><p>${item.content}</p></div>`;
                });
                body += '</div>';
                return body;
            }
            
            generateFooter() {
                return '<footer><p>End of HTML Report</p></footer>';
            }
        }
        
        class CSVReportGenerator extends ReportGenerator {
            formatData(data) {
                return data.map(item => ({
                    title: item.title.replace(/,/g, ';'),
                    content: item.content.replace(/,/g, ';').replace(/\n/g, ' ')
                }));
            }
            
            generateHeader() {
                return 'Title,Content,Generated';
            }
            
            generateBody(data) {
                let body = '';
                data.forEach(item => {
                    body += `${item.title},${item.content},${new Date().toISOString()}\n`;
                });
                return body;
            }
            
            generateFooter() {
                return 'End of CSV Report';
            }
        }
        
        // Inizializza il sistema
        const generators = {
            'pdf': new PDFReportGenerator(),
            'html': new HTMLReportGenerator(),
            'csv': new CSVReportGenerator()
        };
        
        // Event listeners
        document.getElementById('generate-report').addEventListener('click', function() {
            const reportType = document.getElementById('report-type').value;
            const reportData = JSON.parse(document.getElementById('report-data').value);
            
            try {
                const generator = generators[reportType];
                const report = generator.generateReport(reportData);
                displayReport(report, reportType);
            } catch (error) {
                displayReport(`Error: ${error.message}`, reportType);
            }
        });
        
        function displayReport(report, type) {
            const reportDiv = document.getElementById('generated-report');
            if (type === 'html') {
                reportDiv.innerHTML = report;
            } else {
                reportDiv.innerHTML = `<pre>${report}</pre>`;
            }
        }
    </script>
</body>
</html>
