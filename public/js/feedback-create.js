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

eval("/**\n * @author Maximilian Mewes\n *\n *\n */\n\n$(function () {\n  var formFeedbackCreate = $('#container-form-feedback-create');\n  var formFeedback = $('#form-feedback');\n  var inputFeedbackType = $('#input-feedback-type');\n  var btnFeedbackTypeList = $('.btn-feedback');\n  btnFeedbackTypeList.each(function (k, e) {\n    $(e).click(function (event) {\n      $('#container-feedback').hide();\n      formFeedbackCreate.show();\n      var feedbackType = $(e).val();\n      inputFeedbackType.val(feedbackType);\n    });\n  });\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJuYW1lcyI6WyIkIiwiZm9ybUZlZWRiYWNrQ3JlYXRlIiwiZm9ybUZlZWRiYWNrIiwiaW5wdXRGZWVkYmFja1R5cGUiLCJidG5GZWVkYmFja1R5cGVMaXN0IiwiZWFjaCIsImsiLCJlIiwiY2xpY2siLCJldmVudCIsImhpZGUiLCJzaG93IiwiZmVlZGJhY2tUeXBlIiwidmFsIl0sInNvdXJjZXMiOlsid2VicGFjazovLy8uL3Jlc291cmNlcy9qcy9mZWVkYmFjay1jcmVhdGUuanM/NGE2MyJdLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBhdXRob3IgTWF4aW1pbGlhbiBNZXdlc1xuICpcbiAqXG4gKi9cblxuJChmdW5jdGlvbigpXG57XG4gICAgbGV0IGZvcm1GZWVkYmFja0NyZWF0ZSA9ICQoJyNjb250YWluZXItZm9ybS1mZWVkYmFjay1jcmVhdGUnKTtcbiAgICBsZXQgZm9ybUZlZWRiYWNrID0gJCgnI2Zvcm0tZmVlZGJhY2snKTtcbiAgICBsZXQgaW5wdXRGZWVkYmFja1R5cGUgPSAkKCcjaW5wdXQtZmVlZGJhY2stdHlwZScpO1xuICAgIGxldCBidG5GZWVkYmFja1R5cGVMaXN0ID0gJCgnLmJ0bi1mZWVkYmFjaycpO1xuXG4gICAgYnRuRmVlZGJhY2tUeXBlTGlzdC5lYWNoKChrLCBlKSA9PlxuICAgIHtcbiAgICAgICAgJChlKS5jbGljayhldmVudCA9PlxuICAgICAgICB7XG4gICAgICAgICAgICAkKCcjY29udGFpbmVyLWZlZWRiYWNrJykuaGlkZSgpO1xuICAgICAgICAgICAgZm9ybUZlZWRiYWNrQ3JlYXRlLnNob3coKTtcblxuICAgICAgICAgICAgbGV0IGZlZWRiYWNrVHlwZSA9ICQoZSkudmFsKCk7XG4gICAgICAgICAgICBpbnB1dEZlZWRiYWNrVHlwZS52YWwoZmVlZGJhY2tUeXBlKTtcbiAgICAgICAgfSk7XG4gICAgfSk7XG59KTtcbiJdLCJtYXBwaW5ncyI6IkFBQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQUEsQ0FBQyxDQUFDLFlBQ0Y7RUFDSSxJQUFJQyxrQkFBa0IsR0FBR0QsQ0FBQyxDQUFDLGlDQUFpQyxDQUFDO0VBQzdELElBQUlFLFlBQVksR0FBR0YsQ0FBQyxDQUFDLGdCQUFnQixDQUFDO0VBQ3RDLElBQUlHLGlCQUFpQixHQUFHSCxDQUFDLENBQUMsc0JBQXNCLENBQUM7RUFDakQsSUFBSUksbUJBQW1CLEdBQUdKLENBQUMsQ0FBQyxlQUFlLENBQUM7RUFFNUNJLG1CQUFtQixDQUFDQyxJQUFJLENBQUMsVUFBQ0MsQ0FBQyxFQUFFQyxDQUFDLEVBQzlCO0lBQ0lQLENBQUMsQ0FBQ08sQ0FBQyxDQUFDLENBQUNDLEtBQUssQ0FBQyxVQUFBQyxLQUFLLEVBQ2hCO01BQ0lULENBQUMsQ0FBQyxxQkFBcUIsQ0FBQyxDQUFDVSxJQUFJLEVBQUU7TUFDL0JULGtCQUFrQixDQUFDVSxJQUFJLEVBQUU7TUFFekIsSUFBSUMsWUFBWSxHQUFHWixDQUFDLENBQUNPLENBQUMsQ0FBQyxDQUFDTSxHQUFHLEVBQUU7TUFDN0JWLGlCQUFpQixDQUFDVSxHQUFHLENBQUNELFlBQVksQ0FBQztJQUN2QyxDQUFDLENBQUM7RUFDTixDQUFDLENBQUM7QUFDTixDQUFDLENBQUMiLCJmaWxlIjoiLi9yZXNvdXJjZXMvanMvZmVlZGJhY2stY3JlYXRlLmpzLmpzIiwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///./resources/js/feedback-create.js\n");

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