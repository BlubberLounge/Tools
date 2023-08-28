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

/***/ "./resources/js/dart/game/dartResult.js":
/*!**********************************************!*\
  !*** ./resources/js/dart/game/dartResult.js ***!
  \**********************************************/
/***/ (() => {

eval("/**\n * @author Maximilian Mewes\n *\n *\n */\n\n$(function () {\n  // cool, ultra lightweight, but only supports click event\n  // let confetti = new Confetti('confetti');\n  // confetti.setCount(75);\n  // confetti.setSize(1);\n  // confetti.setPower(25);\n  // confetti.setFade(false);\n  // confetti.destroyTarget(false);\n\n  podiumConfetti();\n});\n\n/**\n *\n *\n */\nfunction podiumConfetti() {\n  var count = 300;\n  var defaults = {\n    origin: {\n      y: .35\n    }\n  };\n  var fire = function fire(particleRatio, opts) {\n    confetti(Object.assign({}, defaults, opts, {\n      particleCount: Math.floor(count * particleRatio)\n    }));\n  };\n  fire(0.25, {\n    spread: 26,\n    startVelocity: 55\n  });\n  fire(0.2, {\n    spread: 60\n  });\n  fire(0.35, {\n    spread: 100,\n    decay: 0.91,\n    scalar: 0.8\n  });\n  fire(0.1, {\n    spread: 120,\n    startVelocity: 25,\n    decay: 0.92,\n    scalar: 1.2\n  });\n  fire(0.1, {\n    spread: 120,\n    startVelocity: 45\n  });\n}//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJuYW1lcyI6WyIkIiwicG9kaXVtQ29uZmV0dGkiLCJjb3VudCIsImRlZmF1bHRzIiwib3JpZ2luIiwieSIsImZpcmUiLCJwYXJ0aWNsZVJhdGlvIiwib3B0cyIsImNvbmZldHRpIiwiT2JqZWN0IiwiYXNzaWduIiwicGFydGljbGVDb3VudCIsIk1hdGgiLCJmbG9vciIsInNwcmVhZCIsInN0YXJ0VmVsb2NpdHkiLCJkZWNheSIsInNjYWxhciJdLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9yZXNvdXJjZXMvanMvZGFydC9nYW1lL2RhcnRSZXN1bHQuanM/MTYzMyJdLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBhdXRob3IgTWF4aW1pbGlhbiBNZXdlc1xuICpcbiAqXG4gKi9cblxuXG4kKGZ1bmN0aW9uKClcbntcbiAgICAvLyBjb29sLCB1bHRyYSBsaWdodHdlaWdodCwgYnV0IG9ubHkgc3VwcG9ydHMgY2xpY2sgZXZlbnRcbiAgICAvLyBsZXQgY29uZmV0dGkgPSBuZXcgQ29uZmV0dGkoJ2NvbmZldHRpJyk7XG4gICAgLy8gY29uZmV0dGkuc2V0Q291bnQoNzUpO1xuICAgIC8vIGNvbmZldHRpLnNldFNpemUoMSk7XG4gICAgLy8gY29uZmV0dGkuc2V0UG93ZXIoMjUpO1xuICAgIC8vIGNvbmZldHRpLnNldEZhZGUoZmFsc2UpO1xuICAgIC8vIGNvbmZldHRpLmRlc3Ryb3lUYXJnZXQoZmFsc2UpO1xuXG4gICAgcG9kaXVtQ29uZmV0dGkoKTtcbn0pO1xuXG4vKipcbiAqXG4gKlxuICovXG5mdW5jdGlvbiBwb2RpdW1Db25mZXR0aSgpXG57XG4gICAgdmFyIGNvdW50ID0gMzAwO1xuICAgIHZhciBkZWZhdWx0cyA9IHtcbiAgICAgICAgb3JpZ2luOiB7IHk6IC4zNSB9XG4gICAgfTtcblxuICAgIGxldCBmaXJlID0gKHBhcnRpY2xlUmF0aW8sIG9wdHMpID0+XG4gICAge1xuICAgICAgICBjb25mZXR0aShPYmplY3QuYXNzaWduKHt9LCBkZWZhdWx0cywgb3B0cywge1xuICAgICAgICAgICAgcGFydGljbGVDb3VudDogTWF0aC5mbG9vcihjb3VudCAqIHBhcnRpY2xlUmF0aW8pXG4gICAgICAgIH0pKTtcbiAgICB9XG5cbiAgICBmaXJlKDAuMjUsIHtcbiAgICAgICAgc3ByZWFkOiAyNixcbiAgICAgICAgc3RhcnRWZWxvY2l0eTogNTUsXG4gICAgfSk7XG5cbiAgICBmaXJlKDAuMiwge1xuICAgICAgICBzcHJlYWQ6IDYwLFxuICAgIH0pO1xuXG4gICAgZmlyZSgwLjM1LCB7XG4gICAgICAgIHNwcmVhZDogMTAwLFxuICAgICAgICBkZWNheTogMC45MSxcbiAgICAgICAgc2NhbGFyOiAwLjhcbiAgICB9KTtcblxuICAgIGZpcmUoMC4xLCB7XG4gICAgICAgIHNwcmVhZDogMTIwLFxuICAgICAgICBzdGFydFZlbG9jaXR5OiAyNSxcbiAgICAgICAgZGVjYXk6IDAuOTIsXG4gICAgICAgIHNjYWxhcjogMS4yXG4gICAgfSk7XG5cbiAgICBmaXJlKDAuMSwge1xuICAgICAgICBzcHJlYWQ6IDEyMCxcbiAgICAgICAgc3RhcnRWZWxvY2l0eTogNDUsXG4gICAgfSk7XG59XG4iXSwibWFwcGluZ3MiOiJBQUFBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBR0FBLENBQUMsQ0FBQyxZQUNGO0VBQ0k7RUFDQTtFQUNBO0VBQ0E7RUFDQTtFQUNBO0VBQ0E7O0VBRUFDLGNBQWMsQ0FBQyxDQUFDO0FBQ3BCLENBQUMsQ0FBQzs7QUFFRjtBQUNBO0FBQ0E7QUFDQTtBQUNBLFNBQVNBLGNBQWNBLENBQUEsRUFDdkI7RUFDSSxJQUFJQyxLQUFLLEdBQUcsR0FBRztFQUNmLElBQUlDLFFBQVEsR0FBRztJQUNYQyxNQUFNLEVBQUU7TUFBRUMsQ0FBQyxFQUFFO0lBQUk7RUFDckIsQ0FBQztFQUVELElBQUlDLElBQUksR0FBRyxTQUFQQSxJQUFJQSxDQUFJQyxhQUFhLEVBQUVDLElBQUksRUFDL0I7SUFDSUMsUUFBUSxDQUFDQyxNQUFNLENBQUNDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBRVIsUUFBUSxFQUFFSyxJQUFJLEVBQUU7TUFDdkNJLGFBQWEsRUFBRUMsSUFBSSxDQUFDQyxLQUFLLENBQUNaLEtBQUssR0FBR0ssYUFBYTtJQUNuRCxDQUFDLENBQUMsQ0FBQztFQUNQLENBQUM7RUFFREQsSUFBSSxDQUFDLElBQUksRUFBRTtJQUNQUyxNQUFNLEVBQUUsRUFBRTtJQUNWQyxhQUFhLEVBQUU7RUFDbkIsQ0FBQyxDQUFDO0VBRUZWLElBQUksQ0FBQyxHQUFHLEVBQUU7SUFDTlMsTUFBTSxFQUFFO0VBQ1osQ0FBQyxDQUFDO0VBRUZULElBQUksQ0FBQyxJQUFJLEVBQUU7SUFDUFMsTUFBTSxFQUFFLEdBQUc7SUFDWEUsS0FBSyxFQUFFLElBQUk7SUFDWEMsTUFBTSxFQUFFO0VBQ1osQ0FBQyxDQUFDO0VBRUZaLElBQUksQ0FBQyxHQUFHLEVBQUU7SUFDTlMsTUFBTSxFQUFFLEdBQUc7SUFDWEMsYUFBYSxFQUFFLEVBQUU7SUFDakJDLEtBQUssRUFBRSxJQUFJO0lBQ1hDLE1BQU0sRUFBRTtFQUNaLENBQUMsQ0FBQztFQUVGWixJQUFJLENBQUMsR0FBRyxFQUFFO0lBQ05TLE1BQU0sRUFBRSxHQUFHO0lBQ1hDLGFBQWEsRUFBRTtFQUNuQixDQUFDLENBQUM7QUFDTiIsImZpbGUiOiIuL3Jlc291cmNlcy9qcy9kYXJ0L2dhbWUvZGFydFJlc3VsdC5qcyIsInNvdXJjZVJvb3QiOiIifQ==\n//# sourceURL=webpack-internal:///./resources/js/dart/game/dartResult.js\n");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval-source-map devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./resources/js/dart/game/dartResult.js"]();
/******/ 	
/******/ })()
;