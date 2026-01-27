/*!
 * Color mode toggler for Tailwind CSS dark mode
 * Modified from Bootstrap's docs
 */

(() => {
    'use strict'

    const storedTheme = localStorage.getItem('theme')

    const getPreferredTheme = () => {
        if (storedTheme) {
            return storedTheme
        }
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
    }

    const setTheme = (theme) => {
        const isDark = theme === 'dark' || (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);

        if (isDark) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }

        // Update logo based on theme
        const logo = document.querySelector('.nav-brand');
        if (logo) {
            if (isDark) {
                logo.src = 'https://media.blubber-lounge.de/images/blubber_lounge_rebrand_try_white_optimized.svg'
            } else {
                logo.src = 'https://media.blubber-lounge.de/project/bl/blubber_lounge_rebrand_try.svg'
            }
        }
    }

    // Set initial theme immediately to prevent flash
    setTheme(getPreferredTheme())

    const showActiveTheme = (theme) => {
        const btnToActive = document.querySelector(`[data-theme-value="${theme}"]`)

        document.querySelectorAll('[data-theme-value]').forEach(element => {
            element.classList.remove('active')
        })

        if (btnToActive) {
            btnToActive.classList.add('active')
        }
    }

    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        const currentTheme = localStorage.getItem('theme')
        if (currentTheme !== 'light' && currentTheme !== 'dark') {
            setTheme(getPreferredTheme())
        }
    })

    window.addEventListener('DOMContentLoaded', () => {
        showActiveTheme(getPreferredTheme())

        document.querySelectorAll('[data-theme-value]').forEach(toggle => {
            toggle.addEventListener('click', () => {
                const theme = toggle.getAttribute('data-theme-value')
                localStorage.setItem('theme', theme)
                setTheme(theme)
                showActiveTheme(theme)
            })
        })
    })
})()
