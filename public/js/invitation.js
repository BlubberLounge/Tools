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

eval("/**\n * @author Maximilian Mewes\n *\n *\n */\n\n$(function () {\n  var approveBtns = document.querySelector('.btn-approve');\n  approveBtns === null || approveBtns === void 0 || approveBtns.addEventListener('click', function (e) {\n    console.log('approved');\n    var id = e.target.parentElement.getAttribute('data-invitation-id');\n    axios.post(\"/invitation/approve/\".concat(id)).then(function (response) {\n      console.log(response);\n    })[\"catch\"](function (error) {\n      if (error.response) {\n        console.log(error.response.data);\n      }\n    });\n  });\n  var denieBtns = document.querySelector('.btn-denie');\n  denieBtns === null || denieBtns === void 0 || denieBtns.addEventListener('click', function (e) {\n    console.log('denied');\n    var id = e.target.parentElement.getAttribute('data-invitation-id');\n    axios.post(\"/invitation/denie/\".concat(id)).then(function (response) {\n      console.log(response);\n    })[\"catch\"](function (error) {\n      if (error.response) {\n        console.log(error.response.data);\n      }\n    });\n  });\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9yZXNvdXJjZXMvanMvaW52aXRhdGlvbi5qcyIsIm5hbWVzIjpbIiQiLCJhcHByb3ZlQnRucyIsImRvY3VtZW50IiwicXVlcnlTZWxlY3RvciIsImFkZEV2ZW50TGlzdGVuZXIiLCJlIiwiY29uc29sZSIsImxvZyIsImlkIiwidGFyZ2V0IiwicGFyZW50RWxlbWVudCIsImdldEF0dHJpYnV0ZSIsImF4aW9zIiwicG9zdCIsImNvbmNhdCIsInRoZW4iLCJyZXNwb25zZSIsImVycm9yIiwiZGF0YSIsImRlbmllQnRucyJdLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vLy4vcmVzb3VyY2VzL2pzL2ludml0YXRpb24uanM/N2U0YyJdLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBhdXRob3IgTWF4aW1pbGlhbiBNZXdlc1xuICpcbiAqXG4gKi9cblxuJChmdW5jdGlvbigpXG57XG4gICAgY29uc3QgYXBwcm92ZUJ0bnMgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcuYnRuLWFwcHJvdmUnKTtcbiAgICBhcHByb3ZlQnRucz8uYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBlID0+XG4gICAge1xuICAgICAgICBjb25zb2xlLmxvZygnYXBwcm92ZWQnKTtcbiAgICAgICAgY29uc3QgaWQgPSBlLnRhcmdldC5wYXJlbnRFbGVtZW50LmdldEF0dHJpYnV0ZSgnZGF0YS1pbnZpdGF0aW9uLWlkJyk7XG4gICAgICAgIGF4aW9zLnBvc3QoYC9pbnZpdGF0aW9uL2FwcHJvdmUvJHtpZH1gKS50aGVuKCByZXNwb25zZSA9PlxuICAgICAgICB7XG4gICAgICAgICAgICBjb25zb2xlLmxvZyhyZXNwb25zZSk7XG5cbiAgICAgICAgfSkuY2F0Y2goZnVuY3Rpb24gKGVycm9yKSB7XG4gICAgICAgICAgICBpZiAoZXJyb3IucmVzcG9uc2UpIHtcbiAgICAgICAgICAgICAgICBjb25zb2xlLmxvZyhlcnJvci5yZXNwb25zZS5kYXRhKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSk7XG4gICAgfSk7XG5cbiAgICBjb25zdCBkZW5pZUJ0bnMgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcuYnRuLWRlbmllJyk7XG4gICAgZGVuaWVCdG5zPy5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIGUgPT5cbiAgICB7XG4gICAgICAgIGNvbnNvbGUubG9nKCdkZW5pZWQnKTtcbiAgICAgICAgY29uc3QgaWQgPSBlLnRhcmdldC5wYXJlbnRFbGVtZW50LmdldEF0dHJpYnV0ZSgnZGF0YS1pbnZpdGF0aW9uLWlkJyk7XG5cbiAgICAgICAgYXhpb3MucG9zdChgL2ludml0YXRpb24vZGVuaWUvJHtpZH1gKS50aGVuKCByZXNwb25zZSA9PlxuICAgICAgICB7XG4gICAgICAgICAgICBjb25zb2xlLmxvZyhyZXNwb25zZSk7XG5cbiAgICAgICAgfSkuY2F0Y2goZnVuY3Rpb24gKGVycm9yKSB7XG4gICAgICAgICAgICBpZiAoZXJyb3IucmVzcG9uc2UpIHtcbiAgICAgICAgICAgICAgICBjb25zb2xlLmxvZyhlcnJvci5yZXNwb25zZS5kYXRhKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSk7XG4gICAgfSk7XG59KTtcbiJdLCJtYXBwaW5ncyI6IkFBQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQUEsQ0FBQyxDQUFDLFlBQ0Y7RUFDSSxJQUFNQyxXQUFXLEdBQUdDLFFBQVEsQ0FBQ0MsYUFBYSxDQUFDLGNBQWMsQ0FBQztFQUMxREYsV0FBVyxhQUFYQSxXQUFXLGVBQVhBLFdBQVcsQ0FBRUcsZ0JBQWdCLENBQUMsT0FBTyxFQUFFLFVBQUFDLENBQUMsRUFDeEM7SUFDSUMsT0FBTyxDQUFDQyxHQUFHLENBQUMsVUFBVSxDQUFDO0lBQ3ZCLElBQU1DLEVBQUUsR0FBR0gsQ0FBQyxDQUFDSSxNQUFNLENBQUNDLGFBQWEsQ0FBQ0MsWUFBWSxDQUFDLG9CQUFvQixDQUFDO0lBQ3BFQyxLQUFLLENBQUNDLElBQUksd0JBQUFDLE1BQUEsQ0FBd0JOLEVBQUUsQ0FBRSxDQUFDLENBQUNPLElBQUksQ0FBRSxVQUFBQyxRQUFRLEVBQ3REO01BQ0lWLE9BQU8sQ0FBQ0MsR0FBRyxDQUFDUyxRQUFRLENBQUM7SUFFekIsQ0FBQyxDQUFDLFNBQU0sQ0FBQyxVQUFVQyxLQUFLLEVBQUU7TUFDdEIsSUFBSUEsS0FBSyxDQUFDRCxRQUFRLEVBQUU7UUFDaEJWLE9BQU8sQ0FBQ0MsR0FBRyxDQUFDVSxLQUFLLENBQUNELFFBQVEsQ0FBQ0UsSUFBSSxDQUFDO01BQ3BDO0lBQ0osQ0FBQyxDQUFDO0VBQ04sQ0FBQyxDQUFDO0VBRUYsSUFBTUMsU0FBUyxHQUFHakIsUUFBUSxDQUFDQyxhQUFhLENBQUMsWUFBWSxDQUFDO0VBQ3REZ0IsU0FBUyxhQUFUQSxTQUFTLGVBQVRBLFNBQVMsQ0FBRWYsZ0JBQWdCLENBQUMsT0FBTyxFQUFFLFVBQUFDLENBQUMsRUFDdEM7SUFDSUMsT0FBTyxDQUFDQyxHQUFHLENBQUMsUUFBUSxDQUFDO0lBQ3JCLElBQU1DLEVBQUUsR0FBR0gsQ0FBQyxDQUFDSSxNQUFNLENBQUNDLGFBQWEsQ0FBQ0MsWUFBWSxDQUFDLG9CQUFvQixDQUFDO0lBRXBFQyxLQUFLLENBQUNDLElBQUksc0JBQUFDLE1BQUEsQ0FBc0JOLEVBQUUsQ0FBRSxDQUFDLENBQUNPLElBQUksQ0FBRSxVQUFBQyxRQUFRLEVBQ3BEO01BQ0lWLE9BQU8sQ0FBQ0MsR0FBRyxDQUFDUyxRQUFRLENBQUM7SUFFekIsQ0FBQyxDQUFDLFNBQU0sQ0FBQyxVQUFVQyxLQUFLLEVBQUU7TUFDdEIsSUFBSUEsS0FBSyxDQUFDRCxRQUFRLEVBQUU7UUFDaEJWLE9BQU8sQ0FBQ0MsR0FBRyxDQUFDVSxLQUFLLENBQUNELFFBQVEsQ0FBQ0UsSUFBSSxDQUFDO01BQ3BDO0lBQ0osQ0FBQyxDQUFDO0VBQ04sQ0FBQyxDQUFDO0FBQ04sQ0FBQyxDQUFDIn0=\n//# sourceURL=webpack-internal:///./resources/js/invitation.js\n");

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