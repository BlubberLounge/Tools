/*
 * ATTENTION: An "eval-source-map" devtool has been used.
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file with attached SourceMaps in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/js/IEC7064.js":
/*!*********************************!*\
  !*** ./resources/js/IEC7064.js ***!
  \*********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* binding */ IEC7064)\n/* harmony export */ });\nfunction _typeof(o) { \"@babel/helpers - typeof\"; return _typeof = \"function\" == typeof Symbol && \"symbol\" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && \"function\" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? \"symbol\" : typeof o; }, _typeof(o); }\nfunction _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError(\"Cannot call a class as a function\"); } }\nfunction _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if (\"value\" in descriptor) descriptor.writable = true; Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor); } }\nfunction _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, \"prototype\", { writable: false }); return Constructor; }\nfunction _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }\nfunction _toPropertyKey(arg) { var key = _toPrimitive(arg, \"string\"); return _typeof(key) === \"symbol\" ? key : String(key); }\nfunction _toPrimitive(input, hint) { if (_typeof(input) !== \"object\" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || \"default\"); if (_typeof(res) !== \"object\") return res; throw new TypeError(\"@@toPrimitive must return a primitive value.\"); } return (hint === \"string\" ? String : Number)(input); }\nvar IEC7064 = /*#__PURE__*/function () {\n  function IEC7064() {\n    _classCallCheck(this, IEC7064);\n    _defineProperty(this, \"alphabet\", Array.from('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'));\n  }\n  _createClass(IEC7064, [{\n    key: \"IEC7064\",\n    value: function IEC7064() {\n      //\n    }\n\n    /**\n     * MOD 37, 36\n     */\n  }, {\n    key: \"checksum\",\n    value: function checksum(input) {\n      input = Array.from(input);\n      var m = 37;\n      var n = 36;\n      var produkt = n;\n      for (var i = 0; i < input.length; i++) {\n        var c = this.alphabet.indexOf(input[i]);\n        var sum = this.mod(c + produkt, n);\n        sum = sum == 0 ? n : sum;\n        produkt = this.mod(sum * 2, m);\n      }\n      var ret = m - produkt;\n      return this.alphabet[ret == n ? 0 : ret];\n    }\n  }, {\n    key: \"verify\",\n    value: function verify(input) {\n      input = input.split('');\n      return input.slice(-1)[0] === this.checksum(input.slice(0, -1));\n    }\n  }, {\n    key: \"mod\",\n    value: function mod(n, m) {\n      return (n % m + m) % m;\n    }\n  }]);\n  return IEC7064;\n}();\n//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9yZXNvdXJjZXMvanMvSUVDNzA2NC5qcyIsIm1hcHBpbmdzIjoiOzs7Ozs7Ozs7OztJQUNxQkEsT0FBTztFQUFBLFNBQUFBLFFBQUE7SUFBQUMsZUFBQSxPQUFBRCxPQUFBO0lBQUFFLGVBQUEsbUJBRWJDLEtBQUssQ0FBQ0MsSUFBSSxDQUFDLHNDQUFzQyxDQUFDO0VBQUE7RUFBQUMsWUFBQSxDQUFBTCxPQUFBO0lBQUFNLEdBQUE7SUFBQUMsS0FBQSxFQUU3RCxTQUFBUCxRQUFBLEVBQ0E7TUFDSTtJQUFBOztJQUdKO0FBQ0o7QUFDQTtFQUZJO0lBQUFNLEdBQUE7SUFBQUMsS0FBQSxFQUdBLFNBQUFDLFNBQVNDLEtBQUssRUFDZDtNQUNJQSxLQUFLLEdBQUdOLEtBQUssQ0FBQ0MsSUFBSSxDQUFDSyxLQUFLLENBQUM7TUFDekIsSUFBSUMsQ0FBQyxHQUFHLEVBQUU7TUFDVixJQUFJQyxDQUFDLEdBQUcsRUFBRTtNQUNWLElBQUlDLE9BQU8sR0FBR0QsQ0FBQztNQUNmLEtBQUksSUFBSUUsQ0FBQyxHQUFHLENBQUMsRUFBRUEsQ0FBQyxHQUFHSixLQUFLLENBQUNLLE1BQU0sRUFBRUQsQ0FBQyxFQUFFLEVBQ3BDO1FBQ0ksSUFBSUUsQ0FBQyxHQUFHLElBQUksQ0FBQ0MsUUFBUSxDQUFDQyxPQUFPLENBQUNSLEtBQUssQ0FBQ0ksQ0FBQyxDQUFDLENBQUM7UUFDdkMsSUFBSUssR0FBRyxHQUFHLElBQUksQ0FBQ0MsR0FBRyxDQUFFSixDQUFDLEdBQUdILE9BQU8sRUFBR0QsQ0FBQyxDQUFDO1FBQ3BDTyxHQUFHLEdBQUdBLEdBQUcsSUFBSSxDQUFDLEdBQUdQLENBQUMsR0FBR08sR0FBRztRQUN4Qk4sT0FBTyxHQUFHLElBQUksQ0FBQ08sR0FBRyxDQUFFRCxHQUFHLEdBQUcsQ0FBQyxFQUFHUixDQUFDLENBQUM7TUFDcEM7TUFDQSxJQUFJVSxHQUFHLEdBQUdWLENBQUMsR0FBR0UsT0FBTztNQUNyQixPQUFPLElBQUksQ0FBQ0ksUUFBUSxDQUFDSSxHQUFHLElBQUlULENBQUMsR0FBRyxDQUFDLEdBQUdTLEdBQUcsQ0FBQztJQUM1QztFQUFDO0lBQUFkLEdBQUE7SUFBQUMsS0FBQSxFQUVELFNBQUFjLE9BQU9aLEtBQUssRUFDWjtNQUNJQSxLQUFLLEdBQUdBLEtBQUssQ0FBQ2EsS0FBSyxDQUFDLEVBQUUsQ0FBQztNQUN2QixPQUFPYixLQUFLLENBQUNjLEtBQUssQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxLQUFLLElBQUksQ0FBQ2YsUUFBUSxDQUFDQyxLQUFLLENBQUNjLEtBQUssQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLENBQUMsQ0FBQztJQUNuRTtFQUFDO0lBQUFqQixHQUFBO0lBQUFDLEtBQUEsRUFFRCxTQUFBWSxJQUFJUixDQUFDLEVBQUVELENBQUMsRUFBRTtNQUNOLE9BQU8sQ0FBRUMsQ0FBQyxHQUFHRCxDQUFDLEdBQUlBLENBQUMsSUFBSUEsQ0FBQztJQUM1QjtFQUFDO0VBQUEsT0FBQVYsT0FBQTtBQUFBIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vLy4vcmVzb3VyY2VzL2pzL0lFQzcwNjQuanM/YjI1NCJdLCJzb3VyY2VzQ29udGVudCI6WyJcbmV4cG9ydCBkZWZhdWx0IGNsYXNzIElFQzcwNjRcbntcbiAgICBhbHBoYWJldCA9IEFycmF5LmZyb20oJzAxMjM0NTY3ODlBQkNERUZHSElKS0xNTk9QUVJTVFVWV1hZWicpO1xuXG4gICAgSUVDNzA2NCgpXG4gICAge1xuICAgICAgICAvL1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIE1PRCAzNywgMzZcbiAgICAgKi9cbiAgICBjaGVja3N1bShpbnB1dClcbiAgICB7XG4gICAgICAgIGlucHV0ID0gQXJyYXkuZnJvbShpbnB1dCk7XG4gICAgICAgIGxldCBtID0gMzc7XG4gICAgICAgIGxldCBuID0gMzY7XG4gICAgICAgIGxldCBwcm9kdWt0ID0gbjtcbiAgICAgICAgZm9yKGxldCBpID0gMDsgaSA8IGlucHV0Lmxlbmd0aDsgaSsrKVxuICAgICAgICB7XG4gICAgICAgICAgICBsZXQgYyA9IHRoaXMuYWxwaGFiZXQuaW5kZXhPZihpbnB1dFtpXSk7XG4gICAgICAgICAgICBsZXQgc3VtID0gdGhpcy5tb2QoKGMgKyBwcm9kdWt0KSwgbik7XG4gICAgICAgICAgICBzdW0gPSBzdW0gPT0gMCA/IG4gOiBzdW07XG4gICAgICAgICAgICBwcm9kdWt0ID0gdGhpcy5tb2QoKHN1bSAqIDIpLCBtKTtcbiAgICAgICAgfVxuICAgICAgICBsZXQgcmV0ID0gbSAtIHByb2R1a3Q7XG4gICAgICAgIHJldHVybiB0aGlzLmFscGhhYmV0W3JldCA9PSBuID8gMCA6IHJldF07XG4gICAgfVxuXG4gICAgdmVyaWZ5KGlucHV0KVxuICAgIHtcbiAgICAgICAgaW5wdXQgPSBpbnB1dC5zcGxpdCgnJyk7XG4gICAgICAgIHJldHVybiBpbnB1dC5zbGljZSgtMSlbMF0gPT09IHRoaXMuY2hlY2tzdW0oaW5wdXQuc2xpY2UoMCwgLTEpKTtcbiAgICB9XG5cbiAgICBtb2QobiwgbSkge1xuICAgICAgICByZXR1cm4gKChuICUgbSkgKyBtKSAlIG07XG4gICAgfVxufVxuIl0sIm5hbWVzIjpbIklFQzcwNjQiLCJfY2xhc3NDYWxsQ2hlY2siLCJfZGVmaW5lUHJvcGVydHkiLCJBcnJheSIsImZyb20iLCJfY3JlYXRlQ2xhc3MiLCJrZXkiLCJ2YWx1ZSIsImNoZWNrc3VtIiwiaW5wdXQiLCJtIiwibiIsInByb2R1a3QiLCJpIiwibGVuZ3RoIiwiYyIsImFscGhhYmV0IiwiaW5kZXhPZiIsInN1bSIsIm1vZCIsInJldCIsInZlcmlmeSIsInNwbGl0Iiwic2xpY2UiLCJkZWZhdWx0Il0sInNvdXJjZVJvb3QiOiIifQ==\n//# sourceURL=webpack-internal:///./resources/js/IEC7064.js\n");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The require scope
/******/ 	var __webpack_require__ = {};
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval-source-map devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./resources/js/IEC7064.js"](0, __webpack_exports__, __webpack_require__);
/******/ 	
/******/ })()
;