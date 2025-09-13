/**
 * 博客系统前端JavaScript
 * 提供交互功能和用户体验增强
 */

(function() {
    'use strict';

    // 页面加载完成后执行
    document.addEventListener('DOMContentLoaded', function() {
        initAnimations();
        initSearch();
        initTooltips();
        initScrollEffects();
        initImageLazyLoading();
    });

    /**
     * 初始化动画效果
     */
    function initAnimations() {
        // 为卡片添加淡入动画
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease-out';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    }

    /**
     * 初始化搜索功能
     */
    function initSearch() {
        const searchInput = document.querySelector('input[name="search"]');
        if (!searchInput) return;

        // 搜索建议功能
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                hideSearchSuggestions();
                return;
            }

            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        });

        // 搜索框聚焦时显示建议
        searchInput.addEventListener('focus', function() {
            if (this.value.trim().length >= 2) {
                performSearch(this.value.trim());
            }
        });

        // 点击外部隐藏建议
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-container')) {
                hideSearchSuggestions();
            }
        });
    }

    /**
     * 执行搜索
     */
    function performSearch(query) {
        // 这里可以添加AJAX搜索功能
        console.log('搜索:', query);
    }

    /**
     * 隐藏搜索建议
     */
    function hideSearchSuggestions() {
        const suggestions = document.querySelector('.search-suggestions');
        if (suggestions) {
            suggestions.remove();
        }
    }

    /**
     * 初始化工具提示
     */
    function initTooltips() {
        // 初始化Bootstrap工具提示
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    /**
     * 初始化滚动效果
     */
    function initScrollEffects() {
        let lastScrollTop = 0;
        const navbar = document.querySelector('.navbar');
        
        if (!navbar) return;

        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            // 导航栏滚动效果
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                // 向下滚动，隐藏导航栏
                navbar.style.transform = 'translateY(-100%)';
            } else {
                // 向上滚动，显示导航栏
                navbar.style.transform = 'translateY(0)';
            }
            
            lastScrollTop = scrollTop;
        });

        // 平滑滚动到顶部
        const backToTopBtn = document.querySelector('.back-to-top');
        if (backToTopBtn) {
            backToTopBtn.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            // 显示/隐藏回到顶部按钮
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTopBtn.style.display = 'block';
                } else {
                    backToTopBtn.style.display = 'none';
                }
            });
        }
    }

    /**
     * 初始化图片懒加载
     */
    function initImageLazyLoading() {
        const images = document.querySelectorAll('img[data-src]');
        
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            images.forEach(img => imageObserver.observe(img));
        } else {
            // 降级处理
            images.forEach(img => {
                img.src = img.dataset.src;
                img.classList.remove('lazy');
            });
        }
    }

    /**
     * 格式化日期
     */
    function formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffTime = Math.abs(now - date);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays === 1) {
            return '昨天';
        } else if (diffDays < 7) {
            return `${diffDays}天前`;
        } else if (diffDays < 30) {
            return `${Math.ceil(diffDays / 7)}周前`;
        } else {
            return date.toLocaleDateString('zh-CN');
        }
    }

    /**
     * 复制到剪贴板
     */
    function copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('已复制到剪贴板', 'success');
            });
        } else {
            // 降级处理
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showToast('已复制到剪贴板', 'success');
        }
    }

    /**
     * 显示提示消息
     */
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', () => {
            document.body.removeChild(toast);
        });
    }

    /**
     * 文章阅读进度
     */
    function initReadingProgress() {
        const article = document.querySelector('.article-content');
        if (!article) return;

        const progressBar = document.createElement('div');
        progressBar.className = 'reading-progress';
        progressBar.innerHTML = '<div class="reading-progress-bar"></div>';
        document.body.appendChild(progressBar);

        window.addEventListener('scroll', () => {
            const articleTop = article.offsetTop;
            const articleHeight = article.offsetHeight;
            const windowHeight = window.innerHeight;
            const scrollTop = window.pageYOffset;
            
            const progress = Math.min(
                Math.max((scrollTop - articleTop + windowHeight) / articleHeight, 0),
                1
            );
            
            const bar = progressBar.querySelector('.reading-progress-bar');
            bar.style.width = `${progress * 100}%`;
        });
    }

    /**
     * 代码复制功能
     */
    function initCodeCopy() {
        const codeBlocks = document.querySelectorAll('pre code');
        codeBlocks.forEach(block => {
            const pre = block.parentElement;
            if (pre.querySelector('.copy-btn')) return;

            const copyBtn = document.createElement('button');
            copyBtn.className = 'copy-btn btn btn-sm btn-outline-secondary';
            copyBtn.innerHTML = '<i class="fas fa-copy"></i>';
            copyBtn.title = '复制代码';
            
            copyBtn.addEventListener('click', () => {
                copyToClipboard(block.textContent);
            });
            
            pre.style.position = 'relative';
            pre.appendChild(copyBtn);
        });
    }

    // 初始化文章页面功能
    if (document.querySelector('.article-content')) {
        initReadingProgress();
        initCodeCopy();
    }

    // 全局函数
    window.BlogUtils = {
        formatDate,
        copyToClipboard,
        showToast
    };

})();
