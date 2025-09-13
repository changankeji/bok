/**
 * 管理后台JavaScript
 * 提供管理界面的交互功能
 */

(function() {
    'use strict';

    // 页面加载完成后执行
    document.addEventListener('DOMContentLoaded', function() {
        initSidebar();
        initDataTables();
        initFormValidation();
        initImageUpload();
        initRichTextEditor();
        initConfirmDialogs();
        initAutoSave();
    });

    /**
     * 初始化侧边栏
     */
    function initSidebar() {
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const sidebar = document.querySelector('.sidebar');
        
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
            });
        }

        // 点击外部关闭侧边栏
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && 
                !e.target.closest('.sidebar') && 
                !e.target.closest('.sidebar-toggle')) {
                sidebar.classList.remove('show');
            }
        });
    }

    /**
     * 初始化数据表格
     */
    function initDataTables() {
        const tables = document.querySelectorAll('.table');
        tables.forEach(table => {
            // 添加排序功能
            const headers = table.querySelectorAll('th[data-sort]');
            headers.forEach(header => {
                header.style.cursor = 'pointer';
                header.innerHTML += ' <i class="fas fa-sort text-muted"></i>';
                
                header.addEventListener('click', function() {
                    sortTable(table, this.cellIndex, this.dataset.sort);
                });
            });
        });
    }

    /**
     * 表格排序
     */
    function sortTable(table, columnIndex, order) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            const aText = a.cells[columnIndex].textContent.trim();
            const bText = b.cells[columnIndex].textContent.trim();
            
            if (order === 'asc') {
                return aText.localeCompare(bText);
            } else {
                return bText.localeCompare(aText);
            }
        });
        
        rows.forEach(row => tbody.appendChild(row));
    }

    /**
     * 初始化表单验证
     */
    function initFormValidation() {
        const forms = document.querySelectorAll('.needs-validation');
        
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                
                form.classList.add('was-validated');
            });
        });

        // 实时验证
        const inputs = document.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });
    }

    /**
     * 验证单个字段
     */
    function validateField(field) {
        const isValid = field.checkValidity();
        const feedback = field.parentNode.querySelector('.invalid-feedback');
        
        if (isValid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
        }
    }

    /**
     * 初始化图片上传
     */
    function initImageUpload() {
        const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
        
        imageInputs.forEach(input => {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    previewImage(file, this);
                }
            });
        });
    }

    /**
     * 预览图片
     */
    function previewImage(file, input) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = input.parentNode.querySelector('.image-preview');
            if (preview) {
                preview.innerHTML = `<img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">`;
            }
        };
        reader.readAsDataURL(file);
    }

    /**
     * 初始化富文本编辑器
     */
    function initRichTextEditor() {
        const editors = document.querySelectorAll('.rich-editor');
        
        editors.forEach(editor => {
            // 这里可以集成其他富文本编辑器
            editor.addEventListener('input', function() {
                // 自动保存草稿
                saveDraft(editor);
            });
        });
    }

    /**
     * 初始化确认对话框
     */
    function initConfirmDialogs() {
        const deleteButtons = document.querySelectorAll('[data-confirm]');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const message = this.dataset.confirm || '确定要执行此操作吗？';
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        });
    }

    /**
     * 初始化自动保存
     */
    function initAutoSave() {
        const forms = document.querySelectorAll('form[data-autosave]');
        
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, textarea, select');
            let saveTimeout;
            
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    clearTimeout(saveTimeout);
                    saveTimeout = setTimeout(() => {
                        saveFormData(form);
                    }, 2000);
                });
            });
        });
    }

    /**
     * 保存表单数据
     */
    function saveFormData(form) {
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        localStorage.setItem(`draft_${form.id}`, JSON.stringify(data));
        showNotification('草稿已自动保存', 'info');
    }

    /**
     * 恢复草稿
     */
    function restoreDraft(formId) {
        const draft = localStorage.getItem(`draft_${formId}`);
        if (draft) {
            const data = JSON.parse(draft);
            const form = document.getElementById(formId);
            
            if (form) {
                Object.keys(data).forEach(key => {
                    const field = form.querySelector(`[name="${key}"]`);
                    if (field) {
                        field.value = data[key];
                    }
                });
            }
        }
    }

    /**
     * 显示通知
     */
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    /**
     * 批量操作
     */
    function initBatchOperations() {
        const selectAllCheckbox = document.querySelector('.select-all');
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');
        const batchActions = document.querySelector('.batch-actions');
        
        if (selectAllCheckbox && itemCheckboxes.length > 0) {
            selectAllCheckbox.addEventListener('change', function() {
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                toggleBatchActions();
            });
            
            itemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', toggleBatchActions);
            });
        }
    }

    /**
     * 切换批量操作按钮
     */
    function toggleBatchActions() {
        const checkedItems = document.querySelectorAll('.item-checkbox:checked');
        const batchActions = document.querySelector('.batch-actions');
        
        if (batchActions) {
            if (checkedItems.length > 0) {
                batchActions.style.display = 'block';
            } else {
                batchActions.style.display = 'none';
            }
        }
    }

    /**
     * 执行批量操作
     */
    function executeBatchAction(action) {
        const checkedItems = document.querySelectorAll('.item-checkbox:checked');
        const ids = Array.from(checkedItems).map(checkbox => checkbox.value);
        
        if (ids.length === 0) {
            showNotification('请选择要操作的项目', 'warning');
            return;
        }
        
        if (confirm(`确定要${action}选中的 ${ids.length} 个项目吗？`)) {
            // 这里执行实际的批量操作
            console.log(`执行批量${action}:`, ids);
        }
    }

    /**
     * 搜索和筛选
     */
    function initSearchAndFilter() {
        const searchInput = document.querySelector('.search-input');
        const filterSelects = document.querySelectorAll('.filter-select');
        
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    performSearch(this.value);
                }, 300);
            });
        }
        
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                applyFilters();
            });
        });
    }

    /**
     * 执行搜索
     */
    function performSearch(query) {
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const matches = text.includes(query.toLowerCase());
            row.style.display = matches ? '' : 'none';
        });
    }

    /**
     * 应用筛选
     */
    function applyFilters() {
        const filters = {};
        const filterSelects = document.querySelectorAll('.filter-select');
        
        filterSelects.forEach(select => {
            if (select.value) {
                filters[select.name] = select.value;
            }
        });
        
        // 这里应用筛选逻辑
        console.log('应用筛选:', filters);
    }

    /**
     * 导出数据
     */
    function exportData(format = 'csv') {
        const table = document.querySelector('.table');
        if (!table) return;
        
        const rows = table.querySelectorAll('tr');
        let data = [];
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('td, th');
            const rowData = Array.from(cells).map(cell => cell.textContent.trim());
            data.push(rowData);
        });
        
        if (format === 'csv') {
            const csv = data.map(row => row.join(',')).join('\n');
            downloadFile(csv, 'data.csv', 'text/csv');
        }
    }

    /**
     * 下载文件
     */
    function downloadFile(content, filename, mimeType) {
        const blob = new Blob([content], { type: mimeType });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        link.click();
        URL.revokeObjectURL(url);
    }

    // 初始化批量操作
    initBatchOperations();
    initSearchAndFilter();

    // 全局函数
    window.AdminUtils = {
        showNotification,
        exportData,
        executeBatchAction,
        restoreDraft,
        saveFormData
    };

})();
