/**
 * Stream Preview Manager
 * Handles HLS video previews with auto-play support across all pages
 */

window.StreamPreviewManager = (function() {
    let allPreviewsPlaying = false;
    let autoplayEnabled = false;
    const activeStreams = new Map();
    const failedStreams = new Set();
    let hoverTimeout = null;
    let scrollTimeout = null;

    /**
     * Initialize the stream preview manager
     */
    function init() {
        // Load autoplay preference from localStorage or use smart default
        initAutoplayPreference();
        
        // Setup hover events
        setupHoverEvents();
        
        // Setup scroll handler
        setupScrollHandler();
        
        // Setup infinite scroll observer
        setupInfiniteScrollObserver();
    }

    /**
     * Initialize autoplay based on user preference or connection speed
     */
    function initAutoplayPreference() {
        const savedPref = localStorage.getItem('autoplayPreviews');
        const checkbox = document.getElementById('autoplay-checkbox');
        
        if (savedPref !== null) {
            // Use saved preference
            const enabled = savedPref === 'true';
            if (enabled) {
                toggleAutoplay(true, false);
            }
            if (checkbox) checkbox.checked = enabled;
        } else if ('connection' in navigator && navigator.connection.effectiveType) {
            // Check connection speed for first-time visitors
            const connection = navigator.connection.effectiveType;
            if (connection === 'slow-2g' || connection === '2g') {
                console.log('Slow connection detected, autoplay disabled by default');
                if (checkbox) checkbox.checked = false;
            } else {
                // Good connection: enable autoplay by default
                if (checkbox) checkbox.checked = true;
                toggleAutoplay(true, true);
            }
        } else {
            // Default: enable autoplay
            if (checkbox) checkbox.checked = true;
            toggleAutoplay(true, true);
        }
    }

    /**
     * Toggle autoplay on/off
     */
    function toggleAutoplay(enabled, save = true) {
        autoplayEnabled = enabled;
        
        if (save) {
            localStorage.setItem('autoplayPreviews', enabled);
        }
        
        if (enabled) {
            // Sync with allPreviewsPlaying state
            allPreviewsPlaying = true;
            updateToggleButtonState(true);
            playAllVisibleStreams();
        } else {
            allPreviewsPlaying = false;
            updateToggleButtonState(false);
            stopAllStreams();
        }
    }

    /**
     * Toggle all previews (called from Play All button)
     */
    function toggleAllPreviews() {
        allPreviewsPlaying = !allPreviewsPlaying;
        autoplayEnabled = allPreviewsPlaying;
        
        updateToggleButtonState(allPreviewsPlaying);
        localStorage.setItem('autoplayPreviews', allPreviewsPlaying);
        
        // Update checkbox if exists
        const checkbox = document.getElementById('autoplay-checkbox');
        if (checkbox) {
            checkbox.checked = allPreviewsPlaying;
        }

        if (allPreviewsPlaying) {
            playAllVisibleStreams();
        } else {
            stopAllStreams();
        }
    }

    /**
     * Update the toggle button visual state
     */
    function updateToggleButtonState(playing) {
        const toggle = document.getElementById('preview-toggle');
        const label = document.getElementById('preview-label');
        
        if (!toggle || !label) return;
        
        const offIcon = toggle.querySelector('.preview-off');
        const onIcon = toggle.querySelector('.preview-on');

        if (playing) {
            label.textContent = 'Stop All';
            if (offIcon) offIcon.style.display = 'none';
            if (onIcon) onIcon.style.display = 'block';
            toggle.classList.add('active');
        } else {
            label.textContent = 'Play All';
            if (offIcon) offIcon.style.display = 'block';
            if (onIcon) onIcon.style.display = 'none';
            toggle.classList.remove('active');
        }
    }

    /**
     * Play all visible streams
     */
    function playAllVisibleStreams() {
        const cards = document.querySelectorAll('.model-card[data-stream-url]');
        cards.forEach(card => {
            const streamUrl = card.dataset.streamUrl;
            if (streamUrl && isElementInViewport(card)) {
                startStream(card, streamUrl);
            }
        });
    }

    /**
     * Stop all active streams
     */
    function stopAllStreams() {
        activeStreams.forEach((data, card) => {
            stopStream(card);
        });
    }

    /**
     * Start streaming on a card
     */
    function startStream(card, streamUrl) {
        if (!streamUrl || activeStreams.has(card) || failedStreams.has(streamUrl)) return;

        const video = card.querySelector('.model-card-video');
        if (!video) return;

        // Loading timeout
        const loadTimeout = setTimeout(() => {
            if (!card.classList.contains('stream-playing')) {
                handleStreamError(card, streamUrl);
            }
        }, 8000);

        if (typeof Hls !== 'undefined' && Hls.isSupported()) {
            const hls = new Hls({
                maxBufferLength: 10,
                maxMaxBufferLength: 20,
                startLevel: 0,
                capLevelToPlayerSize: true
            });
            
            hls.loadSource(streamUrl);
            hls.attachMedia(video);
            
            hls.on(Hls.Events.MANIFEST_PARSED, () => {
                clearTimeout(loadTimeout);
                video.play().catch(() => handleStreamError(card, streamUrl));
                card.classList.add('stream-playing');
            });

            hls.on(Hls.Events.ERROR, (event, data) => {
                if (data.fatal) {
                    clearTimeout(loadTimeout);
                    handleStreamError(card, streamUrl);
                }
            });

            activeStreams.set(card, { hls, timeout: loadTimeout });
        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
            // Safari native HLS
            video.src = streamUrl;
            video.play().catch(() => handleStreamError(card, streamUrl));
            card.classList.add('stream-playing');
            activeStreams.set(card, { hls: null, timeout: loadTimeout });
        }
    }

    /**
     * Handle stream error
     */
    function handleStreamError(card, streamUrl) {
        failedStreams.add(streamUrl);
        stopStream(card);
        card.classList.add('stream-failed');
    }

    /**
     * Stop streaming on a card
     */
    function stopStream(card) {
        const streamData = activeStreams.get(card);
        if (streamData) {
            if (streamData.hls) streamData.hls.destroy();
            if (streamData.timeout) clearTimeout(streamData.timeout);
        }
        
        const video = card.querySelector('.model-card-video');
        if (video) {
            video.pause();
            video.src = '';
            video.load();
        }
        
        card.classList.remove('stream-playing');
        activeStreams.delete(card);
    }

    /**
     * Check if element is in viewport
     */
    function isElementInViewport(el) {
        const rect = el.getBoundingClientRect();
        return rect.top < window.innerHeight && rect.bottom > 0;
    }

    /**
     * Setup hover events for preview on hover
     */
    function setupHoverEvents() {
        document.addEventListener('mouseenter', (e) => {
            const card = e.target.closest('.model-card');
            if (!card || allPreviewsPlaying) return;
            
            const streamUrl = card.dataset.streamUrl;
            if (!streamUrl) return;
            
            hoverTimeout = setTimeout(() => startStream(card, streamUrl), 500);
        }, true);

        document.addEventListener('mouseleave', (e) => {
            const card = e.target.closest('.model-card');
            if (!card || allPreviewsPlaying) return;
            
            if (hoverTimeout) clearTimeout(hoverTimeout);
            stopStream(card);
        }, true);
    }

    /**
     * Setup scroll handler
     */
    function setupScrollHandler() {
        window.addEventListener('scroll', () => {
            if (!allPreviewsPlaying) return;
            
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                // Stop streams that are out of view
                activeStreams.forEach((data, card) => {
                    if (!isElementInViewport(card)) {
                        stopStream(card);
                    }
                });
                // Start streams that are now visible
                playAllVisibleStreams();
            }, 200);
        });
    }

    /**
     * Setup infinite scroll observer to start streams on new cards
     */
    function setupInfiniteScrollObserver() {
        // Watch for new model cards being added
        const observer = new MutationObserver((mutations) => {
            if (!allPreviewsPlaying) return;
            
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        const cards = node.classList?.contains('model-card') 
                            ? [node] 
                            : node.querySelectorAll?.('.model-card[data-stream-url]') || [];
                        
                        cards.forEach(card => {
                            const streamUrl = card.dataset?.streamUrl;
                            if (streamUrl && isElementInViewport(card)) {
                                setTimeout(() => startStream(card, streamUrl), 100);
                            }
                        });
                    }
                });
            });
        });

        const grid = document.getElementById('models-grid');
        if (grid) {
            observer.observe(grid, { childList: true, subtree: true });
        }
    }

    // Public API
    return {
        init,
        toggleAutoplay,
        toggleAllPreviews,
        startStream,
        stopStream,
        playAllVisibleStreams,
        stopAllStreams,
        isPlaying: () => allPreviewsPlaying
    };
})();

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    StreamPreviewManager.init();
});

// Global function for onclick handlers
function toggleAutoplay(enabled) {
    StreamPreviewManager.toggleAutoplay(enabled);
}

function toggleAllPreviews() {
    StreamPreviewManager.toggleAllPreviews();
}
