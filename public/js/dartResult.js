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

eval("/**\n * @author Maximilian Mewes\n *\n *\n */\n\n$(function () {\n  // cool, ultra lightweight, but only supports click event\n  // let confetti = new Confetti('confetti');\n  // confetti.setCount(75);\n  // confetti.setSize(1);\n  // confetti.setPower(25);\n  // confetti.setFade(false);\n  // confetti.destroyTarget(false);\n\n  var count = 300;\n  var defaults = {\n    origin: {\n      y: .3\n    }\n  };\n  function fire(particleRatio, opts) {\n    confetti(Object.assign({}, defaults, opts, {\n      particleCount: Math.floor(count * particleRatio)\n    }));\n  }\n  fire(0.25, {\n    spread: 26,\n    startVelocity: 55\n  });\n  fire(0.2, {\n    spread: 60\n  });\n  fire(0.35, {\n    spread: 100,\n    decay: 0.91,\n    scalar: 0.8\n  });\n  fire(0.1, {\n    spread: 120,\n    startVelocity: 25,\n    decay: 0.92,\n    scalar: 1.2\n  });\n  fire(0.1, {\n    spread: 120,\n    startVelocity: 45\n  });\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9yZXNvdXJjZXMvanMvZGFydFJlc3VsdC5qcy5qcyIsIm5hbWVzIjpbIiQiLCJjb3VudCIsImRlZmF1bHRzIiwib3JpZ2luIiwieSIsImZpcmUiLCJwYXJ0aWNsZVJhdGlvIiwib3B0cyIsImNvbmZldHRpIiwiT2JqZWN0IiwiYXNzaWduIiwicGFydGljbGVDb3VudCIsIk1hdGgiLCJmbG9vciIsInNwcmVhZCIsInN0YXJ0VmVsb2NpdHkiLCJkZWNheSIsInNjYWxhciJdLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vLy4vcmVzb3VyY2VzL2pzL2RhcnRSZXN1bHQuanM/YTY4NyJdLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBhdXRob3IgTWF4aW1pbGlhbiBNZXdlc1xuICpcbiAqXG4gKi9cblxuJChmdW5jdGlvbigpXG57XG4gICAgLy8gY29vbCwgdWx0cmEgbGlnaHR3ZWlnaHQsIGJ1dCBvbmx5IHN1cHBvcnRzIGNsaWNrIGV2ZW50XG4gICAgLy8gbGV0IGNvbmZldHRpID0gbmV3IENvbmZldHRpKCdjb25mZXR0aScpO1xuICAgIC8vIGNvbmZldHRpLnNldENvdW50KDc1KTtcbiAgICAvLyBjb25mZXR0aS5zZXRTaXplKDEpO1xuICAgIC8vIGNvbmZldHRpLnNldFBvd2VyKDI1KTtcbiAgICAvLyBjb25mZXR0aS5zZXRGYWRlKGZhbHNlKTtcbiAgICAvLyBjb25mZXR0aS5kZXN0cm95VGFyZ2V0KGZhbHNlKTtcblxuICAgIHZhciBjb3VudCA9IDMwMDtcbiAgICB2YXIgZGVmYXVsdHMgPSB7XG4gICAgICBvcmlnaW46IHsgeTogLjMgfVxuICAgIH07XG5cbiAgICBmdW5jdGlvbiBmaXJlKHBhcnRpY2xlUmF0aW8sIG9wdHMpIHtcbiAgICAgIGNvbmZldHRpKE9iamVjdC5hc3NpZ24oe30sIGRlZmF1bHRzLCBvcHRzLCB7XG4gICAgICAgIHBhcnRpY2xlQ291bnQ6IE1hdGguZmxvb3IoY291bnQgKiBwYXJ0aWNsZVJhdGlvKVxuICAgICAgfSkpO1xuICAgIH1cblxuICAgIGZpcmUoMC4yNSwge1xuICAgICAgc3ByZWFkOiAyNixcbiAgICAgIHN0YXJ0VmVsb2NpdHk6IDU1LFxuICAgIH0pO1xuICAgIGZpcmUoMC4yLCB7XG4gICAgICBzcHJlYWQ6IDYwLFxuICAgIH0pO1xuICAgIGZpcmUoMC4zNSwge1xuICAgICAgc3ByZWFkOiAxMDAsXG4gICAgICBkZWNheTogMC45MSxcbiAgICAgIHNjYWxhcjogMC44XG4gICAgfSk7XG4gICAgZmlyZSgwLjEsIHtcbiAgICAgIHNwcmVhZDogMTIwLFxuICAgICAgc3RhcnRWZWxvY2l0eTogMjUsXG4gICAgICBkZWNheTogMC45MixcbiAgICAgIHNjYWxhcjogMS4yXG4gICAgfSk7XG4gICAgZmlyZSgwLjEsIHtcbiAgICAgIHNwcmVhZDogMTIwLFxuICAgICAgc3RhcnRWZWxvY2l0eTogNDUsXG4gICAgfSk7XG59KTtcbiJdLCJtYXBwaW5ncyI6IkFBQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQUEsQ0FBQyxDQUFDLFlBQ0Y7RUFDSTtFQUNBO0VBQ0E7RUFDQTtFQUNBO0VBQ0E7RUFDQTs7RUFFQSxJQUFJQyxLQUFLLEdBQUcsR0FBRztFQUNmLElBQUlDLFFBQVEsR0FBRztJQUNiQyxNQUFNLEVBQUU7TUFBRUMsQ0FBQyxFQUFFO0lBQUc7RUFDbEIsQ0FBQztFQUVELFNBQVNDLElBQUksQ0FBQ0MsYUFBYSxFQUFFQyxJQUFJLEVBQUU7SUFDakNDLFFBQVEsQ0FBQ0MsTUFBTSxDQUFDQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEVBQUVSLFFBQVEsRUFBRUssSUFBSSxFQUFFO01BQ3pDSSxhQUFhLEVBQUVDLElBQUksQ0FBQ0MsS0FBSyxDQUFDWixLQUFLLEdBQUdLLGFBQWE7SUFDakQsQ0FBQyxDQUFDLENBQUM7RUFDTDtFQUVBRCxJQUFJLENBQUMsSUFBSSxFQUFFO0lBQ1RTLE1BQU0sRUFBRSxFQUFFO0lBQ1ZDLGFBQWEsRUFBRTtFQUNqQixDQUFDLENBQUM7RUFDRlYsSUFBSSxDQUFDLEdBQUcsRUFBRTtJQUNSUyxNQUFNLEVBQUU7RUFDVixDQUFDLENBQUM7RUFDRlQsSUFBSSxDQUFDLElBQUksRUFBRTtJQUNUUyxNQUFNLEVBQUUsR0FBRztJQUNYRSxLQUFLLEVBQUUsSUFBSTtJQUNYQyxNQUFNLEVBQUU7RUFDVixDQUFDLENBQUM7RUFDRlosSUFBSSxDQUFDLEdBQUcsRUFBRTtJQUNSUyxNQUFNLEVBQUUsR0FBRztJQUNYQyxhQUFhLEVBQUUsRUFBRTtJQUNqQkMsS0FBSyxFQUFFLElBQUk7SUFDWEMsTUFBTSxFQUFFO0VBQ1YsQ0FBQyxDQUFDO0VBQ0ZaLElBQUksQ0FBQyxHQUFHLEVBQUU7SUFDUlMsTUFBTSxFQUFFLEdBQUc7SUFDWEMsYUFBYSxFQUFFO0VBQ2pCLENBQUMsQ0FBQztBQUNOLENBQUMsQ0FBQyJ9\n//# sourceURL=webpack-internal:///./resources/js/dartResult.js\n");

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