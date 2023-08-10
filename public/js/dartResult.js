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

/***/ "./resources/js/dartResult.js":
/*!************************************!*\
  !*** ./resources/js/dartResult.js ***!
  \************************************/
/***/ (() => {

eval("/**\n * @author Maximilian Mewes\n *\n *\n */\n\n$(function () {\n  // cool, ultra lightweight, but only supports click event\n  // let confetti = new Confetti('confetti');\n  // confetti.setCount(75);\n  // confetti.setSize(1);\n  // confetti.setPower(25);\n  // confetti.setFade(false);\n  // confetti.destroyTarget(false);\n\n  var count = 300;\n  var defaults = {\n    origin: {\n      y: .35\n    }\n  };\n  function fire(particleRatio, opts) {\n    confetti(Object.assign({}, defaults, opts, {\n      particleCount: Math.floor(count * particleRatio)\n    }));\n  }\n  fire(0.25, {\n    spread: 26,\n    startVelocity: 55\n  });\n  fire(0.2, {\n    spread: 60\n  });\n  fire(0.35, {\n    spread: 100,\n    decay: 0.91,\n    scalar: 0.8\n  });\n  fire(0.1, {\n    spread: 120,\n    startVelocity: 25,\n    decay: 0.92,\n    scalar: 1.2\n  });\n  fire(0.1, {\n    spread: 120,\n    startVelocity: 45\n  });\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9yZXNvdXJjZXMvanMvZGFydFJlc3VsdC5qcy5qcyIsIm5hbWVzIjpbIiQiLCJjb3VudCIsImRlZmF1bHRzIiwib3JpZ2luIiwieSIsImZpcmUiLCJwYXJ0aWNsZVJhdGlvIiwib3B0cyIsImNvbmZldHRpIiwiT2JqZWN0IiwiYXNzaWduIiwicGFydGljbGVDb3VudCIsIk1hdGgiLCJmbG9vciIsInNwcmVhZCIsInN0YXJ0VmVsb2NpdHkiLCJkZWNheSIsInNjYWxhciJdLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vLy4vcmVzb3VyY2VzL2pzL2RhcnRSZXN1bHQuanM/YTY4NyJdLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBhdXRob3IgTWF4aW1pbGlhbiBNZXdlc1xuICpcbiAqXG4gKi9cblxuJChmdW5jdGlvbigpXG57XG4gICAgLy8gY29vbCwgdWx0cmEgbGlnaHR3ZWlnaHQsIGJ1dCBvbmx5IHN1cHBvcnRzIGNsaWNrIGV2ZW50XG4gICAgLy8gbGV0IGNvbmZldHRpID0gbmV3IENvbmZldHRpKCdjb25mZXR0aScpO1xuICAgIC8vIGNvbmZldHRpLnNldENvdW50KDc1KTtcbiAgICAvLyBjb25mZXR0aS5zZXRTaXplKDEpO1xuICAgIC8vIGNvbmZldHRpLnNldFBvd2VyKDI1KTtcbiAgICAvLyBjb25mZXR0aS5zZXRGYWRlKGZhbHNlKTtcbiAgICAvLyBjb25mZXR0aS5kZXN0cm95VGFyZ2V0KGZhbHNlKTtcblxuICAgIHZhciBjb3VudCA9IDMwMDtcbiAgICB2YXIgZGVmYXVsdHMgPSB7XG4gICAgICBvcmlnaW46IHsgeTogLjM1IH1cbiAgICB9O1xuXG4gICAgZnVuY3Rpb24gZmlyZShwYXJ0aWNsZVJhdGlvLCBvcHRzKSB7XG4gICAgICBjb25mZXR0aShPYmplY3QuYXNzaWduKHt9LCBkZWZhdWx0cywgb3B0cywge1xuICAgICAgICBwYXJ0aWNsZUNvdW50OiBNYXRoLmZsb29yKGNvdW50ICogcGFydGljbGVSYXRpbylcbiAgICAgIH0pKTtcbiAgICB9XG5cbiAgICBmaXJlKDAuMjUsIHtcbiAgICAgIHNwcmVhZDogMjYsXG4gICAgICBzdGFydFZlbG9jaXR5OiA1NSxcbiAgICB9KTtcbiAgICBmaXJlKDAuMiwge1xuICAgICAgc3ByZWFkOiA2MCxcbiAgICB9KTtcbiAgICBmaXJlKDAuMzUsIHtcbiAgICAgIHNwcmVhZDogMTAwLFxuICAgICAgZGVjYXk6IDAuOTEsXG4gICAgICBzY2FsYXI6IDAuOFxuICAgIH0pO1xuICAgIGZpcmUoMC4xLCB7XG4gICAgICBzcHJlYWQ6IDEyMCxcbiAgICAgIHN0YXJ0VmVsb2NpdHk6IDI1LFxuICAgICAgZGVjYXk6IDAuOTIsXG4gICAgICBzY2FsYXI6IDEuMlxuICAgIH0pO1xuICAgIGZpcmUoMC4xLCB7XG4gICAgICBzcHJlYWQ6IDEyMCxcbiAgICAgIHN0YXJ0VmVsb2NpdHk6IDQ1LFxuICAgIH0pO1xufSk7XG4iXSwibWFwcGluZ3MiOiJBQUFBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUFBLENBQUMsQ0FBQyxZQUNGO0VBQ0k7RUFDQTtFQUNBO0VBQ0E7RUFDQTtFQUNBO0VBQ0E7O0VBRUEsSUFBSUMsS0FBSyxHQUFHLEdBQUc7RUFDZixJQUFJQyxRQUFRLEdBQUc7SUFDYkMsTUFBTSxFQUFFO01BQUVDLENBQUMsRUFBRTtJQUFJO0VBQ25CLENBQUM7RUFFRCxTQUFTQyxJQUFJLENBQUNDLGFBQWEsRUFBRUMsSUFBSSxFQUFFO0lBQ2pDQyxRQUFRLENBQUNDLE1BQU0sQ0FBQ0MsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFFUixRQUFRLEVBQUVLLElBQUksRUFBRTtNQUN6Q0ksYUFBYSxFQUFFQyxJQUFJLENBQUNDLEtBQUssQ0FBQ1osS0FBSyxHQUFHSyxhQUFhO0lBQ2pELENBQUMsQ0FBQyxDQUFDO0VBQ0w7RUFFQUQsSUFBSSxDQUFDLElBQUksRUFBRTtJQUNUUyxNQUFNLEVBQUUsRUFBRTtJQUNWQyxhQUFhLEVBQUU7RUFDakIsQ0FBQyxDQUFDO0VBQ0ZWLElBQUksQ0FBQyxHQUFHLEVBQUU7SUFDUlMsTUFBTSxFQUFFO0VBQ1YsQ0FBQyxDQUFDO0VBQ0ZULElBQUksQ0FBQyxJQUFJLEVBQUU7SUFDVFMsTUFBTSxFQUFFLEdBQUc7SUFDWEUsS0FBSyxFQUFFLElBQUk7SUFDWEMsTUFBTSxFQUFFO0VBQ1YsQ0FBQyxDQUFDO0VBQ0ZaLElBQUksQ0FBQyxHQUFHLEVBQUU7SUFDUlMsTUFBTSxFQUFFLEdBQUc7SUFDWEMsYUFBYSxFQUFFLEVBQUU7SUFDakJDLEtBQUssRUFBRSxJQUFJO0lBQ1hDLE1BQU0sRUFBRTtFQUNWLENBQUMsQ0FBQztFQUNGWixJQUFJLENBQUMsR0FBRyxFQUFFO0lBQ1JTLE1BQU0sRUFBRSxHQUFHO0lBQ1hDLGFBQWEsRUFBRTtFQUNqQixDQUFDLENBQUM7QUFDTixDQUFDLENBQUMifQ==\n//# sourceURL=webpack-internal:///./resources/js/dartResult.js\n");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval-source-map devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./resources/js/dartResult.js"]();
/******/ 	
/******/ })()
;