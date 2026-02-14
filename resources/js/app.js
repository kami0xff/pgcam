/**
 * PornGuruCam - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize infinite scroll if config exists
    if (window.infiniteScrollConfig) {
        initInfiniteScroll();
    }

    // Initialize video previews
    initVideoPreviews();
});

/**
 * Video Previews on Hover
 */
function initVideoPreviews() {
    // Check if Hls is supported
    const isHlsSupported = typeof Hls !== 'undefined' && Hls.isSupported();
    let activeHls = null;
    let activeVideo = null;
    let hoverTimeout = null;

    // Delegate hover events for model cards
    document.body.addEventListener('mouseover', function(e) {
        const card = e.target.closest('.model-card, .suggested-model-card');
        if (!card) return;

        // Clear any pending hover timeout
        if (hoverTimeout) clearTimeout(hoverTimeout);

        // Delay preview start slightly to prevent flashing when moving mouse quickly
        hoverTimeout = setTimeout(() => {
            playPreview(card);
        }, 300);
    });

    document.body.addEventListener('mouseout', function(e) {
        const card = e.target.closest('.model-card, .suggested-model-card');
        if (!card) return;

        if (hoverTimeout) clearTimeout(hoverTimeout);
        stopPreview(card);
    });

    function playPreview(card) {
        const video = card.querySelector('video');
        const streamUrl = card.dataset.streamUrl;

        if (!video || !streamUrl) return;

        // Stop any currently playing video
        if (activeVideo && activeVideo !== video) {
            stopPreview(activeVideo.closest('.model-card, .suggested-model-card'));
        }

        activeVideo = video;
        card.classList.add('stream-playing');
        // Video is shown via CSS opacity transition

        if (isHlsSupported) {
            if (activeHls) {
                activeHls.destroy();
            }
            activeHls = new Hls({
                enableWorker: true,
                lowLatencyMode: true,
                backBufferLength: 90
            });
            activeHls.loadSource(streamUrl);
            activeHls.attachMedia(video);
            activeHls.on(Hls.Events.MANIFEST_PARSED, function() {
                video.play().catch(e => console.log('Autoplay prevented', e));
            });
        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
            // Native HLS (Safari)
            video.src = streamUrl;
            video.play().catch(e => console.log('Autoplay prevented', e));
        }
    }

    function stopPreview(card) {
        if (!card) return;
        
        const video = card.querySelector('video');
        if (!video) return;

        card.classList.remove('stream-playing');
        video.pause();
        
        if (activeHls && activeVideo === video) {
            activeHls.destroy();
            activeHls = null;
        }
        
        video.removeAttribute('src'); // Clear source
        video.load(); // Reset video element
        
        if (activeVideo === video) {
            activeVideo = null;
        }
    }
}

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

    // Create Intersection Observer
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && hasMore && !isLoading) {
                loadMoreModels();
            }
        });
    }, {
        rootMargin: '200px', // Start loading before user reaches the end
        threshold: 0
    });

    // Observe the trigger element
    observer.observe(trigger);

    async function loadMoreModels() {
        if (isLoading || !hasMore) return;
        
        isLoading = true;
        if (loader) loader.style.display = 'flex';

        try {
            // Build URL with filters
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

            // Append new models to grid
            if (data.html) {
                grid.insertAdjacentHTML('beforeend', data.html);
            }

            // Update state
            currentPage = data.nextPage - 1;
            hasMore = data.hasMore;

            // Hide loader if no more pages
            if (!hasMore && loader) {
                loader.style.display = 'none';
            }

        } catch (error) {
            console.error('Error loading models:', error);
            if (loader) {
                loader.innerHTML = '<span class="loader-error">Failed to load. <button onclick="location.reload()">Retry</button></span>';
            }
        } finally {
            isLoading = false;
        }
    }
}
