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

/***/ "./resources/js/feedback-index.js":
/*!****************************************!*\
  !*** ./resources/js/feedback-index.js ***!
  \****************************************/
/***/ (() => {

eval("/**\n * @author Maximilian Mewes\n *\n *\n */\n\n$(function () {\n  var btnFeedbackRatingList = $('.btn-feedback-rating');\n  var btnFeedbackHeaderList = $('button[data-bl-feedback-status=\"new\"]');\n  btnFeedbackRatingList.each(function (k, e) {\n    $(e).click(function (event) {\n      var feedbackID = $(e).closest('.accordion-item').data('bl-feedback-id');\n      $.ajax({\n        url: '/feedback/' + feedbackID,\n        method: 'PUT',\n        headers: {\n          'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')\n        },\n        data: {\n          status: $(e).data('bl-feedback-status')\n        },\n        beforeSend: function beforeSend() {},\n        success: function success(response) {\n          // console.log(response);\n          // most cheap\n          window.location.reload();\n        },\n        error: function error(jqXHR, textStatus, errorThrown) {\n          // handle the error case\n          // console.log(errorThrown);\n          // TODO\n        }\n      });\n    });\n  });\n  btnFeedbackHeaderList.each(function (k, e) {\n    var feedbackID = $(e).closest('.accordion-item').data('bl-feedback-id');\n    $(e).click(function (event) {\n      $.ajax({\n        url: '/feedback/' + feedbackID,\n        method: 'PUT',\n        headers: {\n          'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')\n        },\n        data: {\n          status: 'seen'\n        },\n        beforeSend: function beforeSend() {},\n        success: function success(response) {\n          // console.log(response);\n          $(e).closest('.feedback-seen-icon').show();\n          $(e).off('click'); // remove event listener\n        },\n\n        error: function error(jqXHR, textStatus, errorThrown) {\n          // handle the error case\n          // console.log(errorThrown);\n          // TODO\n        }\n      });\n    });\n  });\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJuYW1lcyI6WyIkIiwiYnRuRmVlZGJhY2tSYXRpbmdMaXN0IiwiYnRuRmVlZGJhY2tIZWFkZXJMaXN0IiwiZWFjaCIsImsiLCJlIiwiY2xpY2siLCJldmVudCIsImZlZWRiYWNrSUQiLCJjbG9zZXN0IiwiZGF0YSIsImFqYXgiLCJ1cmwiLCJtZXRob2QiLCJoZWFkZXJzIiwiYXR0ciIsInN0YXR1cyIsImJlZm9yZVNlbmQiLCJzdWNjZXNzIiwicmVzcG9uc2UiLCJ3aW5kb3ciLCJsb2NhdGlvbiIsInJlbG9hZCIsImVycm9yIiwianFYSFIiLCJ0ZXh0U3RhdHVzIiwiZXJyb3JUaHJvd24iLCJzaG93Iiwib2ZmIl0sInNvdXJjZXMiOlsid2VicGFjazovLy8uL3Jlc291cmNlcy9qcy9mZWVkYmFjay1pbmRleC5qcz8xMGQwIl0sInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGF1dGhvciBNYXhpbWlsaWFuIE1ld2VzXG4gKlxuICpcbiAqL1xuXG4kKGZ1bmN0aW9uKClcbntcbiAgICBsZXQgYnRuRmVlZGJhY2tSYXRpbmdMaXN0ID0gJCgnLmJ0bi1mZWVkYmFjay1yYXRpbmcnKTtcbiAgICBsZXQgYnRuRmVlZGJhY2tIZWFkZXJMaXN0ID0gJCgnYnV0dG9uW2RhdGEtYmwtZmVlZGJhY2stc3RhdHVzPVwibmV3XCJdJyk7XG5cbiAgICBidG5GZWVkYmFja1JhdGluZ0xpc3QuZWFjaCgoaywgZSkgPT5cbiAgICB7XG4gICAgICAgICQoZSkuY2xpY2soZXZlbnQgPT5cbiAgICAgICAge1xuICAgICAgICAgICAgbGV0IGZlZWRiYWNrSUQgPSAkKGUpLmNsb3Nlc3QoJy5hY2NvcmRpb24taXRlbScpLmRhdGEoJ2JsLWZlZWRiYWNrLWlkJyk7XG5cbiAgICAgICAgICAgICQuYWpheCh7XG4gICAgICAgICAgICAgICAgdXJsOiAnL2ZlZWRiYWNrLycrZmVlZGJhY2tJRCxcbiAgICAgICAgICAgICAgICBtZXRob2Q6ICdQVVQnLFxuICAgICAgICAgICAgICAgIGhlYWRlcnM6IHtcbiAgICAgICAgICAgICAgICAgICAgJ1gtQ1NSRi1UT0tFTic6ICQoJ21ldGFbbmFtZT1cImNzcmYtdG9rZW5cIl0nKS5hdHRyKCdjb250ZW50JyksXG4gICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICBkYXRhOiB7XG4gICAgICAgICAgICAgICAgICAgIHN0YXR1czogJChlKS5kYXRhKCdibC1mZWVkYmFjay1zdGF0dXMnKSxcbiAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgIGJlZm9yZVNlbmQ6IGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICAgICAgc3VjY2VzczogZnVuY3Rpb24ocmVzcG9uc2UpIHtcbiAgICAgICAgICAgICAgICAgICAgLy8gY29uc29sZS5sb2cocmVzcG9uc2UpO1xuICAgICAgICAgICAgICAgICAgICAvLyBtb3N0IGNoZWFwXG4gICAgICAgICAgICAgICAgICAgIHdpbmRvdy5sb2NhdGlvbi5yZWxvYWQoKTtcbiAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgIGVycm9yOiBmdW5jdGlvbihqcVhIUiwgdGV4dFN0YXR1cywgZXJyb3JUaHJvd24pIHtcbiAgICAgICAgICAgICAgICAgICAgLy8gaGFuZGxlIHRoZSBlcnJvciBjYXNlXG4gICAgICAgICAgICAgICAgICAgIC8vIGNvbnNvbGUubG9nKGVycm9yVGhyb3duKTtcbiAgICAgICAgICAgICAgICAgICAgLy8gVE9ET1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9KTtcbiAgICB9KTtcblxuICAgIGJ0bkZlZWRiYWNrSGVhZGVyTGlzdC5lYWNoKChrLCBlKSA9PlxuICAgIHtcbiAgICAgICAgbGV0IGZlZWRiYWNrSUQgPSAkKGUpLmNsb3Nlc3QoJy5hY2NvcmRpb24taXRlbScpLmRhdGEoJ2JsLWZlZWRiYWNrLWlkJyk7XG5cbiAgICAgICAgJChlKS5jbGljayhldmVudCA9PlxuICAgICAgICB7XG4gICAgICAgICAgICAkLmFqYXgoe1xuICAgICAgICAgICAgICAgIHVybDogJy9mZWVkYmFjay8nK2ZlZWRiYWNrSUQsXG4gICAgICAgICAgICAgICAgbWV0aG9kOiAnUFVUJyxcbiAgICAgICAgICAgICAgICBoZWFkZXJzOiB7XG4gICAgICAgICAgICAgICAgICAgICdYLUNTUkYtVE9LRU4nOiAkKCdtZXRhW25hbWU9XCJjc3JmLXRva2VuXCJdJykuYXR0cignY29udGVudCcpLFxuICAgICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICAgICAgZGF0YToge1xuICAgICAgICAgICAgICAgICAgICBzdGF0dXM6ICdzZWVuJyxcbiAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgIGJlZm9yZVNlbmQ6IGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICAgICAgc3VjY2VzczogZnVuY3Rpb24ocmVzcG9uc2UpIHtcbiAgICAgICAgICAgICAgICAgICAgLy8gY29uc29sZS5sb2cocmVzcG9uc2UpO1xuICAgICAgICAgICAgICAgICAgICAkKGUpLmNsb3Nlc3QoJy5mZWVkYmFjay1zZWVuLWljb24nKS5zaG93KCk7XG4gICAgICAgICAgICAgICAgICAgICQoZSkub2ZmKCdjbGljaycpOyAvLyByZW1vdmUgZXZlbnQgbGlzdGVuZXJcbiAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgIGVycm9yOiBmdW5jdGlvbihqcVhIUiwgdGV4dFN0YXR1cywgZXJyb3JUaHJvd24pIHtcbiAgICAgICAgICAgICAgICAgICAgLy8gaGFuZGxlIHRoZSBlcnJvciBjYXNlXG4gICAgICAgICAgICAgICAgICAgIC8vIGNvbnNvbGUubG9nKGVycm9yVGhyb3duKTtcbiAgICAgICAgICAgICAgICAgICAgLy8gVE9ET1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9KTtcbiAgICB9KTtcbn0pO1xuIl0sIm1hcHBpbmdzIjoiQUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBQSxDQUFDLENBQUMsWUFDRjtFQUNJLElBQUlDLHFCQUFxQixHQUFHRCxDQUFDLENBQUMsc0JBQXNCLENBQUM7RUFDckQsSUFBSUUscUJBQXFCLEdBQUdGLENBQUMsQ0FBQyx1Q0FBdUMsQ0FBQztFQUV0RUMscUJBQXFCLENBQUNFLElBQUksQ0FBQyxVQUFDQyxDQUFDLEVBQUVDLENBQUMsRUFDaEM7SUFDSUwsQ0FBQyxDQUFDSyxDQUFDLENBQUMsQ0FBQ0MsS0FBSyxDQUFDLFVBQUFDLEtBQUssRUFDaEI7TUFDSSxJQUFJQyxVQUFVLEdBQUdSLENBQUMsQ0FBQ0ssQ0FBQyxDQUFDLENBQUNJLE9BQU8sQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDQyxJQUFJLENBQUMsZ0JBQWdCLENBQUM7TUFFdkVWLENBQUMsQ0FBQ1csSUFBSSxDQUFDO1FBQ0hDLEdBQUcsRUFBRSxZQUFZLEdBQUNKLFVBQVU7UUFDNUJLLE1BQU0sRUFBRSxLQUFLO1FBQ2JDLE9BQU8sRUFBRTtVQUNMLGNBQWMsRUFBRWQsQ0FBQyxDQUFDLHlCQUF5QixDQUFDLENBQUNlLElBQUksQ0FBQyxTQUFTO1FBQy9ELENBQUM7UUFDREwsSUFBSSxFQUFFO1VBQ0ZNLE1BQU0sRUFBRWhCLENBQUMsQ0FBQ0ssQ0FBQyxDQUFDLENBQUNLLElBQUksQ0FBQyxvQkFBb0I7UUFDMUMsQ0FBQztRQUNETyxVQUFVLEVBQUUsU0FBQUEsV0FBQSxFQUFXLENBQ3ZCLENBQUM7UUFDREMsT0FBTyxFQUFFLFNBQUFBLFFBQVNDLFFBQVEsRUFBRTtVQUN4QjtVQUNBO1VBQ0FDLE1BQU0sQ0FBQ0MsUUFBUSxDQUFDQyxNQUFNLENBQUMsQ0FBQztRQUM1QixDQUFDO1FBQ0RDLEtBQUssRUFBRSxTQUFBQSxNQUFTQyxLQUFLLEVBQUVDLFVBQVUsRUFBRUMsV0FBVyxFQUFFO1VBQzVDO1VBQ0E7VUFDQTtRQUFBO01BRVIsQ0FBQyxDQUFDO0lBQ04sQ0FBQyxDQUFDO0VBQ04sQ0FBQyxDQUFDO0VBRUZ4QixxQkFBcUIsQ0FBQ0MsSUFBSSxDQUFDLFVBQUNDLENBQUMsRUFBRUMsQ0FBQyxFQUNoQztJQUNJLElBQUlHLFVBQVUsR0FBR1IsQ0FBQyxDQUFDSyxDQUFDLENBQUMsQ0FBQ0ksT0FBTyxDQUFDLGlCQUFpQixDQUFDLENBQUNDLElBQUksQ0FBQyxnQkFBZ0IsQ0FBQztJQUV2RVYsQ0FBQyxDQUFDSyxDQUFDLENBQUMsQ0FBQ0MsS0FBSyxDQUFDLFVBQUFDLEtBQUssRUFDaEI7TUFDSVAsQ0FBQyxDQUFDVyxJQUFJLENBQUM7UUFDSEMsR0FBRyxFQUFFLFlBQVksR0FBQ0osVUFBVTtRQUM1QkssTUFBTSxFQUFFLEtBQUs7UUFDYkMsT0FBTyxFQUFFO1VBQ0wsY0FBYyxFQUFFZCxDQUFDLENBQUMseUJBQXlCLENBQUMsQ0FBQ2UsSUFBSSxDQUFDLFNBQVM7UUFDL0QsQ0FBQztRQUNETCxJQUFJLEVBQUU7VUFDRk0sTUFBTSxFQUFFO1FBQ1osQ0FBQztRQUNEQyxVQUFVLEVBQUUsU0FBQUEsV0FBQSxFQUFXLENBQ3ZCLENBQUM7UUFDREMsT0FBTyxFQUFFLFNBQUFBLFFBQVNDLFFBQVEsRUFBRTtVQUN4QjtVQUNBbkIsQ0FBQyxDQUFDSyxDQUFDLENBQUMsQ0FBQ0ksT0FBTyxDQUFDLHFCQUFxQixDQUFDLENBQUNrQixJQUFJLENBQUMsQ0FBQztVQUMxQzNCLENBQUMsQ0FBQ0ssQ0FBQyxDQUFDLENBQUN1QixHQUFHLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQztRQUN2QixDQUFDOztRQUNETCxLQUFLLEVBQUUsU0FBQUEsTUFBU0MsS0FBSyxFQUFFQyxVQUFVLEVBQUVDLFdBQVcsRUFBRTtVQUM1QztVQUNBO1VBQ0E7UUFBQTtNQUVSLENBQUMsQ0FBQztJQUNOLENBQUMsQ0FBQztFQUNOLENBQUMsQ0FBQztBQUNOLENBQUMsQ0FBQyIsImZpbGUiOiIuL3Jlc291cmNlcy9qcy9mZWVkYmFjay1pbmRleC5qcyIsInNvdXJjZVJvb3QiOiIifQ==\n//# sourceURL=webpack-internal:///./resources/js/feedback-index.js\n");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval-source-map devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./resources/js/feedback-index.js"]();
/******/ 	
/******/ })()
;