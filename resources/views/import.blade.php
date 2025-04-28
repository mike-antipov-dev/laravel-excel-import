<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/js/app.js'])
    <title>Импорт Excel</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            padding: 30px;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
        }

        .container {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
            font-weight: 600;
            text-align: center;
        }

        .upload-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .file-input-container {
            position: relative;
            border: 2px dashed #ccc;
            border-radius: 6px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            background-color: #f8f9fa;
        }

        .file-input-container:hover {
            border-color: #4CAF50;
        }

        .file-input-container.drag-over {
            border-color: #4CAF50;
            background-color: rgba(76, 175, 80, 0.1);
        }

        .file-input-container input {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .upload-icon {
            font-size: 48px;
            color: #4CAF50;
            margin-bottom: 15px;
        }

        .file-label {
            font-size: 18px;
            color: #555;
            margin-bottom: 8px;
        }

        .file-hint {
            font-size: 14px;
            color: #888;
        }

        .selected-file {
            margin-top: 15px;
            padding: 8px 12px;
            background-color: #e9f5e9;
            border-radius: 4px;
            display: none;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #3e8e41;
        }

        button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .progress-container {
            margin-top: 20px;
            display: none;
        }

        .progress-bar {
            height: 8px;
            background-color: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .progress-fill {
            height: 100%;
            background-color: #4CAF50;
            width: 0%;
            transition: width 0.3s;
        }

        .progress-text {
            font-size: 14px;
            color: #666;
        }

        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 4px;
            display: none;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Импорт Excel файла</h1>

    <div class="upload-form">
        <div class="file-input-container" id="drop-area">
            <div class="upload-icon">📄</div>
            <div class="file-label">Выберите Excel файл или перетащите его сюда</div>
            <div class="file-hint">Поддерживаемые форматы: .xlsx, .xls</div>
            <div class="selected-file" id="selected-file"></div>
            <input type="file" id="file-input" accept=".xlsx,.xls" />
        </div>

        <button id="upload-button" disabled>Загрузить</button>

        <div class="progress-container" id="progress-container">
            <div class="progress-bar">
                <div class="progress-fill" id="progress-fill"></div>
            </div>
            <div class="progress-text" id="progress-text">Загрузка: 0%</div>
        </div>

        <div class="result" id="result"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropArea = document.getElementById('drop-area');
        const fileInput = document.getElementById('file-input');
        const selectedFileDiv = document.getElementById('selected-file');
        const uploadButton = document.getElementById('upload-button');
        const progressContainer = document.getElementById('progress-container');
        const progressFill = document.getElementById('progress-fill');
        const progressText = document.getElementById('progress-text');
        const resultDiv = document.getElementById('result');

        // Handle drag and drop events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, function() {
                dropArea.classList.add('drag-over');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, function() {
                dropArea.classList.remove('drag-over');
            }, false);
        });

        // Handle file drop
        dropArea.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length) {
                fileInput.files = files;
                handleFileSelect();
            }
        }, false);

        // Handle file select
        fileInput.addEventListener('change', handleFileSelect);

        function handleFileSelect() {
            if (fileInput.files.length) {
                const file = fileInput.files[0];

                // Check if it's an Excel file
                if (!file.name.match(/\.(xlsx|xls)$/)) {
                    resultDiv.className = 'result error';
                    resultDiv.textContent = 'Пожалуйста, выберите файл Excel (.xlsx или .xls)';
                    resultDiv.style.display = 'block';
                    uploadButton.disabled = true;
                    return;
                }

                // Show selected file
                selectedFileDiv.textContent = file.name;
                selectedFileDiv.style.display = 'block';
                uploadButton.disabled = false;

                // Hide any previous results
                resultDiv.style.display = 'none';
            } else {
                selectedFileDiv.style.display = 'none';
                uploadButton.disabled = true;
            }
        }

        // Handle upload
        uploadButton.addEventListener('click', function() {
            if (!fileInput.files.length) return;

            const file = fileInput.files[0];
            const formData = new FormData();
            formData.append('file', file);

            // Show progress bar
            progressContainer.style.display = 'block';
            progressFill.style.width = '0%';
            progressText.textContent = 'Загрузка: 0%';
            uploadButton.disabled = true;

            // Get CSRF token
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Make AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route("import.upload") }}', true);
            xhr.setRequestHeader('X-CSRF-TOKEN', token);

            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    const percentComplete = Math.round((e.loaded / e.total) * 100);
                    progressFill.style.width = percentComplete + '%';
                    progressText.textContent = `Загрузка: ${percentComplete}%`;
                }
            };

            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);

                        if (response.success) {
                            resultDiv.className = 'result success';
                            resultDiv.textContent = response.message || 'Файл успешно загружен и обработан!';
                        } else {
                            resultDiv.className = 'result error';
                            resultDiv.textContent = response.message || 'Произошла ошибка при обработке файла.';
                        }
                    } catch (e) {
                        resultDiv.className = 'result error';
                        resultDiv.textContent = 'Неверный формат ответа от сервера.';
                        console.log(e);
                    }
                } else {
                    resultDiv.className = 'result error';

                    if (xhr.status === 422) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            const errors = response.errors;
                            let errorMessage = 'Ошибка валидации: ';

                            for (const field in errors) {
                                errorMessage += errors[field].join(', ');
                            }

                            resultDiv.textContent = errorMessage;
                        } catch (e) {
                            resultDiv.textContent = 'Ошибка валидации данных.';
                        }
                    } else {
                        resultDiv.textContent = `Ошибка сервера (${xhr.status}): Не удалось загрузить файл.`;
                    }
                }

                resultDiv.style.display = 'block';
                uploadButton.disabled = false;
            };

            xhr.onerror = function() {
                resultDiv.className = 'result error';
                resultDiv.textContent = 'Ошибка сетевого соединения. Пожалуйста, попробуйте позже.';
                resultDiv.style.display = 'block';
                uploadButton.disabled = false;
            };

            xhr.send(formData);
        });
    });
</script>
</body>
</html>
