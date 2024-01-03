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

eval("self.addEventListener('push', function (e) {\n  console.log(\"test\");\n  if (!(self.Notification && self.Notification.permission === 'granted')) {\n    //notifications aren't supported or permission not granted!\n    return;\n  }\n  if (e.data) {\n    var msg = e.data.json();\n    console.log(msg);\n    e.waitUntil(self.registration.showNotification(msg.title, {\n      body: msg.body,\n      icon: msg.icon,\n      actions: msg.actions\n    }));\n  }\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJuYW1lcyI6WyJzZWxmIiwiYWRkRXZlbnRMaXN0ZW5lciIsImUiLCJjb25zb2xlIiwibG9nIiwiTm90aWZpY2F0aW9uIiwicGVybWlzc2lvbiIsImRhdGEiLCJtc2ciLCJqc29uIiwid2FpdFVudGlsIiwicmVnaXN0cmF0aW9uIiwic2hvd05vdGlmaWNhdGlvbiIsInRpdGxlIiwiYm9keSIsImljb24iLCJhY3Rpb25zIl0sInNvdXJjZXMiOlsid2VicGFjazovLy8uL3Jlc291cmNlcy9qcy9zdy5qcz80NzI5Il0sInNvdXJjZXNDb250ZW50IjpbIlxuc2VsZi5hZGRFdmVudExpc3RlbmVyKCdwdXNoJywgZnVuY3Rpb24gKGUpIHtcbiAgICBjb25zb2xlLmxvZyhcInRlc3RcIik7XG4gICAgaWYgKCEoc2VsZi5Ob3RpZmljYXRpb24gJiYgc2VsZi5Ob3RpZmljYXRpb24ucGVybWlzc2lvbiA9PT0gJ2dyYW50ZWQnKSkge1xuICAgICAgICAvL25vdGlmaWNhdGlvbnMgYXJlbid0IHN1cHBvcnRlZCBvciBwZXJtaXNzaW9uIG5vdCBncmFudGVkIVxuICAgICAgICByZXR1cm47XG4gICAgfVxuXG4gICAgaWYgKGUuZGF0YSkge1xuICAgICAgICB2YXIgbXNnID0gZS5kYXRhLmpzb24oKTtcbiAgICAgICAgY29uc29sZS5sb2cobXNnKVxuICAgICAgICBlLndhaXRVbnRpbChzZWxmLnJlZ2lzdHJhdGlvbi5zaG93Tm90aWZpY2F0aW9uKG1zZy50aXRsZSwge1xuICAgICAgICAgICAgYm9keTogbXNnLmJvZHksXG4gICAgICAgICAgICBpY29uOiBtc2cuaWNvbixcbiAgICAgICAgICAgIGFjdGlvbnM6IG1zZy5hY3Rpb25zXG4gICAgICAgIH0pKTtcbiAgICB9XG59KTtcbiJdLCJtYXBwaW5ncyI6IkFBQ0FBLElBQUksQ0FBQ0MsZ0JBQWdCLENBQUMsTUFBTSxFQUFFLFVBQVVDLENBQUMsRUFBRTtFQUN2Q0MsT0FBTyxDQUFDQyxHQUFHLENBQUMsTUFBTSxDQUFDO0VBQ25CLElBQUksRUFBRUosSUFBSSxDQUFDSyxZQUFZLElBQUlMLElBQUksQ0FBQ0ssWUFBWSxDQUFDQyxVQUFVLEtBQUssU0FBUyxDQUFDLEVBQUU7SUFDcEU7SUFDQTtFQUNKO0VBRUEsSUFBSUosQ0FBQyxDQUFDSyxJQUFJLEVBQUU7SUFDUixJQUFJQyxHQUFHLEdBQUdOLENBQUMsQ0FBQ0ssSUFBSSxDQUFDRSxJQUFJLENBQUMsQ0FBQztJQUN2Qk4sT0FBTyxDQUFDQyxHQUFHLENBQUNJLEdBQUcsQ0FBQztJQUNoQk4sQ0FBQyxDQUFDUSxTQUFTLENBQUNWLElBQUksQ0FBQ1csWUFBWSxDQUFDQyxnQkFBZ0IsQ0FBQ0osR0FBRyxDQUFDSyxLQUFLLEVBQUU7TUFDdERDLElBQUksRUFBRU4sR0FBRyxDQUFDTSxJQUFJO01BQ2RDLElBQUksRUFBRVAsR0FBRyxDQUFDTyxJQUFJO01BQ2RDLE9BQU8sRUFBRVIsR0FBRyxDQUFDUTtJQUNqQixDQUFDLENBQUMsQ0FBQztFQUNQO0FBQ0osQ0FBQyxDQUFDIiwiZmlsZSI6Ii4vcmVzb3VyY2VzL2pzL3N3LmpzIiwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///./resources/js/sw.js\n");

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