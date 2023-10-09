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

/***/ "./resources/js/dart-queue.js":
/*!************************************!*\
  !*** ./resources/js/dart-queue.js ***!
  \************************************/
/***/ (() => {

eval("document.getElementById('btnQueueAdd').addEventListener('click', function (e) {\n  axios.post(\"/api/v1/dart/queue/add\").then(function (response) {\n    var newItem = document.createElement('li');\n    newItem.innerHTML = 'Ich';\n    newItem.classList.add('list-group-item');\n    var timeText = document.createElement('span');\n    timeText.innerHTML = ' grade eben';\n    timeText.classList.add('text-body-secondary', 'small');\n    newItem.appendChild(timeText);\n    var btnRemove = document.createElement('button');\n    btnRemove.innerHTML = '<i class=\"fa-solid fa-xmark\"></i>';\n    btnRemove.id = 'btnQueueRemove';\n    btnRemove.classList.add('btn', 'p-0', 'text-danger', 'h-100', 'float-end');\n    newItem.appendChild(btnRemove);\n    addRemoveEventListener(btnRemove);\n    document.getElementById('dartQueueList').append(newItem);\n    var dartText = document.getElementById('dartQueueText');\n    dartText.innerHTML = 'Du bist in der Warteschlange';\n    dartText.classList.remove('text-danger');\n    dartText.classList.add('text-success');\n    document.getElementById('btnQueueAdd').classList.add('disabled');\n  })[\"catch\"](function (error) {\n    if (error.response) {\n      console.log(error.response.data);\n    }\n  });\n});\naddRemoveEventListener(document.getElementById('btnQueueRemove'));\nfunction addRemoveEventListener(element) {\n  element.addEventListener('click', function (e) {\n    axios.post(\"/api/v1/dart/queue/remove\").then(function (response) {\n      e.target.closest('li').remove();\n      var dartText = document.getElementById('dartQueueText');\n      dartText.innerHTML = 'Du bist nicht in der Warteschlange';\n      dartText.classList.remove('text-success');\n      dartText.classList.add('text-danger');\n      document.getElementById('btnQueueAdd').classList.remove('disabled');\n    })[\"catch\"](function (error) {\n      if (error.response) {\n        console.log(error.response.data);\n      }\n    });\n  });\n}//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJuYW1lcyI6WyJkb2N1bWVudCIsImdldEVsZW1lbnRCeUlkIiwiYWRkRXZlbnRMaXN0ZW5lciIsImUiLCJheGlvcyIsInBvc3QiLCJ0aGVuIiwicmVzcG9uc2UiLCJuZXdJdGVtIiwiY3JlYXRlRWxlbWVudCIsImlubmVySFRNTCIsImNsYXNzTGlzdCIsImFkZCIsInRpbWVUZXh0IiwiYXBwZW5kQ2hpbGQiLCJidG5SZW1vdmUiLCJpZCIsImFkZFJlbW92ZUV2ZW50TGlzdGVuZXIiLCJhcHBlbmQiLCJkYXJ0VGV4dCIsInJlbW92ZSIsImVycm9yIiwiY29uc29sZSIsImxvZyIsImRhdGEiLCJlbGVtZW50IiwidGFyZ2V0IiwiY2xvc2VzdCJdLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9yZXNvdXJjZXMvanMvZGFydC1xdWV1ZS5qcz9iOWIyIl0sInNvdXJjZXNDb250ZW50IjpbIlxuZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2J0blF1ZXVlQWRkJykuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBlID0+XG57XG4gICAgYXhpb3MucG9zdChgL2FwaS92MS9kYXJ0L3F1ZXVlL2FkZGApLnRoZW4oIHJlc3BvbnNlID0+IHtcbiAgICAgICAgY29uc3QgbmV3SXRlbSA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoJ2xpJyk7XG4gICAgICAgIG5ld0l0ZW0uaW5uZXJIVE1MID0gJ0ljaCc7XG4gICAgICAgIG5ld0l0ZW0uY2xhc3NMaXN0LmFkZCgnbGlzdC1ncm91cC1pdGVtJyk7XG5cbiAgICAgICAgY29uc3QgdGltZVRleHQgPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCdzcGFuJyk7XG4gICAgICAgIHRpbWVUZXh0LmlubmVySFRNTCA9ICcgZ3JhZGUgZWJlbic7XG4gICAgICAgIHRpbWVUZXh0LmNsYXNzTGlzdC5hZGQoJ3RleHQtYm9keS1zZWNvbmRhcnknLCAnc21hbGwnKTtcbiAgICAgICAgbmV3SXRlbS5hcHBlbmRDaGlsZCh0aW1lVGV4dCk7XG5cbiAgICAgICAgY29uc3QgYnRuUmVtb3ZlID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnYnV0dG9uJyk7XG4gICAgICAgIGJ0blJlbW92ZS5pbm5lckhUTUwgPSAnPGkgY2xhc3M9XCJmYS1zb2xpZCBmYS14bWFya1wiPjwvaT4nO1xuICAgICAgICBidG5SZW1vdmUuaWQgPSAnYnRuUXVldWVSZW1vdmUnO1xuICAgICAgICBidG5SZW1vdmUuY2xhc3NMaXN0LmFkZCgnYnRuJywgJ3AtMCcsICd0ZXh0LWRhbmdlcicsICdoLTEwMCcsICdmbG9hdC1lbmQnKTtcbiAgICAgICAgbmV3SXRlbS5hcHBlbmRDaGlsZChidG5SZW1vdmUpO1xuICAgICAgICBhZGRSZW1vdmVFdmVudExpc3RlbmVyKGJ0blJlbW92ZSk7XG5cbiAgICAgICAgZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2RhcnRRdWV1ZUxpc3QnKS5hcHBlbmQobmV3SXRlbSk7XG4gICAgICAgIGNvbnN0IGRhcnRUZXh0ID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2RhcnRRdWV1ZVRleHQnKVxuICAgICAgICBkYXJ0VGV4dC5pbm5lckhUTUwgPSAnRHUgYmlzdCBpbiBkZXIgV2FydGVzY2hsYW5nZSc7XG4gICAgICAgIGRhcnRUZXh0LmNsYXNzTGlzdC5yZW1vdmUoJ3RleHQtZGFuZ2VyJyk7XG4gICAgICAgIGRhcnRUZXh0LmNsYXNzTGlzdC5hZGQoJ3RleHQtc3VjY2VzcycpO1xuXG4gICAgICAgIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdidG5RdWV1ZUFkZCcpLmNsYXNzTGlzdC5hZGQoJ2Rpc2FibGVkJyk7XG4gICAgfSkuY2F0Y2goZnVuY3Rpb24gKGVycm9yKSB7XG4gICAgICAgIGlmIChlcnJvci5yZXNwb25zZSkge1xuICAgICAgICAgICAgY29uc29sZS5sb2coZXJyb3IucmVzcG9uc2UuZGF0YSk7XG4gICAgICAgIH1cbiAgICB9KTtcbn0pO1xuXG5hZGRSZW1vdmVFdmVudExpc3RlbmVyKGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdidG5RdWV1ZVJlbW92ZScpKTtcblxuXG5mdW5jdGlvbiBhZGRSZW1vdmVFdmVudExpc3RlbmVyKGVsZW1lbnQpXG57XG4gICAgZWxlbWVudC5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIGUgPT5cbiAgICB7XG4gICAgICAgIGF4aW9zLnBvc3QoYC9hcGkvdjEvZGFydC9xdWV1ZS9yZW1vdmVgKS50aGVuKCByZXNwb25zZSA9PiB7XG4gICAgICAgICAgICBlLnRhcmdldC5jbG9zZXN0KCdsaScpLnJlbW92ZSgpO1xuXG4gICAgICAgICAgICBjb25zdCBkYXJ0VGV4dCA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdkYXJ0UXVldWVUZXh0JylcbiAgICAgICAgICAgIGRhcnRUZXh0LmlubmVySFRNTCA9ICdEdSBiaXN0IG5pY2h0IGluIGRlciBXYXJ0ZXNjaGxhbmdlJztcbiAgICAgICAgICAgIGRhcnRUZXh0LmNsYXNzTGlzdC5yZW1vdmUoJ3RleHQtc3VjY2VzcycpO1xuICAgICAgICAgICAgZGFydFRleHQuY2xhc3NMaXN0LmFkZCgndGV4dC1kYW5nZXInKTtcblxuICAgICAgICAgICAgZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2J0blF1ZXVlQWRkJykuY2xhc3NMaXN0LnJlbW92ZSgnZGlzYWJsZWQnKTtcblxuICAgICAgICB9KS5jYXRjaChmdW5jdGlvbiAoZXJyb3IpIHtcbiAgICAgICAgICAgIGlmIChlcnJvci5yZXNwb25zZSkge1xuICAgICAgICAgICAgICAgIGNvbnNvbGUubG9nKGVycm9yLnJlc3BvbnNlLmRhdGEpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9KTtcbiAgICB9KTtcbn1cbiJdLCJtYXBwaW5ncyI6IkFBQ0FBLFFBQVEsQ0FBQ0MsY0FBYyxDQUFDLGFBQWEsQ0FBQyxDQUFDQyxnQkFBZ0IsQ0FBQyxPQUFPLEVBQUUsVUFBQUMsQ0FBQyxFQUNsRTtFQUNJQyxLQUFLLENBQUNDLElBQUkseUJBQXlCLENBQUMsQ0FBQ0MsSUFBSSxDQUFFLFVBQUFDLFFBQVEsRUFBSTtJQUNuRCxJQUFNQyxPQUFPLEdBQUdSLFFBQVEsQ0FBQ1MsYUFBYSxDQUFDLElBQUksQ0FBQztJQUM1Q0QsT0FBTyxDQUFDRSxTQUFTLEdBQUcsS0FBSztJQUN6QkYsT0FBTyxDQUFDRyxTQUFTLENBQUNDLEdBQUcsQ0FBQyxpQkFBaUIsQ0FBQztJQUV4QyxJQUFNQyxRQUFRLEdBQUdiLFFBQVEsQ0FBQ1MsYUFBYSxDQUFDLE1BQU0sQ0FBQztJQUMvQ0ksUUFBUSxDQUFDSCxTQUFTLEdBQUcsYUFBYTtJQUNsQ0csUUFBUSxDQUFDRixTQUFTLENBQUNDLEdBQUcsQ0FBQyxxQkFBcUIsRUFBRSxPQUFPLENBQUM7SUFDdERKLE9BQU8sQ0FBQ00sV0FBVyxDQUFDRCxRQUFRLENBQUM7SUFFN0IsSUFBTUUsU0FBUyxHQUFHZixRQUFRLENBQUNTLGFBQWEsQ0FBQyxRQUFRLENBQUM7SUFDbERNLFNBQVMsQ0FBQ0wsU0FBUyxHQUFHLG1DQUFtQztJQUN6REssU0FBUyxDQUFDQyxFQUFFLEdBQUcsZ0JBQWdCO0lBQy9CRCxTQUFTLENBQUNKLFNBQVMsQ0FBQ0MsR0FBRyxDQUFDLEtBQUssRUFBRSxLQUFLLEVBQUUsYUFBYSxFQUFFLE9BQU8sRUFBRSxXQUFXLENBQUM7SUFDMUVKLE9BQU8sQ0FBQ00sV0FBVyxDQUFDQyxTQUFTLENBQUM7SUFDOUJFLHNCQUFzQixDQUFDRixTQUFTLENBQUM7SUFFakNmLFFBQVEsQ0FBQ0MsY0FBYyxDQUFDLGVBQWUsQ0FBQyxDQUFDaUIsTUFBTSxDQUFDVixPQUFPLENBQUM7SUFDeEQsSUFBTVcsUUFBUSxHQUFHbkIsUUFBUSxDQUFDQyxjQUFjLENBQUMsZUFBZSxDQUFDO0lBQ3pEa0IsUUFBUSxDQUFDVCxTQUFTLEdBQUcsOEJBQThCO0lBQ25EUyxRQUFRLENBQUNSLFNBQVMsQ0FBQ1MsTUFBTSxDQUFDLGFBQWEsQ0FBQztJQUN4Q0QsUUFBUSxDQUFDUixTQUFTLENBQUNDLEdBQUcsQ0FBQyxjQUFjLENBQUM7SUFFdENaLFFBQVEsQ0FBQ0MsY0FBYyxDQUFDLGFBQWEsQ0FBQyxDQUFDVSxTQUFTLENBQUNDLEdBQUcsQ0FBQyxVQUFVLENBQUM7RUFDcEUsQ0FBQyxDQUFDLFNBQU0sQ0FBQyxVQUFVUyxLQUFLLEVBQUU7SUFDdEIsSUFBSUEsS0FBSyxDQUFDZCxRQUFRLEVBQUU7TUFDaEJlLE9BQU8sQ0FBQ0MsR0FBRyxDQUFDRixLQUFLLENBQUNkLFFBQVEsQ0FBQ2lCLElBQUksQ0FBQztJQUNwQztFQUNKLENBQUMsQ0FBQztBQUNOLENBQUMsQ0FBQztBQUVGUCxzQkFBc0IsQ0FBQ2pCLFFBQVEsQ0FBQ0MsY0FBYyxDQUFDLGdCQUFnQixDQUFDLENBQUM7QUFHakUsU0FBU2dCLHNCQUFzQkEsQ0FBQ1EsT0FBTyxFQUN2QztFQUNJQSxPQUFPLENBQUN2QixnQkFBZ0IsQ0FBQyxPQUFPLEVBQUUsVUFBQUMsQ0FBQyxFQUNuQztJQUNJQyxLQUFLLENBQUNDLElBQUksNEJBQTRCLENBQUMsQ0FBQ0MsSUFBSSxDQUFFLFVBQUFDLFFBQVEsRUFBSTtNQUN0REosQ0FBQyxDQUFDdUIsTUFBTSxDQUFDQyxPQUFPLENBQUMsSUFBSSxDQUFDLENBQUNQLE1BQU0sQ0FBQyxDQUFDO01BRS9CLElBQU1ELFFBQVEsR0FBR25CLFFBQVEsQ0FBQ0MsY0FBYyxDQUFDLGVBQWUsQ0FBQztNQUN6RGtCLFFBQVEsQ0FBQ1QsU0FBUyxHQUFHLG9DQUFvQztNQUN6RFMsUUFBUSxDQUFDUixTQUFTLENBQUNTLE1BQU0sQ0FBQyxjQUFjLENBQUM7TUFDekNELFFBQVEsQ0FBQ1IsU0FBUyxDQUFDQyxHQUFHLENBQUMsYUFBYSxDQUFDO01BRXJDWixRQUFRLENBQUNDLGNBQWMsQ0FBQyxhQUFhLENBQUMsQ0FBQ1UsU0FBUyxDQUFDUyxNQUFNLENBQUMsVUFBVSxDQUFDO0lBRXZFLENBQUMsQ0FBQyxTQUFNLENBQUMsVUFBVUMsS0FBSyxFQUFFO01BQ3RCLElBQUlBLEtBQUssQ0FBQ2QsUUFBUSxFQUFFO1FBQ2hCZSxPQUFPLENBQUNDLEdBQUcsQ0FBQ0YsS0FBSyxDQUFDZCxRQUFRLENBQUNpQixJQUFJLENBQUM7TUFDcEM7SUFDSixDQUFDLENBQUM7RUFDTixDQUFDLENBQUM7QUFDTiIsImZpbGUiOiIuL3Jlc291cmNlcy9qcy9kYXJ0LXF1ZXVlLmpzIiwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///./resources/js/dart-queue.js\n");

/***/ }),

/***/ "./resources/js/home.js":
/*!******************************!*\
  !*** ./resources/js/home.js ***!
  \******************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

eval("__webpack_require__(/*! ./dart-queue */ \"./resources/js/dart-queue.js\");//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9yZXNvdXJjZXMvanMvaG9tZS5qcyIsIm1hcHBpbmdzIjoiQUFDQUEsbUJBQU8sQ0FBQyxrREFBYyxDQUFDIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vLy4vcmVzb3VyY2VzL2pzL2hvbWUuanM/MjQyYiJdLCJzb3VyY2VzQ29udGVudCI6WyJcbnJlcXVpcmUoJy4vZGFydC1xdWV1ZScpO1xuIl0sIm5hbWVzIjpbInJlcXVpcmUiXSwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///./resources/js/home.js\n");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval-source-map devtool is used.
/******/ 	var __webpack_exports__ = __webpack_require__("./resources/js/home.js");
/******/ 	
/******/ })()
;