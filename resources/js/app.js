/**
 * PornGuruCam - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize infinite scroll if config exists
    if (window.infiniteScrollConfig) {
        initInfiniteScroll();
    }
});

/**
 * Infinite Scroll Implementation
 */
function initInfiniteScroll() {
    const config = window.infiniteScrollConfig;
    const grid = document.getElementById('models-grid');
    const loader = document.getElementById('infinite-loader');
    const trigger = document.getElementById('infinite-scroll-trigger');
    
    if (!grid || !trigger) return;

    let currentPage = config.currentPage;
    let hasMore = config.hasMore;
    let isLoading = false;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && hasMore && !isLoading) {
                loadMoreModels();
            }
        });
    }, {
        rootMargin: '400px',
        threshold: 0
    });

    observer.observe(trigger);

    async function loadMoreModels() {
        if (isLoading || !hasMore) return;
        
        isLoading = true;
        if (loader) {
            loader.style.display = 'flex';
            loader.innerHTML = '<div class="loader-spinner"></div><span>Loading more models...</span>';
        }

        try {
            const params = new URLSearchParams(config.filters);
            params.set('page', currentPage + 1);
            
            const response = await fetch(`${config.apiUrl}?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Failed to load');

            const data = await response.json();

            if (data.html) {
                grid.insertAdjacentHTML('beforeend', data.html);
            }

            currentPage = data.nextPage - 1;
            hasMore = data.hasMore;

        } catch (error) {
            console.error('Error loading models:', error);
            hasMore = false;
            if (loader) {
                loader.innerHTML = '<span class="loader-error">Failed to load. <button onclick="location.reload()">Retry</button></span>';
                return;
            }
        } finally {
            isLoading = false;
            if (loader) loader.style.display = 'none';

            if (hasMore) {
                observer.unobserve(trigger);
                observer.observe(trigger);
            } else {
                observer.disconnect();
            }
        }
    }
}
