import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

/**
 * Dark Mode Component untuk Alpine.js
 * - Membaca preferensi dari localStorage
 * - Fallback ke system preference (prefers-color-scheme)
 * - Toggle manual menyimpan ke localStorage
 */
document.addEventListener('alpine:init', () => {
    Alpine.data('darkMode', () => ({
        isDark: false,

        init() {
            // Cek localStorage terlebih dahulu
            const saved = localStorage.getItem('theme');

            if (saved === 'dark') {
                this.isDark = true;
            } else if (saved === 'light') {
                this.isDark = false;
            } else {
                // Fallback ke system preference
                this.isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            }

            // Watch system preference changes (hanya jika user belum set manual)
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (!localStorage.getItem('theme')) {
                    this.isDark = e.matches;
                }
            });
        },

        toggle() {
            this.isDark = !this.isDark;
            localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
        },
    }));
});

Alpine.start();