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

eval("/*!\r\n * Color mode toggler for Bootstrap's docs (https://getbootstrap.com/)\r\n * Copyright 2011-2022 The Bootstrap Authors\r\n * Licensed under the Creative Commons Attribution 3.0 Unported License.\r\n * \r\n * Modified.\r\n */\n\n(function () {\n  'use strict';\n\n  var storedTheme = localStorage.getItem('theme');\n  var getPreferredTheme = function getPreferredTheme() {\n    if (storedTheme) {\n      return storedTheme;\n    }\n    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';\n  };\n  var setTheme = function setTheme(theme) {\n    if (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) {\n      document.documentElement.setAttribute('data-bs-theme', 'dark');\n    } else {\n      document.documentElement.setAttribute('data-bs-theme', theme);\n    }\n  };\n  setTheme(getPreferredTheme());\n  var showActiveTheme = function showActiveTheme(theme) {\n    var activeThemeIcon = document.querySelector('.theme-icon-active');\n    var btnToActive = document.querySelector(\"[data-bs-theme-value=\\\"\".concat(theme, \"\\\"]\"));\n    var svgOfActiveBtn = btnToActive.querySelector('i').getAttribute('data-bs-theme-icon');\n    document.querySelectorAll('[data-bs-theme-value]').forEach(function (element) {\n      element.classList.remove('active');\n    });\n    btnToActive.classList.add('active');\n    activeThemeIcon.setAttribute('data-bs-theme-icon', svgOfActiveBtn);\n    activeThemeIcon.classList.remove('fa-sun', 'fa-moon', 'fa-circle-half-stroke');\n    activeThemeIcon.classList.add(svgOfActiveBtn);\n  };\n  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {\n    if (storedTheme !== 'light' || storedTheme !== 'dark') {\n      setTheme(getPreferredTheme());\n    }\n  });\n  window.addEventListener('DOMContentLoaded', function () {\n    showActiveTheme(getPreferredTheme());\n    document.querySelectorAll('[data-bs-theme-value]').forEach(function (toggle) {\n      toggle.addEventListener('click', function () {\n        var theme = toggle.getAttribute('data-bs-theme-value');\n        localStorage.setItem('theme', theme);\n        setTheme(theme);\n        showActiveTheme(theme);\n      });\n    });\n  });\n})();//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJuYW1lcyI6WyJzdG9yZWRUaGVtZSIsImxvY2FsU3RvcmFnZSIsImdldEl0ZW0iLCJnZXRQcmVmZXJyZWRUaGVtZSIsIndpbmRvdyIsIm1hdGNoTWVkaWEiLCJtYXRjaGVzIiwic2V0VGhlbWUiLCJ0aGVtZSIsImRvY3VtZW50IiwiZG9jdW1lbnRFbGVtZW50Iiwic2V0QXR0cmlidXRlIiwic2hvd0FjdGl2ZVRoZW1lIiwiYWN0aXZlVGhlbWVJY29uIiwicXVlcnlTZWxlY3RvciIsImJ0blRvQWN0aXZlIiwic3ZnT2ZBY3RpdmVCdG4iLCJnZXRBdHRyaWJ1dGUiLCJxdWVyeVNlbGVjdG9yQWxsIiwiZm9yRWFjaCIsImVsZW1lbnQiLCJjbGFzc0xpc3QiLCJyZW1vdmUiLCJhZGQiLCJhZGRFdmVudExpc3RlbmVyIiwidG9nZ2xlIiwic2V0SXRlbSJdLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9yZXNvdXJjZXMvanMvZGFyay1tb2RlLmpzPzIxNzQiXSwic291cmNlc0NvbnRlbnQiOlsiLyohXHJcbiAqIENvbG9yIG1vZGUgdG9nZ2xlciBmb3IgQm9vdHN0cmFwJ3MgZG9jcyAoaHR0cHM6Ly9nZXRib290c3RyYXAuY29tLylcclxuICogQ29weXJpZ2h0IDIwMTEtMjAyMiBUaGUgQm9vdHN0cmFwIEF1dGhvcnNcclxuICogTGljZW5zZWQgdW5kZXIgdGhlIENyZWF0aXZlIENvbW1vbnMgQXR0cmlidXRpb24gMy4wIFVucG9ydGVkIExpY2Vuc2UuXHJcbiAqIFxyXG4gKiBNb2RpZmllZC5cclxuICovXHJcblxyXG4oKCkgPT4ge1xyXG4gICAgJ3VzZSBzdHJpY3QnXHJcbiAgXHJcbiAgICBjb25zdCBzdG9yZWRUaGVtZSA9IGxvY2FsU3RvcmFnZS5nZXRJdGVtKCd0aGVtZScpXHJcbiAgXHJcbiAgICBjb25zdCBnZXRQcmVmZXJyZWRUaGVtZSA9ICgpID0+IHtcclxuICAgICAgaWYgKHN0b3JlZFRoZW1lKSB7XHJcbiAgICAgICAgcmV0dXJuIHN0b3JlZFRoZW1lXHJcbiAgICAgIH1cclxuICBcclxuICAgICAgcmV0dXJuIHdpbmRvdy5tYXRjaE1lZGlhKCcocHJlZmVycy1jb2xvci1zY2hlbWU6IGRhcmspJykubWF0Y2hlcyA/ICdkYXJrJyA6ICdsaWdodCdcclxuICAgIH1cclxuICBcclxuICAgIGNvbnN0IHNldFRoZW1lID0gZnVuY3Rpb24gKHRoZW1lKSB7XHJcbiAgICAgIGlmICh0aGVtZSA9PT0gJ2F1dG8nICYmIHdpbmRvdy5tYXRjaE1lZGlhKCcocHJlZmVycy1jb2xvci1zY2hlbWU6IGRhcmspJykubWF0Y2hlcykge1xyXG4gICAgICAgIGRvY3VtZW50LmRvY3VtZW50RWxlbWVudC5zZXRBdHRyaWJ1dGUoJ2RhdGEtYnMtdGhlbWUnLCAnZGFyaycpXHJcbiAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgZG9jdW1lbnQuZG9jdW1lbnRFbGVtZW50LnNldEF0dHJpYnV0ZSgnZGF0YS1icy10aGVtZScsIHRoZW1lKVxyXG4gICAgICB9XHJcbiAgICB9XHJcbiAgXHJcbiAgICBzZXRUaGVtZShnZXRQcmVmZXJyZWRUaGVtZSgpKVxyXG4gIFxyXG4gICAgY29uc3Qgc2hvd0FjdGl2ZVRoZW1lID0gdGhlbWUgPT4ge1xyXG4gICAgICBjb25zdCBhY3RpdmVUaGVtZUljb24gPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcudGhlbWUtaWNvbi1hY3RpdmUnKVxyXG4gICAgICBjb25zdCBidG5Ub0FjdGl2ZSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoYFtkYXRhLWJzLXRoZW1lLXZhbHVlPVwiJHt0aGVtZX1cIl1gKVxyXG4gICAgICBjb25zdCBzdmdPZkFjdGl2ZUJ0biA9IGJ0blRvQWN0aXZlLnF1ZXJ5U2VsZWN0b3IoJ2knKS5nZXRBdHRyaWJ1dGUoJ2RhdGEtYnMtdGhlbWUtaWNvbicpXHJcbiAgXHJcbiAgICAgIGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJ1tkYXRhLWJzLXRoZW1lLXZhbHVlXScpLmZvckVhY2goZWxlbWVudCA9PiB7XHJcbiAgICAgICAgZWxlbWVudC5jbGFzc0xpc3QucmVtb3ZlKCdhY3RpdmUnKVxyXG4gICAgICB9KVxyXG4gIFxyXG4gICAgICBidG5Ub0FjdGl2ZS5jbGFzc0xpc3QuYWRkKCdhY3RpdmUnKVxyXG4gICAgICBhY3RpdmVUaGVtZUljb24uc2V0QXR0cmlidXRlKCdkYXRhLWJzLXRoZW1lLWljb24nLCBzdmdPZkFjdGl2ZUJ0bilcclxuICAgICAgYWN0aXZlVGhlbWVJY29uLmNsYXNzTGlzdC5yZW1vdmUoJ2ZhLXN1bicsICdmYS1tb29uJywgJ2ZhLWNpcmNsZS1oYWxmLXN0cm9rZScpXHJcbiAgICAgIGFjdGl2ZVRoZW1lSWNvbi5jbGFzc0xpc3QuYWRkKHN2Z09mQWN0aXZlQnRuKVxyXG4gICAgfVxyXG4gIFxyXG4gICAgd2luZG93Lm1hdGNoTWVkaWEoJyhwcmVmZXJzLWNvbG9yLXNjaGVtZTogZGFyayknKS5hZGRFdmVudExpc3RlbmVyKCdjaGFuZ2UnLCAoKSA9PiB7XHJcbiAgICAgIGlmIChzdG9yZWRUaGVtZSAhPT0gJ2xpZ2h0JyB8fCBzdG9yZWRUaGVtZSAhPT0gJ2RhcmsnKSB7XHJcbiAgICAgICAgc2V0VGhlbWUoZ2V0UHJlZmVycmVkVGhlbWUoKSlcclxuICAgICAgfVxyXG4gICAgfSlcclxuICBcclxuICAgIHdpbmRvdy5hZGRFdmVudExpc3RlbmVyKCdET01Db250ZW50TG9hZGVkJywgKCkgPT4ge1xyXG4gICAgICBzaG93QWN0aXZlVGhlbWUoZ2V0UHJlZmVycmVkVGhlbWUoKSlcclxuICBcclxuICAgICAgZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnW2RhdGEtYnMtdGhlbWUtdmFsdWVdJylcclxuICAgICAgICAuZm9yRWFjaCh0b2dnbGUgPT4ge1xyXG4gICAgICAgICAgdG9nZ2xlLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgKCkgPT4ge1xyXG4gICAgICAgICAgICBjb25zdCB0aGVtZSA9IHRvZ2dsZS5nZXRBdHRyaWJ1dGUoJ2RhdGEtYnMtdGhlbWUtdmFsdWUnKVxyXG4gICAgICAgICAgICBsb2NhbFN0b3JhZ2Uuc2V0SXRlbSgndGhlbWUnLCB0aGVtZSlcclxuICAgICAgICAgICAgc2V0VGhlbWUodGhlbWUpXHJcbiAgICAgICAgICAgIHNob3dBY3RpdmVUaGVtZSh0aGVtZSlcclxuICAgICAgICAgIH0pXHJcbiAgICAgICAgfSlcclxuICAgIH0pXHJcbiAgfSkoKSJdLCJtYXBwaW5ncyI6IkFBQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUEsQ0FBQyxZQUFNO0VBQ0gsWUFBWTs7RUFFWixJQUFNQSxXQUFXLEdBQUdDLFlBQVksQ0FBQ0MsT0FBTyxDQUFDLE9BQU8sQ0FBQztFQUVqRCxJQUFNQyxpQkFBaUIsR0FBRyxTQUFwQkEsaUJBQWlCLEdBQVM7SUFDOUIsSUFBSUgsV0FBVyxFQUFFO01BQ2YsT0FBT0EsV0FBVztJQUNwQjtJQUVBLE9BQU9JLE1BQU0sQ0FBQ0MsVUFBVSxDQUFDLDhCQUE4QixDQUFDLENBQUNDLE9BQU8sR0FBRyxNQUFNLEdBQUcsT0FBTztFQUNyRixDQUFDO0VBRUQsSUFBTUMsUUFBUSxHQUFHLFNBQVhBLFFBQVEsQ0FBYUMsS0FBSyxFQUFFO0lBQ2hDLElBQUlBLEtBQUssS0FBSyxNQUFNLElBQUlKLE1BQU0sQ0FBQ0MsVUFBVSxDQUFDLDhCQUE4QixDQUFDLENBQUNDLE9BQU8sRUFBRTtNQUNqRkcsUUFBUSxDQUFDQyxlQUFlLENBQUNDLFlBQVksQ0FBQyxlQUFlLEVBQUUsTUFBTSxDQUFDO0lBQ2hFLENBQUMsTUFBTTtNQUNMRixRQUFRLENBQUNDLGVBQWUsQ0FBQ0MsWUFBWSxDQUFDLGVBQWUsRUFBRUgsS0FBSyxDQUFDO0lBQy9EO0VBQ0YsQ0FBQztFQUVERCxRQUFRLENBQUNKLGlCQUFpQixFQUFFLENBQUM7RUFFN0IsSUFBTVMsZUFBZSxHQUFHLFNBQWxCQSxlQUFlLENBQUdKLEtBQUssRUFBSTtJQUMvQixJQUFNSyxlQUFlLEdBQUdKLFFBQVEsQ0FBQ0ssYUFBYSxDQUFDLG9CQUFvQixDQUFDO0lBQ3BFLElBQU1DLFdBQVcsR0FBR04sUUFBUSxDQUFDSyxhQUFhLGtDQUEwQk4sS0FBSyxTQUFLO0lBQzlFLElBQU1RLGNBQWMsR0FBR0QsV0FBVyxDQUFDRCxhQUFhLENBQUMsR0FBRyxDQUFDLENBQUNHLFlBQVksQ0FBQyxvQkFBb0IsQ0FBQztJQUV4RlIsUUFBUSxDQUFDUyxnQkFBZ0IsQ0FBQyx1QkFBdUIsQ0FBQyxDQUFDQyxPQUFPLENBQUMsVUFBQUMsT0FBTyxFQUFJO01BQ3BFQSxPQUFPLENBQUNDLFNBQVMsQ0FBQ0MsTUFBTSxDQUFDLFFBQVEsQ0FBQztJQUNwQyxDQUFDLENBQUM7SUFFRlAsV0FBVyxDQUFDTSxTQUFTLENBQUNFLEdBQUcsQ0FBQyxRQUFRLENBQUM7SUFDbkNWLGVBQWUsQ0FBQ0YsWUFBWSxDQUFDLG9CQUFvQixFQUFFSyxjQUFjLENBQUM7SUFDbEVILGVBQWUsQ0FBQ1EsU0FBUyxDQUFDQyxNQUFNLENBQUMsUUFBUSxFQUFFLFNBQVMsRUFBRSx1QkFBdUIsQ0FBQztJQUM5RVQsZUFBZSxDQUFDUSxTQUFTLENBQUNFLEdBQUcsQ0FBQ1AsY0FBYyxDQUFDO0VBQy9DLENBQUM7RUFFRFosTUFBTSxDQUFDQyxVQUFVLENBQUMsOEJBQThCLENBQUMsQ0FBQ21CLGdCQUFnQixDQUFDLFFBQVEsRUFBRSxZQUFNO0lBQ2pGLElBQUl4QixXQUFXLEtBQUssT0FBTyxJQUFJQSxXQUFXLEtBQUssTUFBTSxFQUFFO01BQ3JETyxRQUFRLENBQUNKLGlCQUFpQixFQUFFLENBQUM7SUFDL0I7RUFDRixDQUFDLENBQUM7RUFFRkMsTUFBTSxDQUFDb0IsZ0JBQWdCLENBQUMsa0JBQWtCLEVBQUUsWUFBTTtJQUNoRFosZUFBZSxDQUFDVCxpQkFBaUIsRUFBRSxDQUFDO0lBRXBDTSxRQUFRLENBQUNTLGdCQUFnQixDQUFDLHVCQUF1QixDQUFDLENBQy9DQyxPQUFPLENBQUMsVUFBQU0sTUFBTSxFQUFJO01BQ2pCQSxNQUFNLENBQUNELGdCQUFnQixDQUFDLE9BQU8sRUFBRSxZQUFNO1FBQ3JDLElBQU1oQixLQUFLLEdBQUdpQixNQUFNLENBQUNSLFlBQVksQ0FBQyxxQkFBcUIsQ0FBQztRQUN4RGhCLFlBQVksQ0FBQ3lCLE9BQU8sQ0FBQyxPQUFPLEVBQUVsQixLQUFLLENBQUM7UUFDcENELFFBQVEsQ0FBQ0MsS0FBSyxDQUFDO1FBQ2ZJLGVBQWUsQ0FBQ0osS0FBSyxDQUFDO01BQ3hCLENBQUMsQ0FBQztJQUNKLENBQUMsQ0FBQztFQUNOLENBQUMsQ0FBQztBQUNKLENBQUMsR0FBRyIsImZpbGUiOiIuL3Jlc291cmNlcy9qcy9kYXJrLW1vZGUuanMuanMiLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///./resources/js/dark-mode.js\n");

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