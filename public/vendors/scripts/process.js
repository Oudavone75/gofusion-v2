// Cache DOM elements
const percentEl = document.getElementById('percent1');
const barEl = document.getElementById('bar1');
const preLoaderEl = document.querySelector('.pre-loader');

// Use requestAnimationFrame for smooth 60fps animation
let progress = 0;
const duration = 800; // Fixed fast duration (0.8s)
const startTime = performance.now();

function animate(currentTime) {
    const elapsed = currentTime - startTime;
    const progressRatio = Math.min(elapsed / duration, 1);

    // Smooth easing function for better visual appeal
    const easedProgress = progressRatio < 0.5
        ? 2 * progressRatio * progressRatio
        : 1 - Math.pow(-2 * progressRatio + 2, 3) / 2;

    progress = Math.floor(easedProgress * 100);

    // Batch DOM updates
    percentEl.textContent = progress + '%';
    barEl.style.width = progress + '%';

    if (progressRatio < 1) {
        requestAnimationFrame(animate);
    } else {
        // Hide loader immediately when done
        preLoaderEl.style.display = 'none';
    }
}

// Start animation immediately
requestAnimationFrame(animate);
