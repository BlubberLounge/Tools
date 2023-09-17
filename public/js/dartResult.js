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

eval("/**\r\n * @author Maximilian Mewes\r\n *\r\n *\r\n */\n\n$(function () {\n  // cool, ultra lightweight, but only supports click event\n  // let confetti = new Confetti('confetti');\n  // confetti.setCount(75);\n  // confetti.setSize(1);\n  // confetti.setPower(25);\n  // confetti.setFade(false);\n  // confetti.destroyTarget(false);\n\n  podiumConfetti();\n});\n\n/**\r\n *\r\n *\r\n */\nfunction podiumConfetti() {\n  var count = 300;\n  var defaults = {\n    origin: {\n      y: .35\n    }\n  };\n  var fire = function fire(particleRatio, opts) {\n    confetti(Object.assign({}, defaults, opts, {\n      particleCount: Math.floor(count * particleRatio)\n    }));\n  };\n  fire(0.25, {\n    spread: 26,\n    startVelocity: 55\n  });\n  fire(0.2, {\n    spread: 60\n  });\n  fire(0.35, {\n    spread: 100,\n    decay: 0.91,\n    scalar: 0.8\n  });\n  fire(0.1, {\n    spread: 120,\n    startVelocity: 25,\n    decay: 0.92,\n    scalar: 1.2\n  });\n  fire(0.1, {\n    spread: 120,\n    startVelocity: 45\n  });\n}//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJuYW1lcyI6WyIkIiwicG9kaXVtQ29uZmV0dGkiLCJjb3VudCIsImRlZmF1bHRzIiwib3JpZ2luIiwieSIsImZpcmUiLCJwYXJ0aWNsZVJhdGlvIiwib3B0cyIsImNvbmZldHRpIiwiT2JqZWN0IiwiYXNzaWduIiwicGFydGljbGVDb3VudCIsIk1hdGgiLCJmbG9vciIsInNwcmVhZCIsInN0YXJ0VmVsb2NpdHkiLCJkZWNheSIsInNjYWxhciJdLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9yZXNvdXJjZXMvanMvZGFydC9nYW1lL2RhcnRSZXN1bHQuanM/MTYzMyJdLCJzb3VyY2VzQ29udGVudCI6WyIvKipcclxuICogQGF1dGhvciBNYXhpbWlsaWFuIE1ld2VzXHJcbiAqXHJcbiAqXHJcbiAqL1xyXG5cclxuXHJcbiQoZnVuY3Rpb24oKVxyXG57XHJcbiAgICAvLyBjb29sLCB1bHRyYSBsaWdodHdlaWdodCwgYnV0IG9ubHkgc3VwcG9ydHMgY2xpY2sgZXZlbnRcclxuICAgIC8vIGxldCBjb25mZXR0aSA9IG5ldyBDb25mZXR0aSgnY29uZmV0dGknKTtcclxuICAgIC8vIGNvbmZldHRpLnNldENvdW50KDc1KTtcclxuICAgIC8vIGNvbmZldHRpLnNldFNpemUoMSk7XHJcbiAgICAvLyBjb25mZXR0aS5zZXRQb3dlcigyNSk7XHJcbiAgICAvLyBjb25mZXR0aS5zZXRGYWRlKGZhbHNlKTtcclxuICAgIC8vIGNvbmZldHRpLmRlc3Ryb3lUYXJnZXQoZmFsc2UpO1xyXG5cclxuICAgIHBvZGl1bUNvbmZldHRpKCk7XHJcbn0pO1xyXG5cclxuLyoqXHJcbiAqXHJcbiAqXHJcbiAqL1xyXG5mdW5jdGlvbiBwb2RpdW1Db25mZXR0aSgpXHJcbntcclxuICAgIHZhciBjb3VudCA9IDMwMDtcclxuICAgIHZhciBkZWZhdWx0cyA9IHtcclxuICAgICAgICBvcmlnaW46IHsgeTogLjM1IH1cclxuICAgIH07XHJcblxyXG4gICAgbGV0IGZpcmUgPSAocGFydGljbGVSYXRpbywgb3B0cykgPT5cclxuICAgIHtcclxuICAgICAgICBjb25mZXR0aShPYmplY3QuYXNzaWduKHt9LCBkZWZhdWx0cywgb3B0cywge1xyXG4gICAgICAgICAgICBwYXJ0aWNsZUNvdW50OiBNYXRoLmZsb29yKGNvdW50ICogcGFydGljbGVSYXRpbylcclxuICAgICAgICB9KSk7XHJcbiAgICB9XHJcblxyXG4gICAgZmlyZSgwLjI1LCB7XHJcbiAgICAgICAgc3ByZWFkOiAyNixcclxuICAgICAgICBzdGFydFZlbG9jaXR5OiA1NSxcclxuICAgIH0pO1xyXG5cclxuICAgIGZpcmUoMC4yLCB7XHJcbiAgICAgICAgc3ByZWFkOiA2MCxcclxuICAgIH0pO1xyXG5cclxuICAgIGZpcmUoMC4zNSwge1xyXG4gICAgICAgIHNwcmVhZDogMTAwLFxyXG4gICAgICAgIGRlY2F5OiAwLjkxLFxyXG4gICAgICAgIHNjYWxhcjogMC44XHJcbiAgICB9KTtcclxuXHJcbiAgICBmaXJlKDAuMSwge1xyXG4gICAgICAgIHNwcmVhZDogMTIwLFxyXG4gICAgICAgIHN0YXJ0VmVsb2NpdHk6IDI1LFxyXG4gICAgICAgIGRlY2F5OiAwLjkyLFxyXG4gICAgICAgIHNjYWxhcjogMS4yXHJcbiAgICB9KTtcclxuXHJcbiAgICBmaXJlKDAuMSwge1xyXG4gICAgICAgIHNwcmVhZDogMTIwLFxyXG4gICAgICAgIHN0YXJ0VmVsb2NpdHk6IDQ1LFxyXG4gICAgfSk7XHJcbn1cclxuIl0sIm1hcHBpbmdzIjoiQUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUdBQSxDQUFDLENBQUMsWUFDRjtFQUNJO0VBQ0E7RUFDQTtFQUNBO0VBQ0E7RUFDQTtFQUNBOztFQUVBQyxjQUFjLENBQUMsQ0FBQztBQUNwQixDQUFDLENBQUM7O0FBRUY7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTQSxjQUFjQSxDQUFBLEVBQ3ZCO0VBQ0ksSUFBSUMsS0FBSyxHQUFHLEdBQUc7RUFDZixJQUFJQyxRQUFRLEdBQUc7SUFDWEMsTUFBTSxFQUFFO01BQUVDLENBQUMsRUFBRTtJQUFJO0VBQ3JCLENBQUM7RUFFRCxJQUFJQyxJQUFJLEdBQUcsU0FBUEEsSUFBSUEsQ0FBSUMsYUFBYSxFQUFFQyxJQUFJLEVBQy9CO0lBQ0lDLFFBQVEsQ0FBQ0MsTUFBTSxDQUFDQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUVSLFFBQVEsRUFBRUssSUFBSSxFQUFFO01BQ3ZDSSxhQUFhLEVBQUVDLElBQUksQ0FBQ0MsS0FBSyxDQUFDWixLQUFLLEdBQUdLLGFBQWE7SUFDbkQsQ0FBQyxDQUFDLENBQUM7RUFDUCxDQUFDO0VBRURELElBQUksQ0FBQyxJQUFJLEVBQUU7SUFDUFMsTUFBTSxFQUFFLEVBQUU7SUFDVkMsYUFBYSxFQUFFO0VBQ25CLENBQUMsQ0FBQztFQUVGVixJQUFJLENBQUMsR0FBRyxFQUFFO0lBQ05TLE1BQU0sRUFBRTtFQUNaLENBQUMsQ0FBQztFQUVGVCxJQUFJLENBQUMsSUFBSSxFQUFFO0lBQ1BTLE1BQU0sRUFBRSxHQUFHO0lBQ1hFLEtBQUssRUFBRSxJQUFJO0lBQ1hDLE1BQU0sRUFBRTtFQUNaLENBQUMsQ0FBQztFQUVGWixJQUFJLENBQUMsR0FBRyxFQUFFO0lBQ05TLE1BQU0sRUFBRSxHQUFHO0lBQ1hDLGFBQWEsRUFBRSxFQUFFO0lBQ2pCQyxLQUFLLEVBQUUsSUFBSTtJQUNYQyxNQUFNLEVBQUU7RUFDWixDQUFDLENBQUM7RUFFRlosSUFBSSxDQUFDLEdBQUcsRUFBRTtJQUNOUyxNQUFNLEVBQUUsR0FBRztJQUNYQyxhQUFhLEVBQUU7RUFDbkIsQ0FBQyxDQUFDO0FBQ04iLCJmaWxlIjoiLi9yZXNvdXJjZXMvanMvZGFydC9nYW1lL2RhcnRSZXN1bHQuanMiLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///./resources/js/dart/game/dartResult.js\n");

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