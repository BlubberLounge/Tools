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

/***/ "./resources/js/invitation.js":
/*!************************************!*\
  !*** ./resources/js/invitation.js ***!
  \************************************/
/***/ (() => {

eval("/**\n * @author Maximilian Mewes\n *\n *\n */\n\n$(function () {\n  var approveBtns = document.querySelector('.btn-approve');\n  approveBtns === null || approveBtns === void 0 || approveBtns.addEventListener('click', function (e) {\n    console.log('approved');\n    var id = e.target.parentElement.getAttribute('data-invitation-id');\n    if (id) axios.post(\"/invitation/approve/\".concat(id))[\"catch\"](function (error) {\n      if (error.response) {\n        console.log(error.response.data);\n      }\n    });\n  });\n  var denieBtns = document.querySelector('.btn-denie');\n  denieBtns === null || denieBtns === void 0 || denieBtns.addEventListener('click', function (e) {\n    console.log('denied');\n    var id = e.target.parentElement.getAttribute('data-invitation-id');\n    if (id) axios.post(\"/invitation/denie/\".concat(id))[\"catch\"](function (error) {\n      if (error.response) {\n        console.log(error.response.data);\n      }\n    });\n  });\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9yZXNvdXJjZXMvanMvaW52aXRhdGlvbi5qcyIsIm5hbWVzIjpbIiQiLCJhcHByb3ZlQnRucyIsImRvY3VtZW50IiwicXVlcnlTZWxlY3RvciIsImFkZEV2ZW50TGlzdGVuZXIiLCJlIiwiY29uc29sZSIsImxvZyIsImlkIiwidGFyZ2V0IiwicGFyZW50RWxlbWVudCIsImdldEF0dHJpYnV0ZSIsImF4aW9zIiwicG9zdCIsImNvbmNhdCIsImVycm9yIiwicmVzcG9uc2UiLCJkYXRhIiwiZGVuaWVCdG5zIl0sInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9yZXNvdXJjZXMvanMvaW52aXRhdGlvbi5qcz83ZTRjIl0sInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGF1dGhvciBNYXhpbWlsaWFuIE1ld2VzXG4gKlxuICpcbiAqL1xuXG4kKGZ1bmN0aW9uKClcbntcbiAgICBjb25zdCBhcHByb3ZlQnRucyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy5idG4tYXBwcm92ZScpO1xuICAgIGFwcHJvdmVCdG5zPy5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIGUgPT5cbiAgICB7XG4gICAgICAgIGNvbnNvbGUubG9nKCdhcHByb3ZlZCcpO1xuICAgICAgICB2YXIgaWQgPSBlLnRhcmdldC5wYXJlbnRFbGVtZW50LmdldEF0dHJpYnV0ZSgnZGF0YS1pbnZpdGF0aW9uLWlkJyk7XG5cbiAgICAgICAgaWYoaWQpXG4gICAgICAgICAgICBheGlvcy5wb3N0KGAvaW52aXRhdGlvbi9hcHByb3ZlLyR7aWR9YCkuY2F0Y2goZnVuY3Rpb24gKGVycm9yKSB7XG4gICAgICAgICAgICAgICAgaWYgKGVycm9yLnJlc3BvbnNlKSB7XG4gICAgICAgICAgICAgICAgICAgIGNvbnNvbGUubG9nKGVycm9yLnJlc3BvbnNlLmRhdGEpO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0pO1xuICAgIH0pO1xuXG4gICAgY29uc3QgZGVuaWVCdG5zID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLmJ0bi1kZW5pZScpO1xuICAgIGRlbmllQnRucz8uYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBlID0+XG4gICAge1xuICAgICAgICBjb25zb2xlLmxvZygnZGVuaWVkJyk7XG4gICAgICAgIHZhciBpZCA9IGUudGFyZ2V0LnBhcmVudEVsZW1lbnQuZ2V0QXR0cmlidXRlKCdkYXRhLWludml0YXRpb24taWQnKTtcblxuICAgICAgICBpZihpZClcbiAgICAgICAgICAgIGF4aW9zLnBvc3QoYC9pbnZpdGF0aW9uL2RlbmllLyR7aWR9YCkuY2F0Y2goZnVuY3Rpb24gKGVycm9yKSB7XG4gICAgICAgICAgICAgICAgaWYgKGVycm9yLnJlc3BvbnNlKSB7XG4gICAgICAgICAgICAgICAgICAgIGNvbnNvbGUubG9nKGVycm9yLnJlc3BvbnNlLmRhdGEpO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0pO1xuICAgIH0pO1xufSk7XG4iXSwibWFwcGluZ3MiOiJBQUFBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUFBLENBQUMsQ0FBQyxZQUNGO0VBQ0ksSUFBTUMsV0FBVyxHQUFHQyxRQUFRLENBQUNDLGFBQWEsQ0FBQyxjQUFjLENBQUM7RUFDMURGLFdBQVcsYUFBWEEsV0FBVyxlQUFYQSxXQUFXLENBQUVHLGdCQUFnQixDQUFDLE9BQU8sRUFBRSxVQUFBQyxDQUFDLEVBQ3hDO0lBQ0lDLE9BQU8sQ0FBQ0MsR0FBRyxDQUFDLFVBQVUsQ0FBQztJQUN2QixJQUFJQyxFQUFFLEdBQUdILENBQUMsQ0FBQ0ksTUFBTSxDQUFDQyxhQUFhLENBQUNDLFlBQVksQ0FBQyxvQkFBb0IsQ0FBQztJQUVsRSxJQUFHSCxFQUFFLEVBQ0RJLEtBQUssQ0FBQ0MsSUFBSSx3QkFBQUMsTUFBQSxDQUF3Qk4sRUFBRSxDQUFFLENBQUMsU0FBTSxDQUFDLFVBQVVPLEtBQUssRUFBRTtNQUMzRCxJQUFJQSxLQUFLLENBQUNDLFFBQVEsRUFBRTtRQUNoQlYsT0FBTyxDQUFDQyxHQUFHLENBQUNRLEtBQUssQ0FBQ0MsUUFBUSxDQUFDQyxJQUFJLENBQUM7TUFDcEM7SUFDSixDQUFDLENBQUM7RUFDVixDQUFDLENBQUM7RUFFRixJQUFNQyxTQUFTLEdBQUdoQixRQUFRLENBQUNDLGFBQWEsQ0FBQyxZQUFZLENBQUM7RUFDdERlLFNBQVMsYUFBVEEsU0FBUyxlQUFUQSxTQUFTLENBQUVkLGdCQUFnQixDQUFDLE9BQU8sRUFBRSxVQUFBQyxDQUFDLEVBQ3RDO0lBQ0lDLE9BQU8sQ0FBQ0MsR0FBRyxDQUFDLFFBQVEsQ0FBQztJQUNyQixJQUFJQyxFQUFFLEdBQUdILENBQUMsQ0FBQ0ksTUFBTSxDQUFDQyxhQUFhLENBQUNDLFlBQVksQ0FBQyxvQkFBb0IsQ0FBQztJQUVsRSxJQUFHSCxFQUFFLEVBQ0RJLEtBQUssQ0FBQ0MsSUFBSSxzQkFBQUMsTUFBQSxDQUFzQk4sRUFBRSxDQUFFLENBQUMsU0FBTSxDQUFDLFVBQVVPLEtBQUssRUFBRTtNQUN6RCxJQUFJQSxLQUFLLENBQUNDLFFBQVEsRUFBRTtRQUNoQlYsT0FBTyxDQUFDQyxHQUFHLENBQUNRLEtBQUssQ0FBQ0MsUUFBUSxDQUFDQyxJQUFJLENBQUM7TUFDcEM7SUFDSixDQUFDLENBQUM7RUFDVixDQUFDLENBQUM7QUFDTixDQUFDLENBQUMifQ==\n//# sourceURL=webpack-internal:///./resources/js/invitation.js\n");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval-source-map devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./resources/js/invitation.js"]();
/******/ 	
/******/ })()
;