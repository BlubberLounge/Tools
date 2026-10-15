/*!
 * Theme toggler for DaisyUI themes
 * Switches between 'corporate' (light) and 'business' (dark)
 */

(() => {
    'use strict'

    const LIGHT_THEME = 'corporate'
    const DARK_THEME = 'business'

    const getStoredTheme = () => localStorage.getItem('theme')

    const getPreferredTheme = () => getStoredTheme() || 'auto'

    const getEffectiveTheme = (theme) => {
        if (theme === 'auto') {
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
        }
        return theme
    }

    const setTheme = (theme) => {
        const effective = getEffectiveTheme(theme)
        const daisyTheme = effective === 'dark' ? DARK_THEME : LIGHT_THEME

        document.documentElement.setAttribute('data-theme', daisyTheme)

        // Update logo
        const logo = document.querySelector('.nav-brand')
        if (logo) {
            logo.src = effective === 'dark'
                ? 'https://media.blubber-lounge.de/images/blubber_lounge_rebrand_try_white_optimized.svg'
                : 'https://media.blubber-lounge.de/project/bl/blubber_lounge_rebrand_try.svg'
        }
    }

    const showActiveTheme = (theme) => {
        document.querySelectorAll('[data-theme-value]').forEach(el => {
            el.classList.remove('active', 'btn-primary')
        })
        const active = document.querySelector(`[data-theme-value="${theme}"]`)
        if (active) {
            active.classList.add('active', 'btn-primary')
        }
    }

    // Set immediately to prevent flash
    setTheme(getPreferredTheme())

    // Listen for system changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        const current = getStoredTheme()
        if (!current || current === 'auto') {
            setTheme('auto')
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
