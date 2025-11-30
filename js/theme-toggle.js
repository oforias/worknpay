/**
 * Theme Toggle - DISABLED
 * App now uses dark mode only
 * This file is kept for compatibility but does nothing
 */

// No-op function for compatibility
function toggleTheme() {
    // Dark mode is always on
    console.log('Theme toggle disabled - app uses dark mode only');
}

// Ensure dark mode is always active
document.addEventListener('DOMContentLoaded', function() {
    // Remove any light mode classes
    document.body.classList.remove('light-mode');
    
    // Force dark mode
    document.body.style.background = '#0A0E1A';
    document.body.style.color = 'rgba(255, 255, 255, 0.95)';
});
