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

/***/ "./resources/js/feedback-create.js":
/*!*****************************************!*\
  !*** ./resources/js/feedback-create.js ***!
  \*****************************************/
/***/ (() => {

eval("/**\r\n * @author Maximilian Mewes\r\n *\r\n *\r\n */\n\n$(function () {\n  var formFeedbackCreate = $('#container-form-feedback-create');\n  var formFeedback = $('#form-feedback');\n  var inputFeedbackType = $('#input-feedback-type');\n  var btnFeedbackTypeList = $('.btn-feedback');\n  btnFeedbackTypeList.each(function (k, e) {\n    $(e).click(function (event) {\n      $('#container-feedback').hide();\n      formFeedbackCreate.show();\n      var feedbackType = $(e).val();\n      inputFeedbackType.val(feedbackType);\n    });\n  });\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJuYW1lcyI6WyIkIiwiZm9ybUZlZWRiYWNrQ3JlYXRlIiwiZm9ybUZlZWRiYWNrIiwiaW5wdXRGZWVkYmFja1R5cGUiLCJidG5GZWVkYmFja1R5cGVMaXN0IiwiZWFjaCIsImsiLCJlIiwiY2xpY2siLCJldmVudCIsImhpZGUiLCJzaG93IiwiZmVlZGJhY2tUeXBlIiwidmFsIl0sInNvdXJjZXMiOlsid2VicGFjazovLy8uL3Jlc291cmNlcy9qcy9mZWVkYmFjay1jcmVhdGUuanM/NGE2MyJdLCJzb3VyY2VzQ29udGVudCI6WyIvKipcclxuICogQGF1dGhvciBNYXhpbWlsaWFuIE1ld2VzXHJcbiAqXHJcbiAqXHJcbiAqL1xyXG5cclxuJChmdW5jdGlvbigpXHJcbntcclxuICAgIGxldCBmb3JtRmVlZGJhY2tDcmVhdGUgPSAkKCcjY29udGFpbmVyLWZvcm0tZmVlZGJhY2stY3JlYXRlJyk7XHJcbiAgICBsZXQgZm9ybUZlZWRiYWNrID0gJCgnI2Zvcm0tZmVlZGJhY2snKTtcclxuICAgIGxldCBpbnB1dEZlZWRiYWNrVHlwZSA9ICQoJyNpbnB1dC1mZWVkYmFjay10eXBlJyk7XHJcbiAgICBsZXQgYnRuRmVlZGJhY2tUeXBlTGlzdCA9ICQoJy5idG4tZmVlZGJhY2snKTtcclxuXHJcbiAgICBidG5GZWVkYmFja1R5cGVMaXN0LmVhY2goKGssIGUpID0+XHJcbiAgICB7XHJcbiAgICAgICAgJChlKS5jbGljayhldmVudCA9PlxyXG4gICAgICAgIHtcclxuICAgICAgICAgICAgJCgnI2NvbnRhaW5lci1mZWVkYmFjaycpLmhpZGUoKTtcclxuICAgICAgICAgICAgZm9ybUZlZWRiYWNrQ3JlYXRlLnNob3coKTtcclxuXHJcbiAgICAgICAgICAgIGxldCBmZWVkYmFja1R5cGUgPSAkKGUpLnZhbCgpO1xyXG4gICAgICAgICAgICBpbnB1dEZlZWRiYWNrVHlwZS52YWwoZmVlZGJhY2tUeXBlKTtcclxuICAgICAgICB9KTtcclxuICAgIH0pO1xyXG59KTtcclxuIl0sIm1hcHBpbmdzIjoiQUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBQSxDQUFDLENBQUMsWUFDRjtFQUNJLElBQUlDLGtCQUFrQixHQUFHRCxDQUFDLENBQUMsaUNBQWlDLENBQUM7RUFDN0QsSUFBSUUsWUFBWSxHQUFHRixDQUFDLENBQUMsZ0JBQWdCLENBQUM7RUFDdEMsSUFBSUcsaUJBQWlCLEdBQUdILENBQUMsQ0FBQyxzQkFBc0IsQ0FBQztFQUNqRCxJQUFJSSxtQkFBbUIsR0FBR0osQ0FBQyxDQUFDLGVBQWUsQ0FBQztFQUU1Q0ksbUJBQW1CLENBQUNDLElBQUksQ0FBQyxVQUFDQyxDQUFDLEVBQUVDLENBQUMsRUFDOUI7SUFDSVAsQ0FBQyxDQUFDTyxDQUFDLENBQUMsQ0FBQ0MsS0FBSyxDQUFDLFVBQUFDLEtBQUssRUFDaEI7TUFDSVQsQ0FBQyxDQUFDLHFCQUFxQixDQUFDLENBQUNVLElBQUksQ0FBQyxDQUFDO01BQy9CVCxrQkFBa0IsQ0FBQ1UsSUFBSSxDQUFDLENBQUM7TUFFekIsSUFBSUMsWUFBWSxHQUFHWixDQUFDLENBQUNPLENBQUMsQ0FBQyxDQUFDTSxHQUFHLENBQUMsQ0FBQztNQUM3QlYsaUJBQWlCLENBQUNVLEdBQUcsQ0FBQ0QsWUFBWSxDQUFDO0lBQ3ZDLENBQUMsQ0FBQztFQUNOLENBQUMsQ0FBQztBQUNOLENBQUMsQ0FBQyIsImlnbm9yZUxpc3QiOltdLCJmaWxlIjoiLi9yZXNvdXJjZXMvanMvZmVlZGJhY2stY3JlYXRlLmpzIiwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///./resources/js/feedback-create.js\n");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval-source-map devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./resources/js/feedback-create.js"]();
/******/ 	
/******/ })()
;