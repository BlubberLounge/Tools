/*!
 * Color mode toggler for Bootstrap's docs (https://getbootstrap.com/)
 * Copyright 2011-2022 The Bootstrap Authors
 * Licensed under the Creative Commons Attribution 3.0 Unported License.
 *
 * Modified.
 */

(() =>
{
    'use strict'

    const storedTheme = localStorage.getItem('theme')

    const getPreferredTheme = () =>
    {
      if (storedTheme)
        return storedTheme

      return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
    }

    const setTheme = function (theme)
    {

      if (theme === 'dark' || (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        // Dark Mode
        document.documentElement.setAttribute('data-bs-theme', 'dark')
      } else {
        // Light Mode
        document.documentElement.setAttribute('data-bs-theme', theme)
      }
    }

    var setTheme00 = function (theme)
    {
      var a = document.querySelector('.nav-brand');
      if (theme === 'dark' || (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        // Dark Mode
        document.documentElement.setAttribute('data-bs-theme', 'dark')
        a.src ='https://media.maximilian-mewes.de/project/bl/blubber_lounge_rebrand_try_white_optimized.svg'
      } else {
        // Light Mode
        document.documentElement.setAttribute('data-bs-theme', theme)
        a.src ='https://media.maximilian-mewes.de/project/bl/blubber_lounge_rebrand_try.svg'
      }
    }

    setTheme(getPreferredTheme())

    const showActiveTheme = theme =>
    {
      const btnToActive = document.querySelector(`[data-bs-theme-value="${theme}"]`)

      document.querySelectorAll('[data-bs-theme-value]').forEach(element =>
      {
        element.classList.remove('active')
      })

      btnToActive.classList.add('active')
    }

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () =>
    {
      if (storedTheme !== 'light' || storedTheme !== 'dark') {
        setTheme00(getPreferredTheme())
      }
    })

    window.addEventListener('DOMContentLoaded', () =>
    {
      showActiveTheme(getPreferredTheme())

      document.querySelectorAll('[data-bs-theme-value]')
        .forEach(toggle => {
          toggle.addEventListener('click', () =>
          {
            const theme = toggle.getAttribute('data-bs-theme-value')
            localStorage.setItem('theme', theme)
            setTheme00(theme)
            showActiveTheme(theme)
          })
        })
    })
  })()
