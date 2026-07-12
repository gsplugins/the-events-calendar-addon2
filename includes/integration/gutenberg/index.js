/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./includes/integration/gutenberg/src/index.js":
/*!*****************************************************!*\
  !*** ./includes/integration/gutenberg/src/index.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./editor.scss */ "./includes/integration/gutenberg/src/editor.scss");

(function (wp) {
  var blocks = wp.blocks,
    serverSideRender = wp.serverSideRender,
    i18n = wp.i18n,
    element = wp.element;
  var registerBlockType = blocks.registerBlockType;
  var ServerSideRender = serverSideRender;
  var __ = i18n.__;
  var el = element.createElement,
    Fragment = element.Fragment;
  var interval;
  var intervalCount = 0;
  function blockServerRenderScript() {
    if (interval) {
      clearInterval(interval);
    }
    intervalCount = 0;
    interval = setInterval(function () {
      if (window.jQuery) {
        window.jQuery(document).trigger('gsteca:scripts:reprocess');
      }
      if (interval && intervalCount > 100) {
        clearInterval(interval);
      }
      intervalCount++;
    }, 200);
  }
  function getDefaultShortcodeId() {
    if (!window.gs_teca_block || !Array.isArray(window.gs_teca_block.shortcodes)) {
      return '';
    }
    return window.gs_teca_block.shortcodes[0] ? String(window.gs_teca_block.shortcodes[0].id) : '';
  }
  function shortcodeExists(shortcodeId) {
    if (!window.gs_teca_block || !Array.isArray(window.gs_teca_block.shortcodes)) {
      return false;
    }
    return window.gs_teca_block.shortcodes.some(function (item) {
      return String(item.id) === String(shortcodeId);
    });
  }
  function BlockIcon() {
    return el('svg', {
      width: 24,
      height: 24,
      viewBox: '0 0 24 24',
      xmlns: 'http://www.w3.org/2000/svg',
      'aria-hidden': true,
      focusable: false
    }, el('path', {
      d: 'M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z'
    }));
  }
  function BlockDisplay(props) {
    var attributes = props.attributes,
      setAttributes = props.setAttributes,
      className = props.className;
    var shortcodeId = attributes.shortcode || '';
    var shortcodes = window.gs_teca_block && window.gs_teca_block.shortcodes || [];
    blockServerRenderScript();
    if (!shortcodes.length) {
      return el('div', {
        className: 'teca--block-placeholder'
      }, window.gs_teca_block.no_shortcodes_available);
    }
    function updateShortcodeId(event) {
      setAttributes({
        shortcode: event.target.value
      });
    }
    var options = shortcodes.map(function (item) {
      return el('option', {
        value: String(item.id),
        key: String(item.id)
      }, item.shortcode_name);
    });
    var selectedId = shortcodeId || getDefaultShortcodeId();
    var hasValidSelection = selectedId && shortcodeExists(selectedId);
    return el('div', {
      className: 'teca--block'
    }, el('div', {
      className: 'teca--toolbar'
    }, el('label', null, window.gs_teca_block.select_shortcode), el('select', {
      onChange: updateShortcodeId,
      value: selectedId
    }, options), el('p', {
      className: 'gs-teca-block--des'
    }, el('span', null, window.gs_teca_block.edit_description_text + ' ', el('a', {
      href: window.gs_teca_block.edit_link + selectedId,
      target: '_blank',
      rel: 'noopener noreferrer'
    }, window.gs_teca_block.edit_link_text)), el('span', null, window.gs_teca_block.create_description_text + ' ', el('a', {
      href: window.gs_teca_block.create_link,
      target: '_blank',
      rel: 'noopener noreferrer'
    }, window.gs_teca_block.create_link_text)))), hasValidSelection ? el(ServerSideRender, {
      className: className,
      block: 'teca/events',
      attributes: {
        shortcode: selectedId,
        align: attributes.align
      }
    }) : el('div', {
      className: 'teca--block-placeholder'
    }, shortcodeId ? window.gs_teca_block.shortcode_missing : window.gs_teca_block.no_shortcode_selected));
  }
  registerBlockType('teca/events', {
    title: __('TECA Events', 'the-events-calendar-addon2'),
    description: __('Insert and display TECA event layouts.', 'the-events-calendar-addon2'),
    icon: BlockIcon,
    category: 'widgets',
    keywords: [__('events', 'the-events-calendar-addon2'), __('calendar', 'the-events-calendar-addon2'), 'teca'],
    supports: {
      align: ['wide', 'full']
    },
    attributes: {
      shortcode: {
        type: 'string',
        "default": getDefaultShortcodeId()
      },
      align: {
        type: 'string',
        "default": 'wide'
      }
    },
    edit: BlockDisplay,
    save: function save() {
      return null;
    }
  });
})(window.wp);

/***/ }),

/***/ "./includes/integration/gutenberg/src/editor.scss":
/*!********************************************************!*\
  !*** ./includes/integration/gutenberg/src/editor.scss ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


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
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
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
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"/includes/integration/gutenberg/index": 0,
/******/ 			"includes/integration/gutenberg/editor": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunkthe_events_calendar_addon"] = self["webpackChunkthe_events_calendar_addon"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["includes/integration/gutenberg/editor"], () => (__webpack_require__("./includes/integration/gutenberg/src/index.js")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;