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

eval("/**\n * @author Maximilian Mewes\n *\n *\n */\n\n$(function () {\n  // cool, ultra lightweight, but only supports click event\n  // let confetti = new Confetti('confetti');\n  // confetti.setCount(75);\n  // confetti.setSize(1);\n  // confetti.setPower(25);\n  // confetti.setFade(false);\n  // confetti.destroyTarget(false);\n\n  podiumConfetti();\n});\n\n/**\n *\n *\n */\nfunction podiumConfetti() {\n  var count = 300;\n  var defaults = {\n    origin: {\n      y: .35\n    }\n  };\n  var fire = function fire(particleRatio, opts) {\n    confetti(Object.assign({}, defaults, opts, {\n      particleCount: Math.floor(count * particleRatio)\n    }));\n  };\n  fire(0.25, {\n    spread: 26,\n    startVelocity: 55\n  });\n  fire(0.2, {\n    spread: 60\n  });\n  fire(0.35, {\n    spread: 100,\n    decay: 0.91,\n    scalar: 0.8\n  });\n  fire(0.1, {\n    spread: 120,\n    startVelocity: 25,\n    decay: 0.92,\n    scalar: 1.2\n  });\n  fire(0.1, {\n    spread: 120,\n    startVelocity: 45\n  });\n}//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJuYW1lcyI6WyIkIiwicG9kaXVtQ29uZmV0dGkiLCJjb3VudCIsImRlZmF1bHRzIiwib3JpZ2luIiwieSIsImZpcmUiLCJwYXJ0aWNsZVJhdGlvIiwib3B0cyIsImNvbmZldHRpIiwiT2JqZWN0IiwiYXNzaWduIiwicGFydGljbGVDb3VudCIsIk1hdGgiLCJmbG9vciIsInNwcmVhZCIsInN0YXJ0VmVsb2NpdHkiLCJkZWNheSIsInNjYWxhciJdLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9yZXNvdXJjZXMvanMvZGFydFJlc3VsdC5qcz9hNjg3Il0sInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGF1dGhvciBNYXhpbWlsaWFuIE1ld2VzXG4gKlxuICpcbiAqL1xuXG5cbiQoZnVuY3Rpb24oKVxue1xuICAgIC8vIGNvb2wsIHVsdHJhIGxpZ2h0d2VpZ2h0LCBidXQgb25seSBzdXBwb3J0cyBjbGljayBldmVudFxuICAgIC8vIGxldCBjb25mZXR0aSA9IG5ldyBDb25mZXR0aSgnY29uZmV0dGknKTtcbiAgICAvLyBjb25mZXR0aS5zZXRDb3VudCg3NSk7XG4gICAgLy8gY29uZmV0dGkuc2V0U2l6ZSgxKTtcbiAgICAvLyBjb25mZXR0aS5zZXRQb3dlcigyNSk7XG4gICAgLy8gY29uZmV0dGkuc2V0RmFkZShmYWxzZSk7XG4gICAgLy8gY29uZmV0dGkuZGVzdHJveVRhcmdldChmYWxzZSk7XG5cbiAgICBwb2RpdW1Db25mZXR0aSgpO1xufSk7XG5cbi8qKlxuICpcbiAqXG4gKi9cbmZ1bmN0aW9uIHBvZGl1bUNvbmZldHRpKClcbntcbiAgICB2YXIgY291bnQgPSAzMDA7XG4gICAgdmFyIGRlZmF1bHRzID0ge1xuICAgICAgICBvcmlnaW46IHsgeTogLjM1IH1cbiAgICB9O1xuXG4gICAgbGV0IGZpcmUgPSAocGFydGljbGVSYXRpbywgb3B0cykgPT5cbiAgICB7XG4gICAgICAgIGNvbmZldHRpKE9iamVjdC5hc3NpZ24oe30sIGRlZmF1bHRzLCBvcHRzLCB7XG4gICAgICAgICAgICBwYXJ0aWNsZUNvdW50OiBNYXRoLmZsb29yKGNvdW50ICogcGFydGljbGVSYXRpbylcbiAgICAgICAgfSkpO1xuICAgIH1cblxuICAgIGZpcmUoMC4yNSwge1xuICAgICAgICBzcHJlYWQ6IDI2LFxuICAgICAgICBzdGFydFZlbG9jaXR5OiA1NSxcbiAgICB9KTtcblxuICAgIGZpcmUoMC4yLCB7XG4gICAgICAgIHNwcmVhZDogNjAsXG4gICAgfSk7XG5cbiAgICBmaXJlKDAuMzUsIHtcbiAgICAgICAgc3ByZWFkOiAxMDAsXG4gICAgICAgIGRlY2F5OiAwLjkxLFxuICAgICAgICBzY2FsYXI6IDAuOFxuICAgIH0pO1xuXG4gICAgZmlyZSgwLjEsIHtcbiAgICAgICAgc3ByZWFkOiAxMjAsXG4gICAgICAgIHN0YXJ0VmVsb2NpdHk6IDI1LFxuICAgICAgICBkZWNheTogMC45MixcbiAgICAgICAgc2NhbGFyOiAxLjJcbiAgICB9KTtcblxuICAgIGZpcmUoMC4xLCB7XG4gICAgICAgIHNwcmVhZDogMTIwLFxuICAgICAgICBzdGFydFZlbG9jaXR5OiA0NSxcbiAgICB9KTtcbn1cbiJdLCJtYXBwaW5ncyI6IkFBQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFHQUEsQ0FBQyxDQUFDLFlBQ0Y7RUFDSTtFQUNBO0VBQ0E7RUFDQTtFQUNBO0VBQ0E7RUFDQTs7RUFFQUMsY0FBYyxFQUFFO0FBQ3BCLENBQUMsQ0FBQzs7QUFFRjtBQUNBO0FBQ0E7QUFDQTtBQUNBLFNBQVNBLGNBQWMsR0FDdkI7RUFDSSxJQUFJQyxLQUFLLEdBQUcsR0FBRztFQUNmLElBQUlDLFFBQVEsR0FBRztJQUNYQyxNQUFNLEVBQUU7TUFBRUMsQ0FBQyxFQUFFO0lBQUk7RUFDckIsQ0FBQztFQUVELElBQUlDLElBQUksR0FBRyxTQUFQQSxJQUFJLENBQUlDLGFBQWEsRUFBRUMsSUFBSSxFQUMvQjtJQUNJQyxRQUFRLENBQUNDLE1BQU0sQ0FBQ0MsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFFUixRQUFRLEVBQUVLLElBQUksRUFBRTtNQUN2Q0ksYUFBYSxFQUFFQyxJQUFJLENBQUNDLEtBQUssQ0FBQ1osS0FBSyxHQUFHSyxhQUFhO0lBQ25ELENBQUMsQ0FBQyxDQUFDO0VBQ1AsQ0FBQztFQUVERCxJQUFJLENBQUMsSUFBSSxFQUFFO0lBQ1BTLE1BQU0sRUFBRSxFQUFFO0lBQ1ZDLGFBQWEsRUFBRTtFQUNuQixDQUFDLENBQUM7RUFFRlYsSUFBSSxDQUFDLEdBQUcsRUFBRTtJQUNOUyxNQUFNLEVBQUU7RUFDWixDQUFDLENBQUM7RUFFRlQsSUFBSSxDQUFDLElBQUksRUFBRTtJQUNQUyxNQUFNLEVBQUUsR0FBRztJQUNYRSxLQUFLLEVBQUUsSUFBSTtJQUNYQyxNQUFNLEVBQUU7RUFDWixDQUFDLENBQUM7RUFFRlosSUFBSSxDQUFDLEdBQUcsRUFBRTtJQUNOUyxNQUFNLEVBQUUsR0FBRztJQUNYQyxhQUFhLEVBQUUsRUFBRTtJQUNqQkMsS0FBSyxFQUFFLElBQUk7SUFDWEMsTUFBTSxFQUFFO0VBQ1osQ0FBQyxDQUFDO0VBRUZaLElBQUksQ0FBQyxHQUFHLEVBQUU7SUFDTlMsTUFBTSxFQUFFLEdBQUc7SUFDWEMsYUFBYSxFQUFFO0VBQ25CLENBQUMsQ0FBQztBQUNOIiwiZmlsZSI6Ii4vcmVzb3VyY2VzL2pzL2RhcnRSZXN1bHQuanMuanMiLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///./resources/js/dartResult.js\n");

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