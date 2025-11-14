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

/***/ "./resources/js/sw.js":
/*!****************************!*\
  !*** ./resources/js/sw.js ***!
  \****************************/
/***/ (() => {

eval("self.addEventListener('push', function (e) {\n  if (!(self.Notification && self.Notification.permission === 'granted')) {\n    //notifications aren't supported or permission not granted!\n    return;\n  }\n  if (e.data) {\n    var msg = e.data.json();\n    console.log(msg);\n    e.waitUntil(self.registration.showNotification(msg.title, {\n      body: msg.body,\n      icon: msg.icon,\n      actions: msg.actions\n    }));\n  }\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9yZXNvdXJjZXMvanMvc3cuanMiLCJuYW1lcyI6WyJzZWxmIiwiYWRkRXZlbnRMaXN0ZW5lciIsImUiLCJOb3RpZmljYXRpb24iLCJwZXJtaXNzaW9uIiwiZGF0YSIsIm1zZyIsImpzb24iLCJjb25zb2xlIiwibG9nIiwid2FpdFVudGlsIiwicmVnaXN0cmF0aW9uIiwic2hvd05vdGlmaWNhdGlvbiIsInRpdGxlIiwiYm9keSIsImljb24iLCJhY3Rpb25zIl0sInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9yZXNvdXJjZXMvanMvc3cuanM/NDcyOSJdLCJzb3VyY2VzQ29udGVudCI6WyJcbnNlbGYuYWRkRXZlbnRMaXN0ZW5lcigncHVzaCcsIGZ1bmN0aW9uIChlKSB7XG4gICAgaWYgKCEoc2VsZi5Ob3RpZmljYXRpb24gJiYgc2VsZi5Ob3RpZmljYXRpb24ucGVybWlzc2lvbiA9PT0gJ2dyYW50ZWQnKSkge1xuICAgICAgICAvL25vdGlmaWNhdGlvbnMgYXJlbid0IHN1cHBvcnRlZCBvciBwZXJtaXNzaW9uIG5vdCBncmFudGVkIVxuICAgICAgICByZXR1cm47XG4gICAgfVxuXG4gICAgaWYgKGUuZGF0YSkge1xuICAgICAgICB2YXIgbXNnID0gZS5kYXRhLmpzb24oKTtcbiAgICAgICAgY29uc29sZS5sb2cobXNnKVxuICAgICAgICBlLndhaXRVbnRpbChzZWxmLnJlZ2lzdHJhdGlvbi5zaG93Tm90aWZpY2F0aW9uKG1zZy50aXRsZSwge1xuICAgICAgICAgICAgYm9keTogbXNnLmJvZHksXG4gICAgICAgICAgICBpY29uOiBtc2cuaWNvbixcbiAgICAgICAgICAgIGFjdGlvbnM6IG1zZy5hY3Rpb25zXG4gICAgICAgIH0pKTtcbiAgICB9XG59KTtcbiJdLCJtYXBwaW5ncyI6IkFBQ0FBLElBQUksQ0FBQ0MsZ0JBQWdCLENBQUMsTUFBTSxFQUFFLFVBQVVDLENBQUMsRUFBRTtFQUN2QyxJQUFJLEVBQUVGLElBQUksQ0FBQ0csWUFBWSxJQUFJSCxJQUFJLENBQUNHLFlBQVksQ0FBQ0MsVUFBVSxLQUFLLFNBQVMsQ0FBQyxFQUFFO0lBQ3BFO0lBQ0E7RUFDSjtFQUVBLElBQUlGLENBQUMsQ0FBQ0csSUFBSSxFQUFFO0lBQ1IsSUFBSUMsR0FBRyxHQUFHSixDQUFDLENBQUNHLElBQUksQ0FBQ0UsSUFBSSxDQUFDLENBQUM7SUFDdkJDLE9BQU8sQ0FBQ0MsR0FBRyxDQUFDSCxHQUFHLENBQUM7SUFDaEJKLENBQUMsQ0FBQ1EsU0FBUyxDQUFDVixJQUFJLENBQUNXLFlBQVksQ0FBQ0MsZ0JBQWdCLENBQUNOLEdBQUcsQ0FBQ08sS0FBSyxFQUFFO01BQ3REQyxJQUFJLEVBQUVSLEdBQUcsQ0FBQ1EsSUFBSTtNQUNkQyxJQUFJLEVBQUVULEdBQUcsQ0FBQ1MsSUFBSTtNQUNkQyxPQUFPLEVBQUVWLEdBQUcsQ0FBQ1U7SUFDakIsQ0FBQyxDQUFDLENBQUM7RUFDUDtBQUNKLENBQUMsQ0FBQyIsImlnbm9yZUxpc3QiOltdfQ==\n//# sourceURL=webpack-internal:///./resources/js/sw.js\n");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval-source-map devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./resources/js/sw.js"]();
/******/ 	
/******/ })()
;