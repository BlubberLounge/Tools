/*
 * ATTENTION: An "eval-source-map" devtool has been used.
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file with attached SourceMaps in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/js/dark-mode.js":
/*!***********************************!*\
  !*** ./resources/js/dark-mode.js ***!
  \***********************************/
/***/ (() => {

eval("/*!\r\n * Color mode toggler for Bootstrap's docs (https://getbootstrap.com/)\r\n * Copyright 2011-2022 The Bootstrap Authors\r\n * Licensed under the Creative Commons Attribution 3.0 Unported License.\r\n * \r\n * Modified.\r\n */\n\n(function () {\n  'use strict';\n\n  var storedTheme = localStorage.getItem('theme');\n  var getPreferredTheme = function getPreferredTheme() {\n    if (storedTheme) {\n      return storedTheme;\n    }\n    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';\n  };\n  var setTheme = function setTheme(theme) {\n    if (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) {\n      // Dark Mode\n      document.documentElement.setAttribute('data-bs-theme', 'dark');\n    } else {\n      // Light Mode\n      document.documentElement.setAttribute('data-bs-theme', theme);\n    }\n  };\n  var setTheme00 = function setTheme00(theme) {\n    var a;\n    if (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) {\n      // Dark Mode\n      document.documentElement.setAttribute('data-bs-theme', 'dark');\n      a = document.getElementById('navBrand').src = 'https://media.maximilian-mewes.de/project/bl/blubber_lounge_rebrand_try.svg';\n    } else {\n      // Light Mode\n      document.documentElement.setAttribute('data-bs-theme', theme);\n      a = document.getElementById('navBrand').src = 'https://media.maximilian-mewes.de/project/bl/blubber_lounge_rebrand_try_white.svg';\n      console.log(a);\n    }\n    console.log(a);\n  };\n  setTheme(getPreferredTheme());\n  var showActiveTheme = function showActiveTheme(theme) {\n    var activeThemeIcon = document.querySelector('.theme-icon-active');\n    var btnToActive = document.querySelector(\"[data-bs-theme-value=\\\"\".concat(theme, \"\\\"]\"));\n    var svgOfActiveBtn = btnToActive.querySelector('i').getAttribute('data-bs-theme-icon');\n    document.querySelectorAll('[data-bs-theme-value]').forEach(function (element) {\n      element.classList.remove('active');\n    });\n    btnToActive.classList.add('active');\n    activeThemeIcon.setAttribute('data-bs-theme-icon', svgOfActiveBtn);\n    activeThemeIcon.classList.remove('fa-sun', 'fa-moon', 'fa-circle-half-stroke');\n    activeThemeIcon.classList.add(svgOfActiveBtn);\n  };\n  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {\n    if (storedTheme !== 'light' || storedTheme !== 'dark') {\n      setTheme00(getPreferredTheme());\n    }\n  });\n  window.addEventListener('DOMContentLoaded', function () {\n    showActiveTheme(getPreferredTheme());\n    document.querySelectorAll('[data-bs-theme-value]').forEach(function (toggle) {\n      toggle.addEventListener('click', function () {\n        var theme = toggle.getAttribute('data-bs-theme-value');\n        localStorage.setItem('theme', theme);\n        setTheme00(theme);\n        showActiveTheme(theme);\n      });\n    });\n  });\n})();//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJuYW1lcyI6WyJzdG9yZWRUaGVtZSIsImxvY2FsU3RvcmFnZSIsImdldEl0ZW0iLCJnZXRQcmVmZXJyZWRUaGVtZSIsIndpbmRvdyIsIm1hdGNoTWVkaWEiLCJtYXRjaGVzIiwic2V0VGhlbWUiLCJ0aGVtZSIsImRvY3VtZW50IiwiZG9jdW1lbnRFbGVtZW50Iiwic2V0QXR0cmlidXRlIiwic2V0VGhlbWUwMCIsImEiLCJnZXRFbGVtZW50QnlJZCIsInNyYyIsImNvbnNvbGUiLCJsb2ciLCJzaG93QWN0aXZlVGhlbWUiLCJhY3RpdmVUaGVtZUljb24iLCJxdWVyeVNlbGVjdG9yIiwiYnRuVG9BY3RpdmUiLCJzdmdPZkFjdGl2ZUJ0biIsImdldEF0dHJpYnV0ZSIsInF1ZXJ5U2VsZWN0b3JBbGwiLCJmb3JFYWNoIiwiZWxlbWVudCIsImNsYXNzTGlzdCIsInJlbW92ZSIsImFkZCIsImFkZEV2ZW50TGlzdGVuZXIiLCJ0b2dnbGUiLCJzZXRJdGVtIl0sInNvdXJjZXMiOlsid2VicGFjazovLy8uL3Jlc291cmNlcy9qcy9kYXJrLW1vZGUuanM/MjE3NCJdLCJzb3VyY2VzQ29udGVudCI6WyIvKiFcclxuICogQ29sb3IgbW9kZSB0b2dnbGVyIGZvciBCb290c3RyYXAncyBkb2NzIChodHRwczovL2dldGJvb3RzdHJhcC5jb20vKVxyXG4gKiBDb3B5cmlnaHQgMjAxMS0yMDIyIFRoZSBCb290c3RyYXAgQXV0aG9yc1xyXG4gKiBMaWNlbnNlZCB1bmRlciB0aGUgQ3JlYXRpdmUgQ29tbW9ucyBBdHRyaWJ1dGlvbiAzLjAgVW5wb3J0ZWQgTGljZW5zZS5cclxuICogXHJcbiAqIE1vZGlmaWVkLlxyXG4gKi9cclxuXHJcbigoKSA9PlxyXG57XHJcbiAgICAndXNlIHN0cmljdCdcclxuICBcclxuICAgIGNvbnN0IHN0b3JlZFRoZW1lID0gbG9jYWxTdG9yYWdlLmdldEl0ZW0oJ3RoZW1lJylcclxuICBcclxuICAgIGNvbnN0IGdldFByZWZlcnJlZFRoZW1lID0gKCkgPT5cclxuICAgIHtcclxuICAgICAgaWYgKHN0b3JlZFRoZW1lKSB7XHJcbiAgICAgICAgcmV0dXJuIHN0b3JlZFRoZW1lXHJcbiAgICAgIH1cclxuICBcclxuICAgICAgcmV0dXJuIHdpbmRvdy5tYXRjaE1lZGlhKCcocHJlZmVycy1jb2xvci1zY2hlbWU6IGRhcmspJykubWF0Y2hlcyA/ICdkYXJrJyA6ICdsaWdodCdcclxuICAgIH1cclxuICBcclxuICAgIGNvbnN0IHNldFRoZW1lID0gZnVuY3Rpb24gKHRoZW1lKVxyXG4gICAge1xyXG4gICAgICBcclxuICAgICAgaWYgKHRoZW1lID09PSAnYXV0bycgJiYgd2luZG93Lm1hdGNoTWVkaWEoJyhwcmVmZXJzLWNvbG9yLXNjaGVtZTogZGFyayknKS5tYXRjaGVzKSB7XHJcbiAgICAgICAgLy8gRGFyayBNb2RlXHJcbiAgICAgICAgZG9jdW1lbnQuZG9jdW1lbnRFbGVtZW50LnNldEF0dHJpYnV0ZSgnZGF0YS1icy10aGVtZScsICdkYXJrJylcclxuICAgICAgfSBlbHNlIHtcclxuICAgICAgICAvLyBMaWdodCBNb2RlXHJcbiAgICAgICAgZG9jdW1lbnQuZG9jdW1lbnRFbGVtZW50LnNldEF0dHJpYnV0ZSgnZGF0YS1icy10aGVtZScsIHRoZW1lKVxyXG4gICAgICB9XHJcbiAgICB9XHJcblxyXG4gICAgdmFyIHNldFRoZW1lMDAgPSBmdW5jdGlvbiAodGhlbWUpXHJcbiAgICB7XHJcbiAgICAgIHZhciBhXHJcbiAgICAgIGlmICh0aGVtZSA9PT0gJ2F1dG8nICYmIHdpbmRvdy5tYXRjaE1lZGlhKCcocHJlZmVycy1jb2xvci1zY2hlbWU6IGRhcmspJykubWF0Y2hlcykge1xyXG4gICAgICAgIC8vIERhcmsgTW9kZVxyXG4gICAgICAgIGRvY3VtZW50LmRvY3VtZW50RWxlbWVudC5zZXRBdHRyaWJ1dGUoJ2RhdGEtYnMtdGhlbWUnLCAnZGFyaycpXHJcbiAgICAgICAgYSA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCduYXZCcmFuZCcpLnNyYyA9J2h0dHBzOi8vbWVkaWEubWF4aW1pbGlhbi1tZXdlcy5kZS9wcm9qZWN0L2JsL2JsdWJiZXJfbG91bmdlX3JlYnJhbmRfdHJ5LnN2ZydcclxuICAgICAgfSBlbHNlIHtcclxuICAgICAgICAvLyBMaWdodCBNb2RlXHJcbiAgICAgICAgZG9jdW1lbnQuZG9jdW1lbnRFbGVtZW50LnNldEF0dHJpYnV0ZSgnZGF0YS1icy10aGVtZScsIHRoZW1lKSAgICAgICAgXHJcbiAgICAgICAgYSAgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnbmF2QnJhbmQnKS5zcmMgPSdodHRwczovL21lZGlhLm1heGltaWxpYW4tbWV3ZXMuZGUvcHJvamVjdC9ibC9ibHViYmVyX2xvdW5nZV9yZWJyYW5kX3RyeV93aGl0ZS5zdmcnXHJcbiAgICAgICAgY29uc29sZS5sb2coYSk7XHJcbiAgICAgIH1cclxuICAgICAgY29uc29sZS5sb2coYSk7XHJcbiAgICB9XHJcbiAgXHJcbiAgICBzZXRUaGVtZShnZXRQcmVmZXJyZWRUaGVtZSgpKVxyXG4gIFxyXG4gICAgY29uc3Qgc2hvd0FjdGl2ZVRoZW1lID0gdGhlbWUgPT5cclxuICAgIHtcclxuICAgICAgY29uc3QgYWN0aXZlVGhlbWVJY29uID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLnRoZW1lLWljb24tYWN0aXZlJylcclxuICAgICAgY29uc3QgYnRuVG9BY3RpdmUgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKGBbZGF0YS1icy10aGVtZS12YWx1ZT1cIiR7dGhlbWV9XCJdYClcclxuICAgICAgY29uc3Qgc3ZnT2ZBY3RpdmVCdG4gPSBidG5Ub0FjdGl2ZS5xdWVyeVNlbGVjdG9yKCdpJykuZ2V0QXR0cmlidXRlKCdkYXRhLWJzLXRoZW1lLWljb24nKVxyXG4gIFxyXG4gICAgICBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCdbZGF0YS1icy10aGVtZS12YWx1ZV0nKS5mb3JFYWNoKGVsZW1lbnQgPT5cclxuICAgICAge1xyXG4gICAgICAgIGVsZW1lbnQuY2xhc3NMaXN0LnJlbW92ZSgnYWN0aXZlJylcclxuICAgICAgfSlcclxuICBcclxuICAgICAgYnRuVG9BY3RpdmUuY2xhc3NMaXN0LmFkZCgnYWN0aXZlJylcclxuICAgICAgYWN0aXZlVGhlbWVJY29uLnNldEF0dHJpYnV0ZSgnZGF0YS1icy10aGVtZS1pY29uJywgc3ZnT2ZBY3RpdmVCdG4pXHJcbiAgICAgIGFjdGl2ZVRoZW1lSWNvbi5jbGFzc0xpc3QucmVtb3ZlKCdmYS1zdW4nLCAnZmEtbW9vbicsICdmYS1jaXJjbGUtaGFsZi1zdHJva2UnKVxyXG4gICAgICBhY3RpdmVUaGVtZUljb24uY2xhc3NMaXN0LmFkZChzdmdPZkFjdGl2ZUJ0bilcclxuICAgIH1cclxuICBcclxuICAgIHdpbmRvdy5tYXRjaE1lZGlhKCcocHJlZmVycy1jb2xvci1zY2hlbWU6IGRhcmspJykuYWRkRXZlbnRMaXN0ZW5lcignY2hhbmdlJywgKCkgPT5cclxuICAgIHtcclxuICAgICAgaWYgKHN0b3JlZFRoZW1lICE9PSAnbGlnaHQnIHx8IHN0b3JlZFRoZW1lICE9PSAnZGFyaycpIHtcclxuICAgICAgICBzZXRUaGVtZTAwKGdldFByZWZlcnJlZFRoZW1lKCkpXHJcbiAgICAgIH1cclxuICAgIH0pXHJcbiAgXHJcbiAgICB3aW5kb3cuYWRkRXZlbnRMaXN0ZW5lcignRE9NQ29udGVudExvYWRlZCcsICgpID0+XHJcbiAgICB7XHJcbiAgICAgIHNob3dBY3RpdmVUaGVtZShnZXRQcmVmZXJyZWRUaGVtZSgpKVxyXG4gIFxyXG4gICAgICBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCdbZGF0YS1icy10aGVtZS12YWx1ZV0nKVxyXG4gICAgICAgIC5mb3JFYWNoKHRvZ2dsZSA9PiB7XHJcbiAgICAgICAgICB0b2dnbGUuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCAoKSA9PlxyXG4gICAgICAgICAge1xyXG4gICAgICAgICAgICBjb25zdCB0aGVtZSA9IHRvZ2dsZS5nZXRBdHRyaWJ1dGUoJ2RhdGEtYnMtdGhlbWUtdmFsdWUnKVxyXG4gICAgICAgICAgICBsb2NhbFN0b3JhZ2Uuc2V0SXRlbSgndGhlbWUnLCB0aGVtZSlcclxuICAgICAgICAgICAgc2V0VGhlbWUwMCh0aGVtZSlcclxuICAgICAgICAgICAgc2hvd0FjdGl2ZVRoZW1lKHRoZW1lKVxyXG4gICAgICAgICAgfSlcclxuICAgICAgICB9KVxyXG4gICAgfSlcclxuICB9KSgpIl0sIm1hcHBpbmdzIjoiQUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQSxDQUFDLFlBQ0Q7RUFDSSxZQUFZOztFQUVaLElBQU1BLFdBQVcsR0FBR0MsWUFBWSxDQUFDQyxPQUFPLENBQUMsT0FBTyxDQUFDO0VBRWpELElBQU1DLGlCQUFpQixHQUFHLFNBQXBCQSxpQkFBaUIsR0FDdkI7SUFDRSxJQUFJSCxXQUFXLEVBQUU7TUFDZixPQUFPQSxXQUFXO0lBQ3BCO0lBRUEsT0FBT0ksTUFBTSxDQUFDQyxVQUFVLENBQUMsOEJBQThCLENBQUMsQ0FBQ0MsT0FBTyxHQUFHLE1BQU0sR0FBRyxPQUFPO0VBQ3JGLENBQUM7RUFFRCxJQUFNQyxRQUFRLEdBQUcsU0FBWEEsUUFBUSxDQUFhQyxLQUFLLEVBQ2hDO0lBRUUsSUFBSUEsS0FBSyxLQUFLLE1BQU0sSUFBSUosTUFBTSxDQUFDQyxVQUFVLENBQUMsOEJBQThCLENBQUMsQ0FBQ0MsT0FBTyxFQUFFO01BQ2pGO01BQ0FHLFFBQVEsQ0FBQ0MsZUFBZSxDQUFDQyxZQUFZLENBQUMsZUFBZSxFQUFFLE1BQU0sQ0FBQztJQUNoRSxDQUFDLE1BQU07TUFDTDtNQUNBRixRQUFRLENBQUNDLGVBQWUsQ0FBQ0MsWUFBWSxDQUFDLGVBQWUsRUFBRUgsS0FBSyxDQUFDO0lBQy9EO0VBQ0YsQ0FBQztFQUVELElBQUlJLFVBQVUsR0FBRyxTQUFiQSxVQUFVLENBQWFKLEtBQUssRUFDaEM7SUFDRSxJQUFJSyxDQUFDO0lBQ0wsSUFBSUwsS0FBSyxLQUFLLE1BQU0sSUFBSUosTUFBTSxDQUFDQyxVQUFVLENBQUMsOEJBQThCLENBQUMsQ0FBQ0MsT0FBTyxFQUFF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\n//# sourceURL=webpack-internal:///./resources/js/dark-mode.js\n");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval-source-map devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./resources/js/dark-mode.js"]();
/******/ 	
/******/ })()
;