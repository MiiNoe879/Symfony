var VoxImplant =
/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/build";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 30);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

Object.defineProperty(exports, "__esModule", { value: true });
/**
 * @hidden
 */
var LogLevel;
(function (LogLevel) {
    LogLevel[LogLevel["NONE"] = 0] = "NONE";
    LogLevel[LogLevel["ERROR"] = 1] = "ERROR";
    LogLevel[LogLevel["WARNING"] = 2] = "WARNING";
    LogLevel[LogLevel["INFO"] = 3] = "INFO";
    LogLevel[LogLevel["TRACE"] = 4] = "TRACE";
})(LogLevel = exports.LogLevel || (exports.LogLevel = {}));
/**
 * @hidden
 */
var LogCategory;
(function (LogCategory) {
    LogCategory[LogCategory["SIGNALING"] = 0] = "SIGNALING";
    LogCategory[LogCategory["RTC"] = 1] = "RTC";
    LogCategory[LogCategory["USERMEDIA"] = 2] = "USERMEDIA";
    LogCategory[LogCategory["CALL"] = 3] = "CALL";
    LogCategory[LogCategory["CALLEXP2P"] = 4] = "CALLEXP2P";
    LogCategory[LogCategory["CALLEXSERVER"] = 5] = "CALLEXSERVER";
    LogCategory[LogCategory["CALLMANAGER"] = 6] = "CALLMANAGER";
    LogCategory[LogCategory["CLIENT"] = 7] = "CLIENT";
    LogCategory[LogCategory["AUTHENTICATOR"] = 8] = "AUTHENTICATOR";
    LogCategory[LogCategory["PCFACTORY"] = 9] = "PCFACTORY";
    LogCategory[LogCategory["UTILS"] = 10] = "UTILS";
    LogCategory[LogCategory["ORTC"] = 11] = "ORTC";
    LogCategory[LogCategory["MESSAGING"] = 12] = "MESSAGING";
    LogCategory[LogCategory["REINVITEQ"] = 13] = "REINVITEQ";
    LogCategory[LogCategory["HARDWARE"] = 14] = "HARDWARE";
})(LogCategory = exports.LogCategory || (exports.LogCategory = {}));
/**
* The client states
*/
var ClientState;
(function (ClientState) {
    /**
    * The client is currently disconnected
    */
    ClientState[ClientState["DISCONNECTED"] = "DISCONNECTED"] = "DISCONNECTED";
    /**
    * The client is currently connecting
    */
    ClientState[ClientState["CONNECTING"] = "CONNECTING"] = "CONNECTING";
    /**
    * The client is currently connected
    */
    ClientState[ClientState["CONNECTED"] = "CONNECTED"] = "CONNECTED";
    /**
    * The client is currently logging in
    */
    ClientState[ClientState["LOGGING_IN"] = "LOGGING_IN"] = "LOGGING_IN";
    /**
    * The client is currently logged in
    */
    ClientState[ClientState["LOGGED_IN"] = "LOGGED_IN"] = "LOGGED_IN";
})(ClientState = exports.ClientState || (exports.ClientState = {}));
/**
 * Common logger
 * @hidden
 */

var Logger = function () {
    function Logger(category, label, provider) {
        _classCallCheck(this, Logger);

        this.category = category;
        this.label = label;
        this.provider = provider;
    }

    _createClass(Logger, [{
        key: "log",
        value: function log(level, message) {
            this.provider.writeMessage(this.category, this.label, level, message);
        }
    }, {
        key: "error",
        value: function error(message) {
            this.log(LogLevel.ERROR, message);
        }
    }, {
        key: "warning",
        value: function warning(message) {
            this.log(LogLevel.WARNING, message);
        }
    }, {
        key: "info",
        value: function info(message) {
            this.log(LogLevel.INFO, message);
        }
    }, {
        key: "trace",
        value: function trace(message) {
            this.log(LogLevel.TRACE, message);
        }
    }]);

    return Logger;
}();

exports.Logger = Logger;
/**
 * @hidden
 */

var LogManager = function () {
    function LogManager() {
        _classCallCheck(this, LogManager);

        this._shadowLogging = false;
        this.levels = {};
    }

    _createClass(LogManager, [{
        key: "getSLog",
        value: function getSLog() {
            return this._shadowLog;
        }
    }, {
        key: "setPrettyPrint",
        value: function setPrettyPrint(state) {
            this.prettyPrint = state;
        }
    }, {
        key: "setLogLevel",
        value: function setLogLevel(category, level) {
            this.levels[LogCategory[category]] = level;
        }
    }, {
        key: "writeMessage",
        value: function writeMessage(category, label, level, message) {
            LogManager.tick++;
            var sampleMessage = "VIWSLR " + LogManager.tick + " " + new Date().toString() + " " + LogLevel[level] + " " + label + ": " + message;
            var currentLevel = LogLevel.NONE;
            if (typeof this.levels[LogCategory[category]] != "undefined") currentLevel = this.levels[LogCategory[category]];
            if (level <= currentLevel) {
                if (typeof console.debug != "undefined" && typeof console.info != "undefined" && typeof console.error != "undefined" && typeof console.warn != "undefined") {
                    if (this.prettyPrint) {
                        if (typeof message != "string") message = JSON.stringify(message);
                        var formatedMessage = "%c VIWSLR " + LogManager.tick + " " + new Date().toUTCString() + " [" + LogLevel[level] + "] %c" + label + ": %c" + message.replace("\r\n", "<br>");
                        if (level === LogLevel.ERROR) console.error(sampleMessage);else if (level === LogLevel.WARNING) console.warn(formatedMessage, 'color:#ccc', 'color:#2375a2', 'color:#000');else if (level === LogLevel.INFO) console.info(formatedMessage, 'color:#ccc', 'color:#2375a2', 'color:#000');else if (level === LogLevel.TRACE) console.log(formatedMessage, 'color:#ccc', 'color:#2375a2', 'color:#000');else console.log(formatedMessage, 'color:#ccc', 'color:#2375a2', 'color:#000');
                    } else {
                        if (level === LogLevel.ERROR) console.error(sampleMessage);else if (level === LogLevel.WARNING) console.warn(sampleMessage);else if (level === LogLevel.INFO) console.info(sampleMessage);else if (level === LogLevel.TRACE) console.debug(sampleMessage);else console.log(sampleMessage);
                    }
                } else console.log(sampleMessage);
            }
            if (this.shadowLogging) {
                this._shadowLog.push(sampleMessage);
            }
        }
    }, {
        key: "createLogger",
        value: function createLogger(category, label) {
            return new Logger(category, label, this);
        }
        //# decorator 4 trace

    }, {
        key: "_traceName",

        /**
         * @hidden
         * @return {string}
         * @private
         */
        value: function _traceName() {
            return 'Logger';
        }
    }, {
        key: "shadowLogging",
        set: function set(flag) {
            if (!this._shadowLogging) this._shadowLog = [];
            this._shadowLogging = flag;
        },
        get: function get() {
            return this._shadowLogging;
        }
    }], [{
        key: "get",
        value: function get() {
            if (typeof this.inst == "undefined") {
                this.inst = new LogManager();
                this.inst.prettyPrint = false;
            }
            return this.inst;
        }
    }, {
        key: "d_trace",
        value: function d_trace(category) {
            return function (target, key, _value) {
                return {
                    value: function value() {
                        var a = "";

                        for (var _len = arguments.length, args = Array(_len), _key = 0; _key < _len; _key++) {
                            args[_key] = arguments[_key];
                        }

                        try {
                            a = args.map(function (a) {
                                return JSON.stringify(a);
                            }).join();
                        } catch (e) {
                            a = "circular structure";
                        }
                        var className = '';
                        if (target._traceName) className = target._traceName();
                        LogManager.get().writeMessage(category, className, LogLevel.TRACE, key + "(" + a + ")");
                        var result = _value.value.apply(this, args);
                        return result;
                    }
                };
            };
        }
    }]);

    return LogManager;
}();

LogManager.tick = 0;
exports.LogManager = LogManager;

/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var __decorate = undefined && undefined.__decorate || function (decorators, target, key, desc) {
    var c = arguments.length,
        r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc,
        d;
    if ((typeof Reflect === "undefined" ? "undefined" : _typeof(Reflect)) === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);else for (var i = decorators.length - 1; i >= 0; i--) {
        if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    }return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var Logger_1 = __webpack_require__(0);
var BrowserSpecific_1 = __webpack_require__(5);
var PCFactory_1 = __webpack_require__(8);
var CallManager_1 = __webpack_require__(6);
var RemoteFunction_1 = __webpack_require__(2);
var RemoteEvent_1 = __webpack_require__(12);
var MsgSignaling_1 = __webpack_require__(25);
var Client_1 = __webpack_require__(3);
/**
 * @hidden
 */
var VoxSignalingState;
(function (VoxSignalingState) {
    VoxSignalingState[VoxSignalingState["IDLE"] = 0] = "IDLE";
    VoxSignalingState[VoxSignalingState["CONNECTING"] = 1] = "CONNECTING";
    VoxSignalingState[VoxSignalingState["WSCONNECTED"] = 2] = "WSCONNECTED";
    VoxSignalingState[VoxSignalingState["CONNECTED"] = 3] = "CONNECTED";
    VoxSignalingState[VoxSignalingState["CLOSING"] = 4] = "CLOSING";
})(VoxSignalingState = exports.VoxSignalingState || (exports.VoxSignalingState = {}));
/**
 * Websocket-based implementation of signaling protocol
 * Singleton
 * IDLE => CONNECTING => WSCONNECTED => CONNECTED => CLOSING => IDLE
 *                 ||        ||      /\           \--(close() called)
 * (WS connection  ||        ||       |
 *      failed)    \/        ||       \-- (__connectionSuccessful RPC)
 *                IDLE       ||
 *                           ||
 * (__connectionFailed RPC)  ||
 *                           ||
 *                           \/
 *                          IDLE
 *
 * (Simplified graph)
 *
 * @hidden
 */

var VoxSignaling = function () {
    function VoxSignaling() {
        var _this = this;

        _classCallCheck(this, VoxSignaling);

        /**
         * ver 2 - old version
         * ver 3 - new call scheme
         * @type {string}
         */
        this.ver = "3";
        this.handlers = [];
        this.rpcHandlers = {};
        /**
         * Link for ping timer
         * @type {null}
         */
        this.pingTimer = null;
        /**
         * Link for pong await timer
         * @type {null}
         */
        this.pongTimer = null;
        this.manualDisconnect = false;
        this.platform = 'platform';
        this.referrer = 'platform';
        this.extra = '';
        this.closing = false;
        this.writeLog = false;
        this._opLog = [];
        this.token = '';
        this.log = Logger_1.LogManager.get().createLogger(Logger_1.LogCategory.SIGNALING, "VoxSignaling");
        this.currentState = VoxSignalingState.IDLE;
        this.setRPCHandler(RemoteEvent_1.RemoteEvent.connectionSuccessful, function (token) {
            _this.onConnectionSuccessfulRPC(token);
        });
        this.setRPCHandler(RemoteEvent_1.RemoteEvent.connectionFailed, function () {
            _this.onConnectionFailedRPC();
        });
        this.setRPCHandler(RemoteEvent_1.RemoteEvent.createConnection, function (token) {
            _this.onConnectionSuccessfulRPC(token);
        });
    }

    _createClass(VoxSignaling, [{
        key: "addHandler",

        /**
         * Add signaling event handler
         * @param h
         */
        value: function addHandler(h) {
            this.handlers.push(h);
        }
        /**
         * Disconnect WS and run onWSClosed
         */

    }, {
        key: "close",
        value: function close() {
            this.closing = true;
            if (this.ws) {
                this.ws.onclose = null;
                this.ws.close();
                this.onWSClosed(null);
            } else {
                this.log.warning("Try close unused WS in state " + VoxSignalingState[this.currentState]);
            }
        }
        /**
         * clear ping&pong timeouts
         */

    }, {
        key: "cleanup",
        value: function cleanup() {
            PCFactory_1.PCFactory.get().closeAll();
            if (this.pingTimer) clearTimeout(this.pingTimer);
            if (this.pongTimer) clearTimeout(this.pongTimer);
        }
        /**
         * Change synthetical state and fire userEvent wher WS connecting
         */

    }, {
        key: "onConnectionSuccessfulRPC",
        value: function onConnectionSuccessfulRPC(token) {
            if (this.currentState != VoxSignalingState.WSCONNECTED) {
                this.log.error("Can't handle __connectionSuccessful while in state " + VoxSignalingState[this.currentState]);
                return;
            }
            if (token) this.token = token;
            this.currentState = VoxSignalingState.CONNECTED;
            if (this.handlers.length > 0) {
                for (var i = 0; i < this.handlers.length; ++i) {
                    try {
                        this.handlers[i].onSignalingConnected();
                    } catch (e) {
                        this.log.warning("Error in onSignalingConnected callback: " + e);
                    }
                }
            } else {
                this.log.warning("No VoxSignaling handler specified");
            }
        }
        /**
         * Change synthetical state and fire userEvent wher WS disconnecting
         */

    }, {
        key: "onConnectionFailedRPC",
        value: function onConnectionFailedRPC() {
            if (this.currentState != VoxSignalingState.WSCONNECTED) {
                this.log.error("Can't handle __connectionSuccessful while in state " + VoxSignalingState[this.currentState]);
                return;
            }
            this.ws.onerror = null;
            this.ws.close();
            this.ws = null;
            this.currentState = VoxSignalingState.IDLE;
            if (this.handlers.length > 0) {
                for (var i = 0; i < this.handlers.length; ++i) {
                    try {
                        this.handlers[i].onMediaConnectionFailed();
                    } catch (e) {
                        this.log.warning("Error in onMediaConnectionFailed callback: " + e);
                    }
                }
            } else {
                this.log.warning("No VoxSignaling handler specified");
            }
        }
        /**
         * Connect to selected WS server and bind WSEvents
         * @param host
         * @param isVideo
         * @param secure
         * @param connectivityCheck
         * @param version
         */

    }, {
        key: "connectTo",
        value: function connectTo(host) {
            var isVideo = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
            var secure = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;

            var _this2 = this;

            var connectivityCheck = arguments[3];
            var version = arguments[4];

            //Sentry.setLastHost(host);
            this.manualDisconnect = false;
            this.ver = version;
            if (this.currentState != VoxSignalingState.IDLE) {
                this.log.error("Can't establish connection while in state " + VoxSignalingState[this.currentState]);
                return;
            }
            this.currentState = VoxSignalingState.CONNECTING;
            var browser = BrowserSpecific_1.default.getWSVendor(connectivityCheck);
            this.ws = new WebSocket("ws" + (secure ? 's' : '') + "://" + host + "/" + this.platform + "?version=" + this.ver + "&client=" + browser + "&referrer=&extra=" + this.extra + "&video=" + (isVideo ? "true" : "false") + "&client_version=" + Client_1.Client.getInstance().version);
            this.ws.onopen = function (e) {
                return _this2.onWSConnected();
            };
            this.ws.onclose = function (e) {
                return _this2.onWSClosed(e);
            };
            this.ws.onerror = function (e) {
                return _this2.onWSError();
            };
            this.ws.onmessage = function (e) {
                return _this2.onWSData(e.data);
            };
        }
        /**
         * Set handler for Server -> Client RPC
         */

    }, {
        key: "setRPCHandler",
        value: function setRPCHandler(name, callback) {
            if (typeof this.rpcHandlers[name] != "undefined") {
                this.log.warning("Overwriting RPC handler for function " + name);
            }
            this.rpcHandlers[name] = callback;
        }
        /**
         * Set handler for Server -> Client RPC
         * @param name
         */

    }, {
        key: "removeRPCHandler",
        value: function removeRPCHandler(name) {
            if (typeof this.rpcHandlers[name] == "undefined" && !this.closing) {
                this.log.warning("There is no RPC handler for function " + name);
            }
            delete this.rpcHandlers[name];
        }
        /**
         * Invoke Client->Server RPC
         * @param name
         * @param params
         */

    }, {
        key: "callRemoteFunction",
        value: function callRemoteFunction(name) {
            for (var _len = arguments.length, params = Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
                params[_key - 1] = arguments[_key];
            }

            if (this.currentState != VoxSignalingState.CONNECTED && this.currentState != VoxSignalingState.WSCONNECTED) {
                if (!this.closing) this.log.error("Can't make a RPC call in state " + VoxSignalingState[this.currentState]);
                return false;
            }
            if (typeof this.ws != "undefined") {
                if (this.writeLog) this._opLog.push("send:" + JSON.stringify({ "name": name, "params": params }));
                var data = JSON.stringify({ "name": name, "params": params });
                this.ws.send(data);
                Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.SIGNALING, '[wsdataout]', Logger_1.LogLevel.INFO, data);
                return true;
            }
        }
        /**
         * WebSocket callbacks
         */

    }, {
        key: "onWSData",
        value: function onWSData(data) {
            Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.SIGNALING, '[wsdatain]', Logger_1.LogLevel.INFO, data);
            if (this.writeLog) this._opLog.push("recv:" + data);
            var parsedData = void 0;
            try {
                parsedData = JSON.parse(data);
            } catch (e) {
                this.log.error("Can't parse JSON data: " + data);
                return;
            }
            if (typeof parsedData['service'] != "undefined") this.onWSMessData(parsedData);else this.onWSVoipData(parsedData);
        }
        /**
         * Handle new messaging protocol
         * @hidden
         * @param parsedData
         */

    }, {
        key: "onWSMessData",
        value: function onWSMessData(parsedData) {
            MsgSignaling_1.MsgSignaling.get().handleWsData(parsedData);
        }
        /**
         * Send WS message to default old flow
         * @hidden
         * @param parsedData
         */

    }, {
        key: "onWSVoipData",
        value: function onWSVoipData(parsedData) {
            var functionName = parsedData["name"];
            var callParams = parsedData["params"];
            if (typeof this.rpcHandlers[functionName] != "undefined") {
                try {
                    this.rpcHandlers[functionName].apply(null, callParams);
                } catch (e) {
                    this.log.warning("Error in '" + functionName + "' handler : " + e);
                }
            } else {
                this.log.warning("No handler for " + functionName);
            }
        }
        /**
         * Manually disconnect transport proto
         */

    }, {
        key: "disconnect",
        value: function disconnect() {
            this.closing = true;
            this.manualDisconnect = true;
            this.onWSClosed(null);
            this.cleanup();
        }
    }, {
        key: "onWSClosed",
        value: function onWSClosed(e) {
            if (this.currentState != VoxSignalingState.CONNECTED && this.currentState != VoxSignalingState.CONNECTING && this.currentState != VoxSignalingState.CLOSING) {
                if (!this.closing) this.log.warning("onWSClosed in state " + VoxSignalingState[this.currentState]);else return;
            }
            if (this.ws) {
                this.ws.close();
                this.ws = undefined;
            }
            var oldState = this.currentState;
            //unbind __ping and __pong timeouts
            if (this.pingTimer) {
                clearTimeout(this.pingTimer);
            }
            if (this.pongTimer) {
                clearTimeout(this.pongTimer);
            }
            this.cleanup();
            this.currentState = VoxSignalingState.IDLE;
            if (this.handlers.length > 0) {
                for (var i = 0; i < this.handlers.length; ++i) {
                    if ((oldState == VoxSignalingState.CONNECTING || oldState == VoxSignalingState.WSCONNECTED || oldState == VoxSignalingState.IDLE) && !this.manualDisconnect) {
                        try {
                            this.handlers[i].onSignalingConnectionFailed(e.reason);
                        } catch (e) {
                            this.log.warning("Error in onSignalingConnectionFailed callback: " + e);
                        }
                    } else {
                        try {
                            this.handlers[i].onSignalingClosed();
                        } catch (e) {
                            this.log.warning("Error in onSignalingClosed callback: " + e);
                        }
                    }
                }
            } else {
                this.log.warning("No VoxSignaling handler specified");
            }
        }
    }, {
        key: "onWSConnected",
        value: function onWSConnected() {
            var _this3 = this;

            this.closing = false;
            if (this.currentState != VoxSignalingState.CONNECTING) {
                this.log.warning("onWSConnected in state " + VoxSignalingState[this.currentState]);
            }
            this.currentState = VoxSignalingState.WSCONNECTED;
            this.pingTimer = window.setTimeout(function () {
                return _this3.doPing();
            }, VoxSignaling.PING_DELAY);
            //Set inner message handlers
            this.setRPCHandler(RemoteEvent_1.RemoteEvent.pong, function () {
                return _this3.pongReceived();
            });
            //Set deprecated message handlers
            this.setRPCHandler(RemoteEvent_1.RemoteEvent.increaseGain, function () {
                _this3.log.info("Deprecated increaseGain");
            });
        }
        /**
         * Event for error on main signaling socket
         */

    }, {
        key: "onWSError",
        value: function onWSError() {
            if (this.currentState != VoxSignalingState.CONNECTING) {
                this.log.warning("onWSError in state " + this.currentState);
            }
            this.ws.close();
            this.ws = undefined;
            //unbind __ping and __pong timeouts
            if (this.pingTimer) {
                clearTimeout(this.pingTimer);
            }
            if (this.pongTimer) {
                clearTimeout(this.pongTimer);
            }
            this.cleanup();
            this.currentState = VoxSignalingState.IDLE;
            if (typeof this.handlers != "undefined") {
                for (var i = 0; i < this.handlers.length; ++i) {
                    try {
                        this.handlers[i].onSignalingConnectionFailed("Error connecting to VoxImplant server");
                    } catch (e) {
                        this.log.warning("Error in onSignalingConnectionFailed callback: " + e);
                    }
                }
            } else {
                this.log.warning("No VoxSignaling handler specified");
            }
        }
        /**
         * Fx run every PING_TIMEOUT ms
         */

    }, {
        key: "doPing",
        value: function doPing() {
            var _this4 = this;

            this.pingTimer = null;
            this.callRemoteFunction(RemoteFunction_1.RemoteFunction.ping, []);
            this.pongTimer = window.setTimeout(function () {
                if (CallManager_1.CallManager.get().numCalls > 0) {
                    _this4.pongReceived();
                    return;
                }
                _this4.pongTimer = null;
                for (var i = 0; i < _this4.handlers.length; ++i) {
                    if (_this4.currentState == VoxSignalingState.CONNECTED) {
                        try {
                            _this4.handlers[i].onSignalingClosed();
                        } catch (e) {
                            _this4.log.warning("Error in onSignalingClosed callback: " + e);
                        }
                    } else {
                        try {
                            _this4.handlers[i].onSignalingConnectionFailed("Connection closed");
                        } catch (e) {
                            _this4.log.warning("Error in onSignalingConnectionFailed callback: " + e);
                        }
                    }
                }
                _this4.currentState = VoxSignalingState.IDLE;
            }, VoxSignaling.PING_DELAY);
        }
        /**
         * Reciver for pong
         * @see doPing()
         */

    }, {
        key: "pongReceived",
        value: function pongReceived() {
            var _this5 = this;

            if (this.pongTimer) {
                clearTimeout(this.pongTimer);
                this.pongTimer = null;
                this.pingTimer = window.setTimeout(function () {
                    return _this5.doPing();
                }, VoxSignaling.PING_DELAY);
            }
        }
        /**
         *
         * @param {MsgBusMessage} data
         * @returns {boolean}
         */

    }, {
        key: "sendRaw",
        value: function sendRaw(data) {
            if (this.writeLog) this._opLog.push("send:" + JSON.stringify(data));
            var xdata = JSON.stringify(data);
            this.ws.send(xdata);
            Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.SIGNALING, '[wsdataout]', Logger_1.LogLevel.INFO, xdata);
            return true;
        }
    }, {
        key: "getLog",
        value: function getLog() {
            return this._opLog;
        }
    }, {
        key: "lagacyConnectTo",
        value: function lagacyConnectTo(server, referrer, extra, appName) {
            this.ver = '2';
            this.platform = appName;
            this.referrer = referrer;
            this.connectTo(server, false, true, true, '2');
        }
        /**
         * @hidden
         * @return {string}
         * @private
         */

    }, {
        key: "_traceName",
        value: function _traceName() {
            return 'VoxSignaling';
        }
    }], [{
        key: "get",
        value: function get() {
            if (typeof this.inst == "undefined") {
                this.inst = new VoxSignaling();
            }
            return this.inst;
        }
    }]);

    return VoxSignaling;
}();
/**
 * Timeout for __ping and __pong method
 * @type {number}
 */


VoxSignaling.PING_DELAY = 30000;
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.SIGNALING)], VoxSignaling.prototype, "addHandler", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.SIGNALING)], VoxSignaling.prototype, "close", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.SIGNALING)], VoxSignaling.prototype, "cleanup", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.SIGNALING)], VoxSignaling.prototype, "onConnectionSuccessfulRPC", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.SIGNALING)], VoxSignaling.prototype, "onConnectionFailedRPC", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.SIGNALING)], VoxSignaling.prototype, "connectTo", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.SIGNALING)], VoxSignaling.prototype, "setRPCHandler", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.SIGNALING)], VoxSignaling.prototype, "removeRPCHandler", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.SIGNALING)], VoxSignaling.prototype, "callRemoteFunction", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.SIGNALING)], VoxSignaling.prototype, "disconnect", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.SIGNALING)], VoxSignaling.prototype, "onWSClosed", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.SIGNALING)], VoxSignaling.prototype, "onWSConnected", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.SIGNALING)], VoxSignaling.prototype, "onWSError", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.SIGNALING)], VoxSignaling.prototype, "sendRaw", null);
exports.VoxSignaling = VoxSignaling;

/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", { value: true });
/**
 * Enum for callRemoteFunction
 *
 * @author Igor Sheko
 * @hidden
 */
var RemoteFunction;
(function (RemoteFunction) {
    RemoteFunction[RemoteFunction["ping"] = "__ping"] = "ping";
    RemoteFunction[RemoteFunction["login"] = "login"] = "login";
    RemoteFunction[RemoteFunction["loginGenerateOneTimeKey"] = "loginGenerateOneTimeKey"] = "loginGenerateOneTimeKey";
    RemoteFunction[RemoteFunction["loginStage2"] = "loginStage2"] = "loginStage2";
    RemoteFunction[RemoteFunction["setOperatorACDStatus"] = "setOperatorACDStatus"] = "setOperatorACDStatus";
    RemoteFunction[RemoteFunction["setDesiredVideoBandwidth"] = "setDesiredVideoBandwidth"] = "setDesiredVideoBandwidth";
    RemoteFunction[RemoteFunction["rejectCall"] = "rejectCall"] = "rejectCall";
    RemoteFunction[RemoteFunction["disconnectCall"] = "disconnectCall"] = "disconnectCall";
    RemoteFunction[RemoteFunction["sendDTMF"] = "sendDTMF"] = "sendDTMF";
    RemoteFunction[RemoteFunction["sendSIPInfo"] = "sendSIPInfo"] = "sendSIPInfo";
    RemoteFunction[RemoteFunction["hold"] = "hold"] = "hold";
    RemoteFunction[RemoteFunction["unhold"] = "unhold"] = "unhold";
    RemoteFunction[RemoteFunction["acceptCall"] = "acceptCall"] = "acceptCall";
    RemoteFunction[RemoteFunction["createCall"] = "createCall"] = "createCall";
    RemoteFunction[RemoteFunction["transferCall"] = "transferCall"] = "transferCall";
    RemoteFunction[RemoteFunction["muteLocal"] = "__muteLocal"] = "muteLocal";
    RemoteFunction[RemoteFunction["reInvite"] = "ReInvite"] = "reInvite";
    RemoteFunction[RemoteFunction["acceptReInvite"] = "AcceptReInvite"] = "acceptReInvite";
    RemoteFunction[RemoteFunction["rejectReInvite"] = "RejectReInvite"] = "rejectReInvite";
    RemoteFunction[RemoteFunction["confirmPC"] = "__confirmPC"] = "confirmPC";
    RemoteFunction[RemoteFunction["addCandidate"] = "__addCandidate"] = "addCandidate";
    RemoteFunction[RemoteFunction["loginUsingOneTimeKey"] = "loginUsingOneTimeKey"] = "loginUsingOneTimeKey";
    RemoteFunction[RemoteFunction["refreshOauthToken"] = "refreshOauthToken"] = "refreshOauthToken";
    //    =========================Legacy ZAPI
    RemoteFunction[RemoteFunction["zPromptFinished"] = "promptFinished"] = "zPromptFinished";
    RemoteFunction[RemoteFunction["zStartPreFlightCheck"] = "__startPreFlightCheck"] = "zStartPreFlightCheck";
    //    =========================Legacy ZAPI
    //    =========================Push service
    RemoteFunction[RemoteFunction["registerPushToken"] = "registerPushToken"] = "registerPushToken";
    RemoteFunction[RemoteFunction["unregisterPushToken"] = "unregisterPushToken"] = "unregisterPushToken";
    RemoteFunction[RemoteFunction["pushFeedback"] = "pushFeedback"] = "pushFeedback";
    //    =========================Push service
})(RemoteFunction = exports.RemoteFunction || (exports.RemoteFunction = {}));

/***/ }),
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _get = function get(object, property, receiver) { if (object === null) object = Function.prototype; var desc = Object.getOwnPropertyDescriptor(object, property); if (desc === undefined) { var parent = Object.getPrototypeOf(object); if (parent === null) { return undefined; } else { return get(parent, property, receiver); } } else if ("value" in desc) { return desc.value; } else { var getter = desc.get; if (getter === undefined) { return undefined; } return getter.call(receiver); } };

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var __decorate = undefined && undefined.__decorate || function (decorators, target, key, desc) {
    var c = arguments.length,
        r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc,
        d;
    if ((typeof Reflect === "undefined" ? "undefined" : _typeof(Reflect)) === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);else for (var i = decorators.length - 1; i >= 0; i--) {
        if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    }return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var Events_1 = __webpack_require__(18);
var Utils_1 = __webpack_require__(21);
var VoxSignaling_1 = __webpack_require__(1);
var UserMediaManager_1 = __webpack_require__(4);
var Authenticator_1 = __webpack_require__(10);
var BrowserSpecific_1 = __webpack_require__(5);
var Logger_1 = __webpack_require__(0);
var PCFactory_1 = __webpack_require__(8);
var CallManager_1 = __webpack_require__(6);
var Renderer_1 = __webpack_require__(20);
var EventDispatcher_1 = __webpack_require__(15);
var RemoteFunction_1 = __webpack_require__(2);
var RemoteEvent_1 = __webpack_require__(12);
var CallstatsIo_1 = __webpack_require__(16);
var MediaCache_1 = __webpack_require__(22);
var ZingayaAPI_1 = __webpack_require__(44);
var PushService_1 = __webpack_require__(45);
var GUID_1 = __webpack_require__(27);
var Hardware_1 = __webpack_require__(13);
/**
 * Client class used to control platform functions. Can't be instantiated directly (singleton), please use <a href="../globals.html#getinstance">VoxImplant.getInstance</a> to get the class instance.
 */

var Client = function (_EventDispatcher_1$Ev) {
    _inherits(Client, _EventDispatcher_1$Ev);

    function Client() {
        _classCallCheck(this, Client);

        /**
         * WS connected flag
         * @type {boolean}
         * @private
         * @hidden
         */
        var _this = _possibleConstructorReturn(this, (Client.__proto__ || Object.getPrototypeOf(Client)).call(this));

        _this._connected = false;
        /**
         * Template for progress tone
         * @type {{US: string, RU: string}}
         * @hidden
         */
        _this.progressToneScript = {
            US: "440@-19,480@-19;*(2/4/1+2)",
            RU: "425@-19;*(1/3/1)"
        };
        /**
         * Flag of now playing progress tone
         * @type {boolean}
         * @hidden
         */
        _this.playingNow = false;
        /**
         * List of available servers, returned by balancer
         * @type {Array}
         * @hidden
         */
        _this.serversList = [];
        /**
         * Global voluem level
         * @type {number}
         * @private
         * @hidden
         */
        _this._vol = 100;
        /**
         * Require microphone on getUserMedia
         * @type {boolean}
         * @hidden
         */
        _this.micRequired = false;
        /**
         * Video settings to getUserMedia
         * @type {null}
         * @hidden
         */
        _this.videoConstraints = null;
        /**
         * Country for progress tone
         * now supported only "US" and "RU"
         * @type {string}
         * @hidden
         */
        _this.progressToneCountry = "US";
        /**
         * Play progress tone on outgoing call
         * @type {boolean}
         * @hidden
         */
        _this.progressTone = true;
        /**
         * If true - set log level to TRACE
         * @type {boolean}
         * @hidden
         */
        _this.showDebugInfo = false;
        /**
         * If true - set log level to WARNING
         * @type {boolean}
         * @hidden
         */
        _this.showWarnings = false;
        /**
         * Is xRTC supported by this browser
         * @type {boolean}
         * @hidden
         */
        _this.RTCsupported = false;
        /**
         * @hidden
         * @type {boolean}
         * @private
         */
        _this._deviceEnumAPI = false;
        /**
         * @hidden
         */
        _this._h264first = false;
        /**
         * @hidden
         */
        _this._VP8first = false;
        _this.applyMixins(Client, [EventDispatcher_1.EventDispatcher]);
        if (Client.instance) {
            throw new Error("Error - use VoxImplant.getInstance()");
        }
        Client.instance = _this;
        _this._promises = {};
        BrowserSpecific_1.default.init();
        var pc = PCFactory_1.PCFactory.get();
        pc.requireMedia = false;
        _this.voxSignaling = VoxSignaling_1.VoxSignaling.get();
        _this.voxMediaManager = UserMediaManager_1.UserMediaManager.get();
        _this.voxCallManager = CallManager_1.CallManager.get();
        _this.mediacache = MediaCache_1.MediaCache.get();
        _this.renderer = Renderer_1.Renderer.get();
        _this.setLogLevelAll(Logger_1.LogLevel.NONE);
        Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.CLIENT, 'SDK ver.', Logger_1.LogLevel.TRACE, _this.version);
        VoxSignaling_1.VoxSignaling.get().setRPCHandler(RemoteEvent_1.RemoteEvent.onPCStats, function (id, stats) {
            if (PCFactory_1.PCFactory.get().getPeerConnect(id)) _this.dispatchEvent({
                name: "NetStatsReceived",
                stats: stats
            });
        });
        _this._defaultSinkId = null;
        _this.loginState = 0;
        return _this;
    }
    /**
     * Helper for apply mixins
     * @hidden
     * @param derivedCtor
     * @param baseCtors
     */


    _createClass(Client, [{
        key: "applyMixins",
        value: function applyMixins(derivedCtor, baseCtors) {
            baseCtors.forEach(function (baseCtor) {
                Object.getOwnPropertyNames(baseCtor.prototype).forEach(function (name) {
                    derivedCtor.prototype[name] = baseCtor.prototype[name];
                });
            });
        }
        /**
         @hidden
         */

    }, {
        key: "deviceEnumAPI",
        value: function deviceEnumAPI() {
            var _this2 = this;

            this.voxMediaManager.getDevices().then(function () {
                _this2._deviceEnumAPI = true;
                _this2.dispatchEvent({ name: Events_1.Events.SourcesInfoUpdated });
            }).catch(function (err) {
                this._deviceEnumAPI = false;
            });
        }
        /**
         * Plays progress tone according to specified country in config.progressToneCountry
         * @hidden
         */

    }, {
        key: "playProgressTone",
        value: function playProgressTone() {
            var check = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

            if (!check || check && this.progressTone) {
                if (this.progressToneScript[this.progressToneCountry] !== null) {
                    if (!this.playingNow) this.playToneScript(this.progressToneScript[this.progressToneCountry]);
                    this.playingNow = true;
                }
            }
        }
        /**
         * Stop progress tone
         * @hidden
         */

    }, {
        key: "stopProgressTone",
        value: function stopProgressTone() {
            if (this.playingNow) {
                this.stopPlayback();
                this.playingNow = false;
            }
        }
        /**
         * @hidden
         */

    }, {
        key: "onIncomingCall",
        value: function onIncomingCall(id, callerid, displayName, headers, hasVideo) {
            this.dispatchEvent({
                name: Events_1.Events.IncomingCall,
                call: CallManager_1.CallManager.get().calls[id],
                headers: headers,
                video: hasVideo
            });
        }
        /**
         * @hidden
         */

    }, {
        key: "checkConnection",
        value: function checkConnection() {
            if (!this._connected) throw new Error("NOT_CONNECTED_TO_VOXIMPLANT");
        }
        /**
         * Initialize SDK. SDKReady event will be dispatched after successful SDK initialization. SDK can't be used until it's initialized
         * @param {VoxImplant.Config} [config] Client configuration options
         */

    }, {
        key: "init",
        value: function init(config) {
            var _this3 = this;

            return new Promise(function (resolve, reject) {
                //if (this.config !== null) throw ("VoxImplant.Client has been already initialized");
                _this3._config = typeof config !== 'undefined' ? config : {};
                if (_this3._config.progressToneCountry !== undefined) _this3.progressToneCountry = _this3._config.progressToneCountry;
                if (_this3._config.progressTone !== true) _this3.progressTone = false;
                if (_this3._config.serverIp !== undefined) _this3.serverIp = _this3._config.serverIp;
                if (_this3._config.showDebugInfo !== undefined) _this3.showDebugInfo = _this3._config.showDebugInfo;
                if (_this3._config.showWarnings !== false) _this3.showWarnings = true;
                if (typeof _this3._config.videoContainerId === "string") _this3.remoteVideoContainerId = _this3._config.videoContainerId;
                if (typeof _this3._config.remoteVideoContainerId === "string") _this3.remoteVideoContainerId = _this3._config.remoteVideoContainerId;
                if (typeof _this3._config.localVideoContainerId === "string") _this3.localVideoContainerId = _this3._config.localVideoContainerId;
                if (_this3._config.micRequired !== false) _this3.micRequired = true;
                if (typeof _this3._config.videoSupport != "undefined") _this3.videoSupport = _this3._config.videoSupport;else _this3.videoSupport = false;
                if (typeof _this3._config.H264first != "undefined") {
                    _this3._h264first = _this3._config.H264first;
                    CallManager_1.CallManager.get()._h264first = _this3._h264first;
                }
                if (typeof _this3._config.VP8first != "undefined") _this3._VP8first = _this3._config.VP8first;
                if (typeof _this3._config.rtcStatsCollectionInterval != "undefined") CallManager_1.CallManager.get().rtcStatsCollectionInterval = _this3._config.rtcStatsCollectionInterval;else CallManager_1.CallManager.get().rtcStatsCollectionInterval = 10000;
                if (_this3._config.protocolVersion && (_this3._config.protocolVersion === "2" || _this3._config.protocolVersion === "3")) {
                    _this3._callProtocolVersion = _this3._config.protocolVersion;
                    CallManager_1.CallManager.get().setProtocolVersion(_this3._callProtocolVersion);
                } else _this3._callProtocolVersion = "3";
                if (_this3._config.callstatsIoParams) CallstatsIo_1.CallstatsIo.get(_this3._config.callstatsIoParams);
                if (_this3._config.prettyPrint) Logger_1.LogManager.get().setPrettyPrint(_this3._config.prettyPrint);
                if (_this3.showWarnings) _this3.setLogLevelAll(Logger_1.LogLevel.WARNING);
                if (_this3.showDebugInfo) _this3.setLogLevelAll(Logger_1.LogLevel.TRACE);
                if (_this3._config.videoConstraints !== undefined) {
                    _this3.videoConstraints = _this3._config.videoConstraints;
                    if (_this3._config.experiments && _this3._config.experiments.hardware) {
                        var videoConfig = Hardware_1.default.CameraManager.legacyParamConverter(_this3._config.videoConstraints);
                        Hardware_1.default.CameraManager.get().setDefaultCamera(videoConfig);
                    } else {
                        UserMediaManager_1.UserMediaManager.get().setConstraints(_this3.videoConstraints, false);
                    }
                }
                //Fix always asking for webcam
                UserMediaManager_1.UserMediaManager.get().enableAudio(_this3.micRequired);
                UserMediaManager_1.UserMediaManager.get().enableVideo(_this3.videoSupport);
                // Show warning about getUserMedia w/o https
                if (window.location.hostname != "127.0.0.1" && window.location.hostname != "localhost" && window.location.protocol != "https:") {
                    if (typeof console.error != "undefined" && _this3.showWarnings) Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.CLIENT, 'WARNING:', Logger_1.LogLevel.WARNING, "getUserMedia() is deprecated on insecure origins, and support will be removed in the future. You should consider switching your application to a secure origin, such as HTTPS. See https://goo.gl/rStTGz for more details.");
                }
                if (_this3._config.experiments && _this3._config.experiments.ignorewebrtc) {
                    _this3.RTCsupported = true;
                } else {
                    /* Check if WebRTC is supported */
                    if (typeof webkitRTCPeerConnection != 'undefined' || typeof mozRTCPeerConnection != 'undefined' || typeof RTCPeerConnection != 'undefined' || typeof RTCIceGatherer != "undefined") {
                        if (typeof mozRTCPeerConnection != 'undefined') {
                            try {
                                new mozRTCPeerConnection({ "iceServers": [] });
                                _this3.RTCsupported = true;
                            } catch (e) {}
                        } else _this3.RTCsupported = true;
                    }
                }
                if (_this3.RTCsupported) {
                    var ts;
                    // Show warning about WebRTC security restrictions
                    if (window.location.href.match(/^file\:\/{3}.*$/g) != null) {
                        if (typeof console.error != "undefined" && _this3.showWarnings) console.error("WebRTC requires application to be loaded from a web server");
                    }
                    // work with low-level API
                    _this3.voxAuth = Authenticator_1.Authenticator.get();
                    _this3.voxAuth.setHandler({
                        onLoginSuccessful: function onLoginSuccessful(displayName, tokens) {
                            _this3.loginState = 2;
                            var event = { name: Events_1.Events.AuthResult, displayName: displayName, result: true, tokens: tokens };
                            _this3._resolvePromise('login', event);
                            _this3.dispatchEvent(event);
                        },
                        onLoginFailed: function onLoginFailed(statusCode) {
                            _this3.loginState = 0;
                            var event = { name: Events_1.Events.AuthResult, code: statusCode, result: false };
                            _this3._rejectPromise('login', event);
                            _this3.dispatchEvent(event);
                        },
                        onSecondStageInitiated: function onSecondStageInitiated() {
                            var event = { name: Events_1.Events.AuthResult, code: 301, result: false };
                            _this3._rejectPromise('login', event);
                            _this3.dispatchEvent(event);
                        },
                        onOneTimeKeyGenerated: function onOneTimeKeyGenerated(key) {
                            var event = { name: Events_1.Events.AuthResult, key: key, code: 302, result: false };
                            _this3._resolvePromise('loginkey', event);
                            _this3.dispatchEvent(event);
                        },
                        onRefreshTokenFailed: function onRefreshTokenFailed(code) {
                            var event = { name: Events_1.Events.RefreshTokenResult, code: code, result: false };
                            _this3._resolvePromise('token_refresh', event);
                            _this3.dispatchEvent(event);
                        },
                        onRefreshTokenSuccess: function onRefreshTokenSuccess(oauth) {
                            var event = { name: Events_1.Events.RefreshTokenResult, tokens: oauth, result: true };
                            _this3._rejectPromise('token_refresh', event);
                            _this3.dispatchEvent(event);
                        }
                    });
                    _this3.voxSignaling.addHandler(_this3);
                    ts = setInterval(function () {
                        if (typeof document != 'undefined') {
                            clearInterval(ts);
                            _this3.deviceEnumAPI();
                            _this3.dispatchEvent({ name: Events_1.Events.SDKReady, version: _this3.version });
                            resolve({ name: Events_1.Events.SDKReady, version: _this3.version });
                        }
                    }, 100);
                } else {
                    reject(new Error("NO_WEBRTC_SUPPORT"));
                    throw new Error("NO_WEBRTC_SUPPORT");
                }
            });
        }
        /**
         * @hidden
         * @param {string} eventName
         * @param {Object} event
         * @private
         */

    }, {
        key: "_resolvePromise",
        value: function _resolvePromise(eventName, event) {
            var promise = this._promises[eventName];
            if (promise) {
                promise.resolve(event);
                this._promises[eventName] = undefined;
            }
        }
        /**
         * @hidden
         * @param {string} eventName
         * @param {Object} event
         * @private
         */

    }, {
        key: "_rejectPromise",
        value: function _rejectPromise(eventName, event) {
            var promise = this._promises[eventName];
            if (promise) {
                promise.reject(event);
                this._promises[eventName] = undefined;
            }
        }
        /**
         * Create call
         * @name VoxImplant.Client.call
         * @param {String} num The number to call. For SIP compatibility reasons it should be a non-empty string even if the number itself is not used by a Voximplant cloud scenario.
         * @param {Boolean} useVideo Tells if video should be supported for the call. It's false by default.
         * @param {String} customData Custom string associated with the call session. It can be later obtained from Call History using HTTP API. Maximum size is 200 bytes.
         * @param {Object} extraHeaders Optional custom parameters (SIP headers) that should be passed with call (INVITE) message. Parameter names must start with "X-" to be processed by application. IMPORTANT: Headers size limit is 200 bytes.
         * @returns {VoxImplant.Call}
         */

    }, {
        key: "call",
        value: function call(num, useVideo, customData, extraHeaders) {
            Utils_1.Utils.checkCA();
            var sets = {
                H264first: this._h264first,
                VP8first: this._VP8first
            };
            if (typeof num === "string" || typeof num === "number") {
                sets = {
                    number: num,
                    video: useVideo,
                    customData: customData,
                    extraHeaders: extraHeaders
                };
            } else {
                sets = num;
            }
            switch (_typeof(sets.video)) {
                case "boolean":
                    sets.video = { sendVideo: sets.video, receiveVideo: sets.video };
                    break;
                case "undefined":
                    sets.video = { sendVideo: false, receiveVideo: true };
                    break;
            }
            var newCall = this.voxCallManager.call(sets);
            return newCall;
        }
        /**
         * Get current config
         */

    }, {
        key: "config",
        value: function config() {
            return this._config;
        }
        /**
         * Connect to VoxImplant Cloud
         */

    }, {
        key: "connect",
        value: function connect(connectivityCheck) {
            var _this4 = this;

            return new Promise(function (resolve, reject) {
                _this4._promises["connect"] = { resolve: resolve, reject: reject };
                if (_this4.serverIp !== undefined) {
                    var host = void 0;
                    if (_typeof(_this4.serverIp) === "object") {
                        _this4.serversList = _this4.serverIp;
                        host = _this4.serversList[0];
                    } else host = _this4.serverIp;
                    _this4.connectTo(host, null, connectivityCheck);
                } else {
                    var balancerResult = function balancerResult(data) {
                        var ind = String(data).indexOf(";"),
                            host = void 0;
                        if (ind == -1) {
                            // one IP available
                            host = data;
                        } else {
                            this.serversList = data.split(";");
                            host = this.serversList[0];
                        }
                        this.connectTo(host, null, connectivityCheck);
                    };
                    Utils_1.Utils.getServers(balancerResult.bind(_this4), false, _this4);
                }
            });
        }
        /**
         * Connect to specific VoxImplant Cloud host
         * @name VoxImplant.Client.connectTo
         * @hidden
         */

    }, {
        key: "connectTo",
        value: function connectTo(host, omitMicDetection, connectivityCheck) {
            var _this5 = this;

            if (this._connected) {
                throw new Error("ALREADY_CONNECTED_TO_VOXIMPLANT");
            }
            this.host = host;
            if (!this.micRequired || omitMicDetection === true) this.voxSignaling.connectTo(host, true, true, connectivityCheck, this._callProtocolVersion); //this.zingayaAPI.connectTo(host, "platform");
            else {
                    this.voxMediaManager.queryMedia().then(function (stream) {
                        _this5.deviceEnumAPI();
                        if (_this5.micRequired) _this5.voxSignaling.connectTo(_this5.host, true, true, connectivityCheck, _this5._callProtocolVersion);
                        _this5.dispatchEvent({ name: Events_1.Events.MicAccessResult, result: true, stream: stream });
                    }).catch(function (err) {
                        _this5.dispatchEvent({ name: Events_1.Events.MicAccessResult, result: false, reason: err });
                    });
                }
        }
        /**
         * Disconnect from VoxImplant Cloud
         */

    }, {
        key: "disconnect",
        value: function disconnect() {
            this.checkConnection();
            this.voxSignaling.disconnect();
            this.voxMediaManager.stopLocalStream();
            this.voxSignaling.removeRPCHandler(RemoteEvent_1.RemoteEvent.onCallRemoteFunctionError);
            this.voxSignaling.removeRPCHandler(RemoteEvent_1.RemoteEvent.handleError);
        }
        /**
         * Set ACD status
         * @param {OperatorACDStatuses} Automatic call distributor status
         */

    }, {
        key: "setOperatorACDStatus",
        value: function setOperatorACDStatus(status) {
            var _this6 = this;

            return new Promise(function (resolve, reject) {
                Utils_1.Utils.checkCA();
                _this6.voxSignaling.callRemoteFunction(RemoteFunction_1.RemoteFunction.setOperatorACDStatus, status);
                resolve();
            });
        }
        /**
         * Login into application
         * @param {String} username Fully-qualified username that includes Voximplant user, application and account names. The format is: "username@appname.accname.voximplant.com".
         * @param {String} password
         * @param {VoxImplant.LoginOptions} [options]
         */

    }, {
        key: "login",
        value: function login(username, password, options) {
            var _this7 = this;

            this.loginState = 1;
            //Sentry.getInstance().setUserContext(username);
            return new Promise(function (resolve, reject) {
                _this7._promises["login"] = { resolve: resolve, reject: reject };
                options = typeof options !== 'undefined' ? options : {};
                options = Utils_1.Utils.extend({}, options);
                if (!_this7._connected) {
                    reject(new Error("NOT_CONNECTED_TO_VOXIMPLANT"));
                    throw new Error("NOT_CONNECTED_TO_VOXIMPLANT");
                }
                //if (this.RTCsupported) this.zingayaAPI.login(username, password, options);
                if (_this7._config.experiments && _this7._config.experiments.mediaServer) {
                    options.mediaServer = _this7._config.experiments.mediaServer;
                }
                _this7.voxAuth.basicLogin(username, password, options);
            });
        }
        /**
         * Login into application using 'code' auth method
         * <br>
         * Please, read <a href="http://voximplant.com/docs/quickstart/24/automated-login/">howto page</a>
         * @param {String} username Fully-qualified username that includes Voximplant user, application and account names. The format is: "username@appname.accname.voximplant.com".
         * @param {String} code
         * @param {VoxImplant.LoginOptions} [options]
         * @hidden
         */

    }, {
        key: "loginWithCode",
        value: function loginWithCode(username, code, options) {
            var _this8 = this;

            this.loginState = 1;
            return new Promise(function (resolve, reject) {
                _this8._promises["login"] = { resolve: resolve, reject: reject };
                options = typeof options !== 'undefined' ? options : {};
                options = Utils_1.Utils.extend({ serverPresenceControl: false }, options);
                if (!_this8._connected) {
                    reject(new Error("NOT_CONNECTED_TO_VOXIMPLANT"));
                    throw new Error("NOT_CONNECTED_TO_VOXIMPLANT");
                }
                //if (this.RTCsupported) this.zingayaAPI.loginStage2(username, code, options);
                _this8.voxAuth.loginStage2(username, code, options);
            });
        }
        /**
         * Login into application using accessToken
         * @param {String} username Fully-qualified username that includes Voximplant user, application and account names. The format is: "username@appname.accname.voximplant.com".
         * @param {String} token
         * @param {VoxImplant.LoginOptions} [options]
         */

    }, {
        key: "loginWithToken",
        value: function loginWithToken(username, token, options) {
            var _this9 = this;

            this.loginState = 1;
            return new Promise(function (resolve, reject) {
                _this9._promises["login"] = { resolve: resolve, reject: reject };
                options = typeof options !== 'undefined' ? options : {};
                options = Utils_1.Utils.extend({ serverPresenceControl: false }, options);
                options.accessToken = token;
                if (!_this9._connected) {
                    reject(new Error("NOT_CONNECTED_TO_VOXIMPLANT"));
                    throw new Error("NOT_CONNECTED_TO_VOXIMPLANT");
                }
                //if (this.RTCsupported) this.zingayaAPI.loginStage2(username, code, options);
                _this9.voxAuth.tokenLogin(username, options);
            });
        }
        /**
         * Refresh expired access token
         * @param {String} username Fully-qualified username that includes Voximplant user, application and account names. The format is: "username@appname.accname.voximplant.com".
         * @param {String} refreshToken
         * @param {String} deviceToken A unique token for the current device
         */

    }, {
        key: "tokenRefresh",
        value: function tokenRefresh(username, refreshToken, deviceToken) {
            var _this10 = this;

            return new Promise(function (resolve, reject) {
                _this10._promises["token_refresh"] = { resolve: resolve, reject: reject };
                _this10.voxAuth.tokenRefresh(username, refreshToken, deviceToken);
            });
        }
        /**
         * Request a key for 'onetimekey' auth method.
         * Server will send the key in AuthResult event with code 302
         * <br/>
         * Please, read <a href="http://voximplant.com/docs/quickstart/24/automated-login/">howto page</a>
         * @param {String} username
         */

    }, {
        key: "requestOneTimeLoginKey",
        value: function requestOneTimeLoginKey(username) {
            var _this11 = this;

            return new Promise(function (resolve, reject) {
                _this11._promises["loginkey"] = { resolve: resolve, reject: reject };
                if (!_this11._connected) {
                    reject(new Error("NOT_CONNECTED_TO_VOXIMPLANT"));
                    throw new Error("NOT_CONNECTED_TO_VOXIMPLANT");
                }
                //if (this.RTCsupported) this.zingayaAPI.loginGenerateOneTimeKey(username);
                _this11.voxAuth.generateOneTimeKey(username);
            });
        }
        /**
         * Login into application using 'onetimekey' auth method.
         * Hash should be calculated with the key received in AuthResult event
         * <br/>
         * Please, read <a href="http://voximplant.com/docs/quickstart/24/automated-login/">howto page</a>
         * @param {String} username
         * @param {String} hash
         * @param {VoxImplant.LoginOptions} [options]
         */

    }, {
        key: "loginWithOneTimeKey",
        value: function loginWithOneTimeKey(username, hash, options) {
            var _this12 = this;

            this.loginState = 1;
            return new Promise(function (resolve, reject) {
                _this12._promises["login"] = { resolve: resolve, reject: reject };
                options = typeof options !== 'undefined' ? options : {};
                options = Utils_1.Utils.extend({ serverPresenceControl: false }, options);
                if (!_this12._connected) {
                    reject(new Error("NOT_COFNNECTED_TO_VOXIMPLANT"));
                    throw new Error("NOT_CONNECTED_TO_VOXIMPLANT");
                }
                //if (this.RTCsupported) this.zingayaAPI.loginUsingOneTimeKey(username, hash, options);
                _this12.voxAuth.loginUsingOneTimeKey(username, hash, options);
            });
        }
        /**
         * Check if connected to VoxImplant Cloud
         * @deprecated
         * See [[Client.getClientState]]
         */

    }, {
        key: "connected",
        value: function connected() {
            return this._connected;
        }
        /**
         * Show/hide local video
         * @param {Boolean} [flag=true] Show/hide - true/false
         * @param {Boolean} [mirror=false] Mirror local video
         * @param {Boolean} [detachCamera=false] Detach camera on hide local video
         */

    }, {
        key: "showLocalVideo",
        value: function showLocalVideo() {
            var flag = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
            var mirror = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
            var detachCamera = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

            if (this._config.experiments && this._config.experiments.hardware) {
                var containerToUse = document.getElementById(Client.getInstance().localVideoContainerId) || document.body;
                var videoContainer = document.getElementById('voximplantlocalvideo');
                if (flag) {
                    if (videoContainer === null) {
                        videoContainer = document.createElement('video');
                        videoContainer.id = 'voximplantlocalvideo';
                        videoContainer.autoplay = true;
                        videoContainer.setAttribute('playsinline', null);
                        videoContainer.setAttribute('muted', null);
                        if (containerToUse.firstChild) containerToUse.insertBefore(videoContainer, containerToUse.firstChild);else containerToUse.appendChild(videoContainer);
                    } else {
                        videoContainer.style.display = "block";
                    }
                    Hardware_1.default.StreamManager.get().getMirrorStream().then(function (stream) {
                        BrowserSpecific_1.default.attachMedia(stream, videoContainer);
                    });
                } else {
                    if (typeof videoContainer !== "undefined" && videoContainer !== null) {
                        videoContainer.style.display = "none";
                    }
                    if (detachCamera) Hardware_1.default.StreamManager.get().remMirrorStream();
                }
                //fix for local mirrored video
                if (mirror && videoContainer) videoContainer.style.cssText += "transform: rotateY(180deg);" + "-webkit-transform:rotateY(180deg);" + "-moz-transform:rotateY(180deg);";
            } else {
                this.voxMediaManager.showLocalVideo(flag, mirror, detachCamera);
            }
        }
        /**
         * Set local video position
         * @param {Number} x Horizontal position (px)
         * @param {Number} y Vertical position (px)
         * @function
         * @hidden
         * @name VoxImplant.Client.setLocalVideoPosition
         */

    }, {
        key: "setLocalVideoPosition",
        value: function setLocalVideoPosition(x, y) {
            throw new Error("Deprecated: please use CSS to position '#voximplantlocalvideo' element");
        }
        /**
         * Set local video size
         * @param {Number} width Width in pixels
         * @param {Number} height Height in pixels
         * @function
         * @hidden
         * @name VoxImplant.Client.setLocalVideoSize
         */

    }, {
        key: "setLocalVideoSize",
        value: function setLocalVideoSize(width, height) {
            throw new Error("Deprecated: please use CSS to set size of '#voximplantlocalvideo' element");
        }
        /**
         * Set video settings globally. This settings will be used for the next call.
         * @param {VoxImplant.VideoSettings|VoxImplant.FlashVideoSettings} settings Video settings
         * @param {Function} [successCallback] Success callback function has MediaStream object as its argument
         * @param {Function} [failedCallback] Failed callback function
         */

    }, {
        key: "setVideoSettings",
        value: function setVideoSettings(settings, successCallback, failedCallback) {
            var _this13 = this;

            UserMediaManager_1.UserMediaManager.get().setConstraints(settings, true).then(function (stream) {
                if (document.getElementById("voximplantlocalvideo") !== null) BrowserSpecific_1.default.attachMedia(_this13.voxMediaManager.currentStream, document.getElementById("voximplantlocalvideo"));
                if (typeof successCallback == "function") successCallback(stream);
            }).catch(function (err) {
                if (typeof failedCallback == "function") failedCallback(err);
            });
        }
        /**
         * Set bandwidth limit for video calls. Currently supported by Chrome/Chromium. (WebRTC mode only). The limit will be applied for the next call.
         * @param {Number} bandwidth Bandwidth limit in kilobits per second (kbps)
         */

    }, {
        key: "setVideoBandwidth",
        value: function setVideoBandwidth(bandwidth) {
            this.checkConnection();
            PCFactory_1.PCFactory.get().setBandwidthParams(bandwidth);
            this.voxSignaling.callRemoteFunction(RemoteFunction_1.RemoteFunction.setDesiredVideoBandwidth, bandwidth);
        }
        /**
         * Play ToneScript using WebAudio API
         * @param {String} script Tonescript string
         * @param {Boolean} [loop=false] Loop playback if true
         */

    }, {
        key: "playToneScript",
        value: function playToneScript(script) {
            var loop = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

            Utils_1.Utils.playToneScript(script, loop);
        }
        /**
         * Stop playing ToneScript using WebAudio API
         */

    }, {
        key: "stopPlayback",
        value: function stopPlayback() {
            if (Utils_1.Utils.stopPlayback()) this.dispatchEvent({ name: Events_1.Events.PlaybackFinished });
        }
        /**
         * Get current global sound volume
         * @function
         * @hidden
         * @returns {Number}
         */
        /**
         * Change current global sound volume
         * @param {Number} vol New sound volume value between 0 and 100
         * @function
         * @hidden
         */

    }, {
        key: "volume",
        value: function volume(vol) {
            if (vol === undefined) {
                return this._vol;
            } else {
                if (vol > 100) vol = 100;
                if (vol < 0) vol = 0;
                Renderer_1.Renderer.get().setPlaybackVolume(vol / 100);
                this._vol = vol;
            }
        }
        /**
         * Get a list of all currently available audio sources / microphones
         */

    }, {
        key: "audioSources",
        value: function audioSources() {
            //if (!this._deviceEnumAPI) throw new Error("NOT_SUPPORTED: enumerateDevices");
            return this.voxMediaManager.audioSourcesList;
        }
        /**
         * Get a list of all currently available video sources / cameras
         */

    }, {
        key: "videoSources",
        value: function videoSources() {
            //if (!this._deviceEnumAPI) throw new Error("NOT_SUPPORTED: enumerateDevices");
            return this.voxMediaManager.videoSourcesList;
        }
        /**
         * Get a list of all currently available audio playback devices
         */

    }, {
        key: "audioOutputs",
        value: function audioOutputs() {
            //if (!this._deviceEnumAPI) throw new Error("NOT_SUPPORTED: enumerateDevices");
            return this.voxMediaManager.audioOutputsList;
        }
        /**
         * Use specified audio source, use <a href="#audiosources">audioSources</a> to get the list of available audio sources
         * If SDK was init with micRequired: false, force attach microphone.
         * @param {String} id Id of the audio source
         * @param {Function} [successCallback] Called in WebRTC mode if audio source changed successfully
         * @param {Function} [failedCallback] Called in WebRTC mode if audio source couldn't changed successfully
         */

    }, {
        key: "useAudioSource",
        value: function useAudioSource(id, successCallback, failedCallback) {
            var _this14 = this;

            //this.zingayaAPI.useAudioSource(id, successCallback, failedCallback);
            return new Promise(function (resolve, reject) {
                _this14.voxMediaManager.useAudioInputDevice(id);
                _this14.voxMediaManager.enableAudio(true);
                _this14.voxMediaManager.queryMedia().then(function (stream) {
                    if (typeof successCallback == "function") successCallback(stream);
                    resolve(stream);
                    UserMediaManager_1.UserMediaManager.get().updateLocalVideo(stream);
                }).catch(function (err) {
                    if (typeof failedCallback == "function") failedCallback(err);
                    reject(err);
                });
            });
        }
        /**
         * Use specified video source, use <a href="#videosources">videoSources</a> to get the list of available video sources
         * @param {String} id Id of the video source
         * @param {Function} [successCallback] Called if video source changed successfully, has MediaStream object as its argument
         * @param {Function} [failedCallback] Called if video source couldn't be changed successfully, has MediaStreamError object as its argument
         */

    }, {
        key: "useVideoSource",
        value: function useVideoSource(id, successCallback, failedCallback) {
            var _this15 = this;

            return new Promise(function (resolve, reject) {
                _this15.voxMediaManager.useVideoDevice(id);
                if (UserMediaManager_1.UserMediaManager.get().isVideoEnabled()) {
                    _this15.voxMediaManager.queryMedia().then(function (stream) {
                        if (typeof successCallback == "function") successCallback(stream);
                        UserMediaManager_1.UserMediaManager.get().updateLocalVideo(stream);
                        resolve(stream);
                    }).catch(function (err) {
                        if (typeof failedCallback == "function") failedCallback(err);
                        reject(err);
                    });
                } else {
                    UserMediaManager_1.UserMediaManager.get().updateLocalVideo();
                }
            });
        }
        /**
         * Use specified audio output for new calls, use <a href="#audiooutputs">audioOutputs</a> to get the list of available audio output
         * @param {String} id Id of the audio source
         */

    }, {
        key: "useAudioOutput",
        value: function useAudioOutput(id) {
            var _this16 = this;

            return new Promise(function (resolve, reject) {
                if (BrowserSpecific_1.default.getWSVendor(true) !== "chrome") reject(new Error("Unsupported browser. Only Google Chrome 49 and above."));
                _this16._defaultSinkId = id;
                resolve();
            });
        }
        /**
         * Enable microphone/camera if micRequired in <a href="../interfaces/config.html">VoxImplant.Config</a> was set to false
         * @param {Function} [successCallback] Called if selected recording devices were attached successfully, has MediaStream object as its argument
         * @param {Function} [failedCallback] Called if selected recording devices couldn't be attached, has MediaStreamError object as its argument
         */

    }, {
        key: "attachRecordingDevice",
        value: function attachRecordingDevice(successCallback, failedCallback) {
            var _this17 = this;

            this.voxMediaManager.enableAudio(true);
            if (this._config.videoSupport) this.voxMediaManager.enableVideo(true);
            return new Promise(function (resolve, reject) {
                return _this17.voxMediaManager.queryMedia().then(function (stream) {
                    if (typeof successCallback == "function") successCallback(stream);
                    _this17.dispatchEvent({ name: Events_1.Events.MicAccessResult, result: true, stream: stream });
                }).catch(function (err) {
                    if (typeof failedCallback == "function") failedCallback(err);
                    _this17.dispatchEvent({ name: Events_1.Events.MicAccessResult, result: false, reason: err });
                });
            });
        }
        /**
         * Disable microphone/camera if micRequired in <a href="../interfaces/config.html">VoxImplant.Config</a> was set to false
         */

    }, {
        key: "detachRecordingDevice",
        value: function detachRecordingDevice() {
            UserMediaManager_1.UserMediaManager.get().stopLocalStream();
        }
        /**
         * Set active call
         * @param {VoxImplant.Call} call VoxImplant call instance
         * @param {Boolean} [active=true] If true make call active, otherwise make call inactive
         */

    }, {
        key: "setCallActive",
        value: function setCallActive(call) {
            var active = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

            return new Promise(function (resolve, reject) {
                Utils_1.Utils.checkCA();
                if (call) return call.setActive(active);else reject("trying to hold unknown call " + call);
            });
        }
        /**
         * Start/stop sending local video to remote party/parties
         * @param {Boolean} [flag=true] Start/stop - true/false
         */

    }, {
        key: "sendVideo",
        value: function sendVideo() {
            var flag = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

            this.voxMediaManager.sendVideo(flag);
        }
        /**
         * Check if WebRTC support is available
         * @returns {Boolean}
         */

    }, {
        key: "isRTCsupported",
        value: function isRTCsupported() {
            if (typeof webkitRTCPeerConnection != 'undefined' || typeof mozRTCPeerConnection != 'undefined' || typeof RTCPeerConnection != 'undefined' || typeof RTCIceGatherer != "undefined") {
                if (typeof mozRTCPeerConnection != 'undefined') {
                    try {
                        new mozRTCPeerConnection({ "iceServers": [] });
                        return true;
                    } catch (e) {
                        return false;
                    }
                } else {
                    return true;
                }
            }
        }
        /**
         * Transfer call, depending on the result <a href="../enums/callevents.html#transfercomplete">VoxImplant.CallEvents.TransferComplete</a> or <a href="../enums/callevents.html#transferfailed">VoxImplant.CallEvents.TransferFailed</a> event will be dispatched.
         * @param {VoxImplant.Call} call1 Call which will be transferred
         * @param {VoxImplant.Call} call2 Call where call1 will be transferred
         */

    }, {
        key: "transferCall",
        value: function transferCall(call1, call2) {
            Utils_1.Utils.checkCA();
            this.voxCallManager.transferCall(call1, call2);
        }
        /**
         * @hidden
         */

    }, {
        key: "setLogLevel",

        /**
         * Set log levels for specified log categories
         * @param {LogCategory} category Log category
         * @param {LogLevel} level Log level
         * @hidden
         */
        value: function setLogLevel(category, level) {
            Logger_1.LogManager.get().setLogLevel(category, level);
        }
        /**
         * @hidden
         * @param level
         */

    }, {
        key: "setLogLevelAll",
        value: function setLogLevelAll(level) {
            this.setLogLevel(Logger_1.LogCategory.SIGNALING, level);
            this.setLogLevel(Logger_1.LogCategory.RTC, level);
            this.setLogLevel(Logger_1.LogCategory.ORTC, level);
            this.setLogLevel(Logger_1.LogCategory.USERMEDIA, level);
            this.setLogLevel(Logger_1.LogCategory.CALL, level);
            this.setLogLevel(Logger_1.LogCategory.CALLEXP2P, level);
            this.setLogLevel(Logger_1.LogCategory.CALLEXSERVER, level);
            this.setLogLevel(Logger_1.LogCategory.CALLMANAGER, level);
            this.setLogLevel(Logger_1.LogCategory.CLIENT, level);
            this.setLogLevel(Logger_1.LogCategory.AUTHENTICATOR, level);
            this.setLogLevel(Logger_1.LogCategory.PCFACTORY, level);
            this.setLogLevel(Logger_1.LogCategory.UTILS, level);
            this.setLogLevel(Logger_1.LogCategory.MESSAGING, level);
            this.setLogLevel(Logger_1.LogCategory.REINVITEQ, level);
            this.setLogLevel(Logger_1.LogCategory.HARDWARE, level);
        }
        /**
         * @hidden
         */

    }, {
        key: "onSignalingConnected",
        value: function onSignalingConnected() {
            this._connected = true;
            var event = { name: Events_1.Events.ConnectionEstablished };
            this._resolvePromise('connect', event);
            this.dispatchEvent(event);
        }
    }, {
        key: "onSignalingClosed",

        /**
         * @hidden
         */
        value: function onSignalingClosed() {
            this._connected = false;
            this.dispatchEvent({ name: Events_1.Events.ConnectionClosed });
            if (this.progressTone) this.stopProgressTone();
        }
    }, {
        key: "onSignalingConnectionFailed",

        /**
         * @hidden
         */
        value: function onSignalingConnectionFailed(reason) {
            this._connected = false;
            if (this.serversList.length > 1 && (typeof this.serverIp === "undefined" || _typeof(this.serverIp) === "object")) {
                this.serversList.splice(0, 1);
                this.connectTo(this.serversList[0], true);
            } else {
                var event = { name: Events_1.Events.ConnectionFailed, message: reason };
                this._rejectPromise('connect', event);
                this.dispatchEvent(event);
            }
        }
        /**
         * @hidden
         */

    }, {
        key: "onMediaConnectionFailed",
        value: function onMediaConnectionFailed() {}
    }, {
        key: "getCall",

        /**
         * Not documented function for backward compatibility
         * @hidden
         * @param string call_id Call ID
         * @returns {Call}
         */
        value: function getCall(call_id) {
            return CallManager_1.CallManager.get().calls[call_id];
        }
        /**
         * Not documented function for backward compatibility
         * Remove call from calls array
         * @param string call_id Call id
         * @hidden
         */

    }, {
        key: "removeCall",
        value: function removeCall(call_id) {
            CallManager_1.CallManager.get().removeCall(call_id);
        }
        /**
         * Returns promise that is resolved with a boolean flag. The boolean flag
         * is set to 'true' if screen sharing is supported.
         * Promise is rejected in case of an internal error.
        */

    }, {
        key: "screenSharingSupported",
        value: function screenSharingSupported() {
            return BrowserSpecific_1.default.screenSharingSupported();
        }
        /**
         * Register handler for specified event
         * @param event Event class (i.e. <a href="/docs/references/websdk/enums/events.html#sdkready">VoxImplant.Events.SDKReady</a>). See <a href="/docs/references/websdk/enums/events.html">VoxImplant.Events</a>
         * @param handler Handler function. A single parameter is passed - object with event information
         * @deprecated
         * @hidden
         */

    }, {
        key: "addEventListener",
        value: function addEventListener(event, handler) {
            _get(Client.prototype.__proto__ || Object.getPrototypeOf(Client.prototype), "addEventListener", this).call(this, event, handler);
        }
        /**
         * Remove handler for specified event
         * @param {Function} event Event class (i.e. <a href="docs/references/websdk/enums/events.html#sdkready">VoxImplant.Events.SDKReady</a>). See <a href="/docs/references/websdk/enums/events.html">VoxImplant.Events</a>
         * @param {Function} [handler] Handler function, if not specified all event handlers will be removed
         * @function
         * @deprecated
         * @hidden
         */

    }, {
        key: "removeEventListener",
        value: function removeEventListener(event, handler) {
            _get(Client.prototype.__proto__ || Object.getPrototypeOf(Client.prototype), "removeEventListener", this).call(this, event, handler);
        }
        /**
         * Register handler for specified event
         * @param {Function} event Event class (i.e. <a href="/docs/references/websdk/enums/events.html#sdkready">VoxImplant.Events.SDKReady</a>). See <a href="/docs/references/websdk/enums/events.html">VoxImplant.Events</a>
         * @param {Function} handler Handler function. A single parameter is passed - object with event information
         * @function
         */

    }, {
        key: "on",
        value: function on(event, handler) {
            _get(Client.prototype.__proto__ || Object.getPrototypeOf(Client.prototype), "on", this).call(this, event, handler);
        }
        /**
         * Remove handler for specified event
         * @param {Function} event Event class (i.e. <a href="/docs/references/websdk/enums/events.html#sdkready">VoxImplant.Events.SDKReady</a>). See <a href="/docs/references/websdk/enums/events.html">VoxImplant.Events</a>
         * @param {Function} [handler] Handler function, if not specified all event handlers will be removed
         * @function
         */

    }, {
        key: "off",
        value: function off(event, handler) {
            _get(Client.prototype.__proto__ || Object.getPrototypeOf(Client.prototype), "off", this).call(this, event, handler);
        }
        /**
         * @hidden
         * @param val
         */

    }, {
        key: "sslset",
        value: function sslset(val) {
            this.voxSignaling.writeLog = val;
        }
        /**
         * @hidden
         * @returns {Array<string>}
         */

    }, {
        key: "sslget",
        value: function sslget() {
            return this.voxSignaling.getLog();
        }
        /**
         * @hidden
         */

    }, {
        key: "getZingayaAPI",
        value: function getZingayaAPI() {
            return new ZingayaAPI_1.ZingayaAPI(this);
        }
        /**
         * @hidden
         */

    }, {
        key: "getMediaCache",
        value: function getMediaCache() {
            return MediaCache_1.MediaCache.get();
        }
        /**
        * Register for push notifications. Application will receive push notifications from VoxImplant Server after first log in.
        * @hidden
        * @param token FCM registration token that can be retrieved by calling firebase.messaging().getToken() inside a service worker
        * @returns {Promise<void>}
        */

    }, {
        key: "registerForPushNotificatuons",
        value: function registerForPushNotificatuons(token) {
            return PushService_1.PushService.register(token);
        }
        /**
        * Unregister from push notifications. Application will no longer receive push notifications from VoxImplant server.
        * @hidden
        * @param token FCM registration token that was used to register for push notifications
        * @returns {Promise<void>}
        */

    }, {
        key: "unregisterForPushNotificatuons",
        value: function unregisterForPushNotificatuons(token) {
            return PushService_1.PushService.unregister(token);
        }
        /**
        * Handle incoming push notification
        * @hidden
        * @param message  Incoming push notification that comes from the firebase.messaging().setBackgroundMessageHandler callback inside a service worker
        * @returns {Promise<void>}
        */

    }, {
        key: "handlePushNotification",
        value: function handlePushNotification(message) {
            return PushService_1.PushService.incomingPush(message);
        }
        /**
        * Generate a new GUID identifier. Unique each time.
        * @hidden
        */

    }, {
        key: "getGUID",
        value: function getGUID() {
            return new GUID_1.GUID().toString();
        }
        /**
        * @hidden
        * @param {boolean} flag
        */

    }, {
        key: "setSilentLogging",
        value: function setSilentLogging(flag) {
            Logger_1.LogManager.get().shadowLogging = flag;
        }
        /**
        * @hidden
        * @returns {Array<string>}
        */

    }, {
        key: "getSilentLog",
        value: function getSilentLog() {
            return Logger_1.LogManager.get().getSLog();
        }
        /**
         * Get current client state
         * @return {ClientState}
         */

    }, {
        key: "getClientState",
        value: function getClientState() {
            var signalingState = this.voxSignaling.currentState;
            if (signalingState == VoxSignaling_1.VoxSignalingState.CONNECTING || signalingState == VoxSignaling_1.VoxSignalingState.WSCONNECTED) {
                return Logger_1.ClientState.CONNECTING;
            } else if (signalingState == VoxSignaling_1.VoxSignalingState.CLOSING || signalingState == VoxSignaling_1.VoxSignalingState.IDLE) return Logger_1.ClientState.DISCONNECTED;else if (signalingState == VoxSignaling_1.VoxSignalingState.CONNECTED) {
                if (this.loginState == 1) {
                    return Logger_1.ClientState.LOGGING_IN;
                } else if (this.loginState == 2) {
                    return Logger_1.ClientState.LOGGED_IN;
                }
                return Logger_1.ClientState.CONNECTED;
            }
        }
    }, {
        key: "setSwfColor",
        value: function setSwfColor() {
            Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.CLIENT, "NOT SUPPORTED", Logger_1.LogLevel.ERROR, "setSwfColor deprecated, and not supported!");
        }
        /**
         * @hidden
         * @return {string}
         * @private
         */

    }, {
        key: "_traceName",
        value: function _traceName() {
            return 'Client';
        }
    }, {
        key: "version",

        /**
         * Return VoxImplant Web SDK version
         * @function
         * @hidden
         */
        get: function get() {
            return "4.2.11022-1513253698";
        }
    }], [{
        key: "getInstance",
        value: function getInstance() {
            if (typeof Client.instance == "undefined") Client.instance = new Client();
            return Client.instance;
        }
    }]);

    return Client;
}(EventDispatcher_1.EventDispatcher);

__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "deviceEnumAPI", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "playProgressTone", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "stopProgressTone", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "onIncomingCall", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "checkConnection", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "init", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "call", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "config", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "connect", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "connectTo", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "disconnect", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "setOperatorACDStatus", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "login", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "loginWithCode", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "loginWithToken", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "tokenRefresh", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "requestOneTimeLoginKey", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "loginWithOneTimeKey", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "connected", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "showLocalVideo", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "setLocalVideoPosition", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "setLocalVideoSize", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "setVideoSettings", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "setVideoBandwidth", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "playToneScript", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "stopPlayback", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "volume", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "audioSources", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "videoSources", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "audioOutputs", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "useAudioSource", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "useVideoSource", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "useAudioOutput", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "attachRecordingDevice", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "detachRecordingDevice", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "setCallActive", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "sendVideo", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "isRTCsupported", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "transferCall", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "setLogLevel", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "setLogLevelAll", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "onSignalingConnected", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "onSignalingClosed", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "onSignalingConnectionFailed", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "onMediaConnectionFailed", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "getCall", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "removeCall", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "addEventListener", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "removeEventListener", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "on", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client.prototype, "off", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Client, "getInstance", null);
exports.Client = Client;

/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var __decorate = undefined && undefined.__decorate || function (decorators, target, key, desc) {
    var c = arguments.length,
        r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc,
        d;
    if ((typeof Reflect === "undefined" ? "undefined" : _typeof(Reflect)) === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);else for (var i = decorators.length - 1; i >= 0; i--) {
        if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    }return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var Logger_1 = __webpack_require__(0);
var MediaCaptureConfig_1 = __webpack_require__(33);
var BrowserSpecific_1 = __webpack_require__(5);
var PCFactory_1 = __webpack_require__(8);
var Client_1 = __webpack_require__(3);
/**
 * @hidden
 */
var MediaDirection;
(function (MediaDirection) {
    MediaDirection[MediaDirection["LOCAL"] = -1] = "LOCAL";
    MediaDirection[MediaDirection["ANY"] = 0] = "ANY";
    MediaDirection[MediaDirection["REMOTE"] = 1] = "REMOTE";
})(MediaDirection = exports.MediaDirection || (exports.MediaDirection = {}));
/**
 * Singleton that manages local media streams
 * After configuration changes (enableAudio, enableVideo, ...)
 * one MUST call queryMedia() to apply new settings.
 * If user media access with new settings can't be acquired,
 * last successful configuration will be restored
 * @hidden
 */

var UserMediaManager = function () {
    function UserMediaManager() {
        _classCallCheck(this, UserMediaManager);

        this.audioSourcesList = [];
        this.videoSourcesList = [];
        this.audioOutputsList = [];
        /**
         * Video container id for local video
         * @type {string}
         */
        this.videoContainerId = 'voximplantlocalvideo';
        this.log = Logger_1.LogManager.get().createLogger(Logger_1.LogCategory.USERMEDIA, "");
        this.config = new MediaCaptureConfig_1.MediaCaptureConfig();
        this.clearConstraints();
        this.enableAudio(true);
        this.enableVideo(true);
        this.handlers = new Array();
        this.mediaAccessGranted = false;
        this._localVideos = new Array();
        if (typeof window.AudioContext != 'undefined' || typeof window.webkitAudioContext != 'undefined') {
            window.AudioContext = window.AudioContext || window.webkitAudioContext;
            try {
                this.audioContext = new AudioContext();
            } catch (e) {
                this.audioContext = null;
            }
        }
    }

    _createClass(UserMediaManager, [{
        key: "clearConstraints",
        value: function clearConstraints() {}
    }, {
        key: "addHandler",
        value: function addHandler(h) {
            this.handlers.push(h);
        }
    }, {
        key: "enableAudio",
        value: function enableAudio(doEnable) {
            this.config.audioEnabled = doEnable;
        }
    }, {
        key: "enableVideo",
        value: function enableVideo(doEnable) {
            this.config.videoEnabled = doEnable;
        }
        //Set new local stream

    }, {
        key: "setLocalStream",
        value: function setLocalStream(stream) {
            //fix ended tracks
            //Get audio and video tracks from new stream
            if (stream.getAudioTracks().length > 0) {
                this.currentAudioTrack = stream.getAudioTracks()[0];
            }
            if (stream.getVideoTracks().length > 0) {
                this.currentVideoTrack = stream.getVideoTracks()[0];
            }
            if (!this._currentStream) {
                this._currentStream = stream;
            } else {
                //Remove all tracks from old streamz
                //and copy tracks from new stream to old stream
                //This will not work in FF<44.0
                while (this._currentStream.getTracks().length > 0) {
                    this._currentStream.removeTrack(this._currentStream.getTracks()[0]);
                }if (this.currentAudioTrack) this._currentStream.addTrack(this.currentAudioTrack);
                if (this.currentVideoTrack) this._currentStream.addTrack(this.currentVideoTrack);
            }
            //refresh in active connections
            for (var i in PCFactory_1.PCFactory.get().peerConnections) {
                this.attachTo(PCFactory_1.PCFactory.get().peerConnections[i]);
            }
        }
        //Request media access with configuration

    }, {
        key: "queryMedia",
        value: function queryMedia() {
            var constraints = BrowserSpecific_1.default.composeConstraints(this.config);
            return this.getQueryMedia(constraints);
        }
        //Request media access with configuration

    }, {
        key: "queryMediaSilent",
        value: function queryMediaSilent() {
            var constraints = BrowserSpecific_1.default.composeConstraints(this.config);
            this.muteAllLocal();
            return this.getQueryMediaSilent(constraints);
        }
    }, {
        key: "getConstrainWithSendFlag",
        value: function getConstrainWithSendFlag(sendAudio, sendVideo) {
            var fixconfig = JSON.parse(JSON.stringify(this.config));
            if (sendAudio != null) fixconfig.audioEnabled = sendAudio;
            if (sendVideo != null) fixconfig.videoEnabled = sendVideo;
            return BrowserSpecific_1.default.composeConstraints(fixconfig);
        }
    }, {
        key: "getQueryMedia",
        value: function getQueryMedia(constraints) {
            var _this = this;

            return new Promise(function (resolve, reject) {
                _this.getQueryMediaSilent(constraints).then(function (stream) {
                    _this.processGrantedMedia(stream);
                    resolve(stream);
                }).catch(function (e) {
                    return reject;
                });
            });
        }
    }, {
        key: "getQueryMediaSilent",
        value: function getQueryMediaSilent(constraints) {
            var _this2 = this;

            return new Promise(function (resolve, reject) {
                BrowserSpecific_1.default.getUserMedia(constraints).then(function (stream) {
                    _this2.validConstraints = constraints;
                    _this2.log.info("Media access granted");
                    resolve(stream);
                }).catch(function (error) {
                    _this2.log.info("Media access rejected: " + error.name);
                    if (_this2.validConstraints) {
                        //Try to restore media stream using previous constraints
                        BrowserSpecific_1.default.getUserMedia(_this2.validConstraints).then(function (stream) {
                            _this2.log.info("Regained media access using previous settings");
                            resolve(stream);
                        }).catch(function (error) {
                            reject(error);
                            _this2.log.error("Failed to regain media access with previously valid constraints");
                        });
                    }
                    reject(error);
                });
            });
        }
    }, {
        key: "processGrantedMedia",
        value: function processGrantedMedia(stream) {
            var _this3 = this;

            this.stopLocalStream();
            this.setLocalStream(stream);
            this.handlers.forEach(function (h) {
                try {
                    h.onMediaAccessGranted(stream);
                } catch (e) {
                    _this3.log.error("Error in callback " + e);
                }
            });
        }
    }, {
        key: "muteAllLocal",
        value: function muteAllLocal() {
            if (this.currentAudioTrack) {
                this.currentAudioTrack.stop();
                this.currentAudioTrack = null;
            }
            if (this.currentVideoTrack) {
                this.currentVideoTrack.stop();
                this.currentVideoTrack = null;
            }
        }
        //get list of all devices (input and output) optionally filtered by device kind

    }, {
        key: "getDevices",
        value: function getDevices() {
            if (UserMediaManager.get().audioSourcesList.length !== 0) this.audioSourcesList = [];
            if (UserMediaManager.get().videoSourcesList.length !== 0) this.videoSourcesList = [];
            if (UserMediaManager.get().audioOutputsList.length !== 0) this.audioOutputsList = [];
            var q = new Promise(function (resolve, reject) {
                navigator.mediaDevices.enumerateDevices().then(function (devices) {
                    for (var i = 0; i != devices.length; ++i) {
                        var counter = [0, 0, 0];
                        var sourceInfo = devices[i];
                        if (sourceInfo.kind === 'audio' || sourceInfo.kind === 'audioinput') UserMediaManager.get().audioSourcesList.push({
                            id: sourceInfo.id || sourceInfo.deviceId,
                            name: sourceInfo.label || 'Audio recording device ' + counter[0]++
                        });else if (sourceInfo.kind === 'video' || sourceInfo.kind === 'videoinput') UserMediaManager.get().videoSourcesList.push({
                            id: sourceInfo.id || sourceInfo.deviceId,
                            name: sourceInfo.label || 'Video recording device ' + counter[1]++
                        });else if (sourceInfo.kind === 'audiooutput') UserMediaManager.get().audioOutputsList.push({
                            id: sourceInfo.id || sourceInfo.deviceId,
                            name: sourceInfo.label || 'Audio playback device ' + counter[2]++
                        });
                    }
                    resolve();
                }).catch(function (err) {
                    reject(err);
                });
            });
            return q;
        }
        //Set video device to use by id

    }, {
        key: "useVideoDevice",
        value: function useVideoDevice(id) {
            this.config.videoInputId = id;
        }
        //Set audio input device

    }, {
        key: "useAudioInputDevice",
        value: function useAudioInputDevice(id) {
            this.config.audioInputId = id;
        }
        //Get current local media stream

    }, {
        key: "attachTo",
        value: function attachTo(peerConnection) {
            //TODO: Client media test & improve
            // if (typeof(this._currentStream.clone) != "undefined") {
            //     this.log.trace("clone _currentStream in UserMediaManager.attachTo");
            //     this._legacySoundControll = false;
            //     peerConnection.bindLocalMedia(this._currentStream.clone());
            // }
            // else {
            this._legacySoundControll = true;
            peerConnection.bindLocalMedia(this._currentStream);
            // }
        }
    }, {
        key: "attachToSound",
        value: function attachToSound(peerConnection) {
            //TODO: Client media test & improve
            // if (typeof(this._currentStream.clone) != "undefined") {
            //     this.log.trace("clone _currentStream in UserMediaManager.attachTo");
            //     this._legacySoundControll = false;
            //     peerConnection.bindLocalMedia(this._currentStream.clone());
            // }
            // else {
            this._legacySoundControll = true;
            if (this._currentStream && this._currentStream.getAudioTracks().length) peerConnection.bindLocalMedia(new MediaStream(this._currentStream.getAudioTracks()));
            // }
        }
        /**
         * Marker for legacy sound controls in Chrome
         * @returns {boolean}
         */

    }, {
        key: "sendVideo",
        value: function sendVideo(state) {
            if (this.currentVideoTrack) this.currentVideoTrack.enabled = state;
        }
    }, {
        key: "isVideoEnabled",
        value: function isVideoEnabled() {
            return this.config.videoEnabled;
        }
    }, {
        key: "stopLocalStream",
        value: function stopLocalStream() {
            if (this._currentStream) {
                this.stopStream(this._currentStream);
                this._currentStream = null;
                for (var i in PCFactory_1.PCFactory.get().peerConnections) {
                    if (PCFactory_1.PCFactory.get().peerConnections.hasOwnProperty(i)) {
                        PCFactory_1.PCFactory.get().peerConnections[i].bindLocalMedia(null);
                    }
                }
            }
        }
    }, {
        key: "stopStream",
        value: function stopStream(stream) {
            stream.getTracks().forEach(function (track) {
                track.stop();
            });
        }
    }, {
        key: "setConstraints",
        value: function setConstraints(config, apply) {
            var _this4 = this;

            this.config.videoEnabled = true;
            return new Promise(function (resolve, reject) {
                _this4.config.setVideoSettings(config);
                if (apply === true) _this4.queryMedia().then(function (stream) {
                    resolve(stream);
                }).catch(function (error) {
                    reject(error);
                });
            });
        }
    }, {
        key: "showLocalVideo",

        /**
         * Show video container with local video stream, if gained
         *
         * @param flag
         * @param mirror
         * @param detachCamera
         */
        value: function showLocalVideo() {
            var flag = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

            var _this5 = this;

            var mirror = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
            var detachCamera = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

            this.currentLocalVideoSets = {
                flag: flag,
                mirror: mirror,
                detachCamera: detachCamera
            };
            if (typeof this.currentStream != "undefined") {
                var containerToUse = document.getElementById(Client_1.Client.getInstance().localVideoContainerId) || document.body;
                var videoContainer = document.getElementById(this.videoContainerId);
                if (flag) {
                    if (videoContainer === null) {
                        videoContainer = document.createElement('video');
                        videoContainer.id = this.videoContainerId;
                        videoContainer.autoplay = true;
                        videoContainer.setAttribute('playsinline', null);
                        videoContainer.setAttribute('muted', null);
                        if (containerToUse.firstChild) containerToUse.insertBefore(videoContainer, containerToUse.firstChild);else containerToUse.appendChild(videoContainer);
                    } else {
                        videoContainer.style.display = "block";
                    }
                    BrowserSpecific_1.default.getUserMedia(UserMediaManager.get().getConstrainWithSendFlag(false, true)).then(function (stream) {
                        _this5._localVideos.push(stream);
                        BrowserSpecific_1.default.attachMedia(stream, videoContainer);
                    });
                } else {
                    if (typeof videoContainer !== "undefined" && videoContainer !== null) {
                        videoContainer.style.display = "none";
                    }
                    if (detachCamera) for (var i = 0; i < this._localVideos.length; i++) {
                        this.stopStream(this._localVideos[i]);
                    }
                }
                //fix for local mirrored video
                if (mirror && videoContainer) videoContainer.style.cssText += "transform: rotateY(180deg);" + "-webkit-transform:rotateY(180deg);" + "-moz-transform:rotateY(180deg);";
            }
        }
    }, {
        key: "resetLocalVideo",
        value: function resetLocalVideo() {
            this._localVideos = [];
            this.showLocalVideo(this.currentLocalVideoSets.flag, this.currentLocalVideoSets.mirror, this.currentLocalVideoSets.detachCamera);
        }
    }, {
        key: "updateLocalVideo",
        value: function updateLocalVideo() {
            var stream = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

            var element = document.getElementById(this.videoContainerId);
            if (stream === null && element !== null && element.style.display != "none") this.showLocalVideo(true);else {
                if (element === null || stream.getVideoTracks().length == 0) {
                    return false;
                } else
                    // this._localVideos.push(stream);
                    BrowserSpecific_1.default.attachMedia(stream, element);
            }
        }
    }, {
        key: "testLocalStream",
        value: function testLocalStream(haveAudio, haveVideo) {
            var realAudio = false;
            var realVideo = false;
            var _iteratorNormalCompletion = true;
            var _didIteratorError = false;
            var _iteratorError = undefined;

            try {
                for (var _iterator = this._currentStream.getTracks()[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                    var stream = _step.value;

                    if (stream.kind === "audio") realAudio = true;
                    if (stream.kind === "video") realVideo = true;
                }
            } catch (err) {
                _didIteratorError = true;
                _iteratorError = err;
            } finally {
                try {
                    if (!_iteratorNormalCompletion && _iterator.return) {
                        _iterator.return();
                    }
                } finally {
                    if (_didIteratorError) {
                        throw _iteratorError;
                    }
                }
            }

            return haveAudio === realAudio && haveVideo === realVideo;
        }
        /**
         * @hidden
         * @return {string}
         * @private
         */

    }, {
        key: "_traceName",
        value: function _traceName() {
            return 'UserMediaManager';
        }
    }, {
        key: "currentStream",
        get: function get() {
            return this._currentStream;
        }
    }, {
        key: "legacySoundControll",
        get: function get() {
            return this._legacySoundControll;
        }
    }], [{
        key: "get",
        value: function get() {
            if (!this.inst) this.inst = new UserMediaManager();
            return this.inst;
        }
    }, {
        key: "updateMuteState",
        value: function updateMuteState(call_id, direction, newstate) {
            var pcs = PCFactory_1.PCFactory.get().peerConnections;
            for (var i in pcs) {
                if (pcs.hasOwnProperty(i)) {
                    switch (direction) {
                        case MediaDirection.ANY:
                            this.updateMuteState(call_id, MediaDirection.LOCAL, newstate);
                            this.updateMuteState(call_id, MediaDirection.REMOTE, newstate);
                            break;
                        case MediaDirection.LOCAL:
                            //VoxSignaling.get().callRemoteFunction(RemoteFunction.muteLocal, call_id, !newstate);
                            UserMediaManager.getAudioTracks(pcs[i].localStream).map(function (track) {
                                track.enabled = newstate;
                            });
                            break;
                        case MediaDirection.REMOTE:
                            pcs[i].remoteStreams.map(function (stream) {
                                UserMediaManager.getAudioTracks(stream).map(function (track) {
                                    track.enabled = newstate;
                                });
                            });
                            return;
                        default:
                            return false;
                    }
                }
            }
        }
    }, {
        key: "getAudioTracks",
        value: function getAudioTracks(stream) {
            if (stream) {
                if (stream.audioTracks) return stream.audioTracks;
                if (stream.getAudioTracks) return stream.getAudioTracks();
            }
            return null;
        }
    }, {
        key: "getVideoTracks",
        value: function getVideoTracks(stream) {
            if (stream) {
                if (stream.videoTracks) return stream.videoTracks;
                if (stream.getVideoTracks) return stream.getVideoTracks();
            }
            return null;
        }
    }]);

    return UserMediaManager;
}();

__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager.prototype, "clearConstraints", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager.prototype, "addHandler", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager.prototype, "enableAudio", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager.prototype, "enableVideo", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager.prototype, "setLocalStream", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager.prototype, "queryMedia", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager.prototype, "queryMediaSilent", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager.prototype, "processGrantedMedia", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager.prototype, "muteAllLocal", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager.prototype, "getDevices", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager.prototype, "useVideoDevice", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager.prototype, "useAudioInputDevice", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager.prototype, "attachTo", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager.prototype, "attachToSound", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager.prototype, "sendVideo", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager.prototype, "stopLocalStream", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager.prototype, "stopStream", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager.prototype, "setConstraints", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager.prototype, "showLocalVideo", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager.prototype, "updateLocalVideo", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager.prototype, "testLocalStream", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager, "updateMuteState", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager, "getAudioTracks", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], UserMediaManager, "getVideoTracks", null);
exports.UserMediaManager = UserMediaManager;

/***/ }),
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

Object.defineProperty(exports, "__esModule", { value: true });
var Logger_1 = __webpack_require__(0);
var FF_1 = __webpack_require__(31);
var Webkit_1 = __webpack_require__(39);
var WebRTCPC_1 = __webpack_require__(40);
var Edge_1 = __webpack_require__(42);
var SignalingDTMFSender_1 = __webpack_require__(17);
var Safari_1 = __webpack_require__(43);
/**
 * Browser-specific implementation of webrtc functionality
 * @hidden
 */
var BrowserSpecific;
(function (BrowserSpecific) {
    var Vendor = void 0;
    (function (Vendor) {
        Vendor[Vendor["Firefox"] = 1] = "Firefox";
        Vendor[Vendor["Webkit"] = 2] = "Webkit";
        Vendor[Vendor["Edge"] = 3] = "Edge";
        Vendor[Vendor["Safari"] = 4] = "Safari";
    })(Vendor || (Vendor = {}));
    var vendor = void 0;
    function applyIdealConstraint(constraints, name, value) {
        var r = constraints;
        if ((typeof r === "undefined" ? "undefined" : _typeof(r)) != "object") {
            r = {};
        }
        r[name] = { ideal: value };
        return r;
    }
    function peerConnectionFactory(id, mode, videoEnabled) {
        switch (vendor) {
            case Vendor.Firefox:
                return new WebRTCPC_1.WebRTCPC(id, mode, videoEnabled);
            case Vendor.Webkit:
                return new WebRTCPC_1.WebRTCPC(id, mode, videoEnabled);
            case Vendor.Safari:
                return new WebRTCPC_1.WebRTCPC(id, mode, videoEnabled);
            case Vendor.Edge:
                return new WebRTCPC_1.WebRTCPC(id, mode, videoEnabled);
            //return new ORTC(id, mode, videoEnabled);
            default:
                Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.RTC, "Core", Logger_1.LogLevel.INFO, "Unsupported browser " + navigator.userAgent);
                return null;
        }
    }
    BrowserSpecific.peerConnectionFactory = peerConnectionFactory;
    function isScreenSharingSupported() {
        return vendor === Vendor.Firefox || vendor === Vendor.Webkit;
    }
    BrowserSpecific.isScreenSharingSupported = isScreenSharingSupported;
    function defaultGetUserMedia(constraints) {
        return navigator.mediaDevices.getUserMedia(constraints);
    }
    function defaultGetDTMFSender(pc, callId) {
        return new SignalingDTMFSender_1.SignalingDTMFSender(callId);
    }
    function defaultScreenSharingSupported() {
        return new Promise(function (resolve) {
            resolve(false);
        });
    }
    //Convert user specified config to constraints object that can be recognized by browser
    function composeConstraintsDefault(config) {
        var audioConstraints = false;
        var videoConstraints = false;
        if (config.audioEnabled) {
            audioConstraints = true;
            if (config.audioInputId) audioConstraints = applyIdealConstraint(audioConstraints, "deviceId", config.audioInputId);
        }
        if (config.videoEnabled) {
            videoConstraints = true;
            if (config.videoSettings) {
                videoConstraints = config.videoSettings;
            }
            if (config.videoInputId) videoConstraints = applyIdealConstraint(videoConstraints, "deviceId", config.videoInputId);
        }
        return { peerIdentity: null, audio: audioConstraints, video: videoConstraints };
    }
    function getWSVendor() {
        var connectivityCheck = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

        if (connectivityCheck === false) {
            return "voxmobile";
        }
        if (!vendor) {
            detectVendor();
        }
        switch (vendor) {
            case Vendor.Firefox:
                return "firefox";
            case Vendor.Webkit:
                return "chrome";
            case Vendor.Safari:
                return "safari";
            case Vendor.Edge:
                return "voxmobile";
            default:
                return "";
        }
    }
    BrowserSpecific.getWSVendor = getWSVendor;
    function detectVendor() {
        if (navigator["mozGetUserMedia"]) {
            vendor = Vendor.Firefox;
        } else if (navigator["webkitGetUserMedia"]) {
            vendor = Vendor.Webkit;
        } else if (navigator.mediaDevices && navigator.userAgent.match(/Edge\/(\d+).(\d+)$/)) {
            vendor = Vendor.Edge;
        } else if (navigator["getUserMedia"]) {
            vendor = Vendor.Safari;
        }
    }
    function detectFirefoxVersion() {}
    //This function must be called before usage
    function init() {
        if (!vendor) {
            detectVendor();
        }
        if (vendor) {
            Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.RTC, "Core", Logger_1.LogLevel.INFO, "Detected browser " + Vendor[vendor]);
        }
        BrowserSpecific.getUserMedia = defaultGetUserMedia;
        BrowserSpecific.getDTMFSender = defaultGetDTMFSender;
        BrowserSpecific.screenSharingSupported = defaultScreenSharingSupported;
        switch (vendor) {
            case Vendor.Firefox:
                BrowserSpecific.attachMedia = FF_1.FF.attachStream;
                BrowserSpecific.detachMedia = FF_1.FF.detachStream;
                BrowserSpecific.getScreenMedia = FF_1.FF.getScreenMedia;
                BrowserSpecific.getRTCStats = FF_1.FF.getRTCStats;
                BrowserSpecific.getUserMedia = FF_1.FF.getUserMedia;
                BrowserSpecific.screenSharingSupported = FF_1.FF.screenSharingSupported;
                BrowserSpecific.getDTMFSender = FF_1.FF.getDTMFSender;
                break;
            case Vendor.Webkit:
                BrowserSpecific.attachMedia = Webkit_1.Webkit.attachStream;
                BrowserSpecific.detachMedia = Webkit_1.Webkit.detachStream;
                BrowserSpecific.getScreenMedia = Webkit_1.Webkit.getScreenMedia;
                BrowserSpecific.getRTCStats = Webkit_1.Webkit.getRTCStats;
                BrowserSpecific.getUserMedia = Webkit_1.Webkit.getUserMedia;
                BrowserSpecific.screenSharingSupported = Webkit_1.Webkit.screenSharingSupported;
                BrowserSpecific.getDTMFSender = Webkit_1.Webkit.getDTMFSender;
                break;
            case Vendor.Safari:
                BrowserSpecific.attachMedia = Safari_1.Safari.attachStream;
                BrowserSpecific.detachMedia = Safari_1.Safari.detachStream;
                BrowserSpecific.getScreenMedia = Safari_1.Safari.getScreenMedia;
                BrowserSpecific.getRTCStats = Safari_1.Safari.getRTCStats;
                BrowserSpecific.getUserMedia = FF_1.FF.getUserMedia;
                BrowserSpecific.getDTMFSender = Safari_1.Safari.getDTMFSender;
                break;
            case Vendor.Edge:
                BrowserSpecific.attachMedia = Edge_1.Edge.attachStream;
                BrowserSpecific.detachMedia = Edge_1.Edge.detachStream;
                BrowserSpecific.getScreenMedia = Edge_1.Edge.getScreenMedia;
                BrowserSpecific.getRTCStats = Edge_1.Edge.getRTCStats;
                break;
            default:
                Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.RTC, "Core", Logger_1.LogLevel.INFO, "Unsupported browser " + navigator.userAgent);
        }
        BrowserSpecific.composeConstraints = composeConstraintsDefault;
    }
    BrowserSpecific.init = init;
})(BrowserSpecific || (BrowserSpecific = {}));
exports.default = BrowserSpecific;

/***/ }),
/* 6 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var __decorate = undefined && undefined.__decorate || function (decorators, target, key, desc) {
    var c = arguments.length,
        r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc,
        d;
    if ((typeof Reflect === "undefined" ? "undefined" : _typeof(Reflect)) === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);else for (var i = decorators.length - 1; i >= 0; i--) {
        if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    }return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var Call_1 = __webpack_require__(14);
var CallEvents_1 = __webpack_require__(9);
var VoxSignaling_1 = __webpack_require__(1);
var Utils_1 = __webpack_require__(21);
var Authenticator_1 = __webpack_require__(10);
var Constants_1 = __webpack_require__(11);
var Logger_1 = __webpack_require__(0);
var PCFactory_1 = __webpack_require__(8);
var Client_1 = __webpack_require__(3);
var PeerConnection_1 = __webpack_require__(19);
var CallExServer_1 = __webpack_require__(35);
var CallExP2P_1 = __webpack_require__(36);
var RemoteFunction_1 = __webpack_require__(2);
var RemoteEvent_1 = __webpack_require__(12);
var CallExMedia_1 = __webpack_require__(37);
var CallstatsIo_1 = __webpack_require__(16);
var CodecSorterHelpers_1 = __webpack_require__(38);
/**
 * Implenets signaling protocol and local call management'
 * Singleton
 * All call manipulation MUST be there
 * @hidden
 */

var CallManager = function () {
    function CallManager() {
        var _this = this;

        _classCallCheck(this, CallManager);

        this.protocolVersion = "3";
        this._h264first = false;
        this._calls = {};
        this.voxSignaling = VoxSignaling_1.VoxSignaling.get();
        this.log = Logger_1.LogManager.get().createLogger(Logger_1.LogCategory.SIGNALING, "CallManager");
        this.voxSignaling.addHandler(this);
        this.voxSignaling.setRPCHandler(RemoteEvent_1.RemoteEvent.handleIncomingConnection, function (id, callerid, displayName, headers, sdp) {
            _this.handleIncomingConnection(id, callerid, displayName, headers, sdp);
        });
        this.voxSignaling.setRPCHandler(RemoteEvent_1.RemoteEvent.handleConnectionConnected, function (id, headers, sdp) {
            _this.handleConnectionConnected(id, headers, sdp);
        });
        this.voxSignaling.setRPCHandler(RemoteEvent_1.RemoteEvent.handleConnectionDisconnected, function (id, headers, params) {
            _this.handleConnectionDisconnected(id, headers, params);
        });
        this.voxSignaling.setRPCHandler(RemoteEvent_1.RemoteEvent.handleRingOut, function (id) {
            _this.handleRingOut(id);
        });
        this.voxSignaling.setRPCHandler(RemoteEvent_1.RemoteEvent.stopRinging, function (id) {
            _this.stopRinging(id);
        });
        this.voxSignaling.setRPCHandler(RemoteEvent_1.RemoteEvent.handleConnectionFailed, function (id, code, reason, headers) {
            _this.handleConnectionFailed(id, code, reason, headers);
        });
        this.voxSignaling.setRPCHandler(RemoteEvent_1.RemoteEvent.handleSIPInfo, function (callId, type, subType, body, headers) {
            _this.handleSIPInfo(callId, type, subType, body, headers);
        });
        this.voxSignaling.setRPCHandler(RemoteEvent_1.RemoteEvent.handleSipEvent, function (callId) {
            _this.handleSipEvent(callId);
        });
        this.voxSignaling.setRPCHandler(RemoteEvent_1.RemoteEvent.handleTransferStarted, function (callId) {
            _this.handleTransferStarted(callId);
        });
        this.voxSignaling.setRPCHandler(RemoteEvent_1.RemoteEvent.handleTransferComplete, function (callId) {
            _this.handleTransferComplete(callId);
        });
        this.voxSignaling.setRPCHandler(RemoteEvent_1.RemoteEvent.handleTransferFailed, function (callId) {
            _this.handleTransferFailed(callId);
        });
        this.voxSignaling.setRPCHandler(RemoteEvent_1.RemoteEvent.handleReInvite, function (callid, headers, sdp) {
            _this.handleInReinvite(callid, headers, sdp);
        });
        this.voxSignaling.setRPCHandler(RemoteEvent_1.RemoteEvent.handleAcceptReinvite, function (callid, headers, sdp) {
            _this.handleReinvite(callid, headers, sdp);
        });
        this.voxSignaling.setRPCHandler(RemoteEvent_1.RemoteEvent.handleRejectReinvite, function (callid, headers, sdp) {
            _this.handleRejectReinvite(callid, headers, sdp);
        });
        this.voxSignaling.setRPCHandler(RemoteEvent_1.RemoteEvent.startEarlyMedia, function (id, headers, sdp) {
            _this.startEarlyMedia(id, headers, sdp);
        });
    }
    /**
     * Get active call count
     * @hidden
     * @returns {number}
     */


    _createClass(CallManager, [{
        key: "call",

        /**
         * Place an outgoing call
         * @param {string} number Number to place call
         * @param {object} headers Additional headers
         * @param {boolean} video Initial state of video - enabled/disabled
         * @param {object} extraParams DEPRECATED
         */
        value: function call(sets) {
            var _this2 = this;

            var defaults = {
                number: null,
                video: { sendVideo: false, receiveVideo: false },
                customData: null,
                extraHeaders: {},
                wiredLocal: true,
                wiredRemote: true,
                H264first: this._h264first,
                VP8first: false,
                forceActive: false,
                extraParams: {}
            };
            //here will media pain
            var settings = Utils_1.Utils.mixObjectToLeft(defaults, sets);
            settings = CallManager.addCustomDataToHeaders(settings);
            var id = Utils_1.Utils.generateUUID();
            if (this._calls[id]) {
                this.log.error("Call " + id + " already exists");
                throw new Error("Internal error");
            }
            var call = this.getCallInstance(id, Authenticator_1.Authenticator.get().displayName, false, settings);
            if (CallstatsIo_1.CallstatsIo.isModuleEnabled()) {
                settings.extraHeaders[Constants_1.Constants.CALLSTATSIOID_HEADER] = id;
            }
            if (settings.VP8first) call.rearangeCodecs = CodecSorterHelpers_1.CodecSorterHelpers.VP8Sorter;
            if (settings.H264first) call.rearangeCodecs = CodecSorterHelpers_1.CodecSorterHelpers.H264Sorter;
            //
            var pcHold = false;
            call.settings.active = true;
            if (Object.keys(this._calls).length > 1 && !settings.forceActive) {
                call.setActiveForce(false);
                pcHold = true;
            }
            if (typeof settings.extraHeaders[Constants_1.Constants.DIRECT_CALL_HEADER] === "undefined" && this.protocolVersion == "2") {
                this.voxSignaling.callRemoteFunction(RemoteFunction_1.RemoteFunction.createCall, -1, settings.number, settings.video, id, null, null, settings.extraHeaders, settings.extraParams);
            } else {
                PCFactory_1.PCFactory.get().setupDirectPC(id, PeerConnection_1.PeerConnectionMode.P2P, sets.video, pcHold).then(function (sdpOffer) {
                    call.peerConnection = PCFactory_1.PCFactory.get().peerConnections[id];
                    var extra = { tracks: call.peerConnection.getTrackKind() };
                    _this2.voxSignaling.callRemoteFunction(RemoteFunction_1.RemoteFunction.createCall, -1, settings.number, true, id, null, null, settings.extraHeaders, "", sdpOffer.sdp, extra);
                });
            }
            call.sendVideo(settings.video.sendVideo);
            return call;
        }
        /**
         * snipet to process customData into CallSettings
         * @param settings
         * @returns {CallSettings}
         */

    }, {
        key: "handleIncomingConnection",

        /**
         * Handle incoming call
         * @hidden
         * @param id
         * @param callerid
         * @param displayName
         * @param headers
         * @param sdp
         */
        value: function handleIncomingConnection(id, callerid, displayName, headers, sdp) {
            var _this3 = this;

            if (this._calls[id]) {
                this.log.error("Call " + id + " already exists");
                throw new Error("Internal error");
            }
            var hasVideo = Client_1.Client.getInstance().videoSupport;
            var settings = {
                number: callerid,
                extraHeaders: headers,
                video: hasVideo,
                wiredLocal: true,
                wiredRemote: true,
                forceActive: false
            };
            var call = this.getCallInstance(id, displayName, true, settings);
            if (this._h264first) call.rearangeCodecs = CodecSorterHelpers_1.CodecSorterHelpers.H264Sorter;
            var pcHold = false;
            call.settings.active = true;
            if (Object.keys(this._calls).length > 1) {
                call.setActiveForce(false);
                pcHold = true;
            }
            if (typeof settings.extraHeaders[Constants_1.Constants.DIRECT_CALL_HEADER] === "undefined" && this.protocolVersion == "2") {
                call.peerConnection = PCFactory_1.PCFactory.get().getPeerConnect(id);
                Client_1.Client.getInstance().onIncomingCall(id, callerid, displayName, headers, this.isSDPHasVideo(sdp));
            } else {
                PCFactory_1.PCFactory.get().incomeDirectPC(id, { receiveVideo: true, sendVideo: true }, sdp, pcHold).then(function (pc) {
                    call.peerConnection = pc;
                    Client_1.Client.getInstance().onIncomingCall(id, callerid, displayName, headers, _this3.isSDPHasVideo(sdp));
                });
            }
        }
    }, {
        key: "getCallInstance",
        value: function getCallInstance(id, displayName, direction, settings) {
            var call = void 0;
            if (this.protocolVersion == "3") {
                call = new CallExMedia_1.CallExMedia(id, displayName, direction, settings);
            } else if (typeof settings.extraHeaders[Constants_1.Constants.DIRECT_CALL_HEADER] != "undefined") call = new CallExP2P_1.CallExP2P(id, displayName, direction, settings);else call = new CallExServer_1.CallExServer(id, displayName, direction, settings);
            this._calls[id] = call;
            return call;
        }
        /**
         * Check if sdp have video section with send flow
         * @param sdp
         * @returns {boolean}
         */

    }, {
        key: "isSDPHasVideo",
        value: function isSDPHasVideo(sdp) {
            var videoPos = sdp.indexOf('m=video');
            if (videoPos === -1) return false;
            var sendresvPos = sdp.indexOf('a=sendrecv', videoPos);
            var sendonlyPos = sdp.indexOf('a=sendonly', videoPos);
            var nextM = sdp.indexOf('m=', videoPos);
            if (sendresvPos !== -1 && (sendresvPos < nextM || nextM === -1) || sendonlyPos !== -1 && (sendonlyPos < nextM || nextM === -1)) return true;
            return false;
        }
    }, {
        key: "findCall",
        value: function findCall(id, functionName) {
            var c = this._calls[id];
            if (id === "") c = this._calls[Object.keys(this._calls)[0]];
            if (typeof c == "undefined") {
                this.log.warning("Received " + functionName + " for unknown call " + id);
                return null;
            }
            return c;
        }
    }, {
        key: "handleRingOut",
        value: function handleRingOut(id) {
            var c = this.findCall(id, "handleRingOut");
            if (typeof c == "undefined") return;
            Client_1.Client.getInstance().playProgressTone(true);
            c.onRingOut();
            c.canStartSendingCandidates();
        }
    }, {
        key: "handleConnectionConnected",
        value: function handleConnectionConnected(id, headers, sdp) {
            var c = this.findCall(id, "handleConnectionConnected");
            c.signalingConnected = true;
            c.canStartSendingCandidates();
            if (typeof c == "undefined") {
                return;
            }
            c.onConnected(headers, sdp);
            if (typeof sdp != "undefined" && sdp.length > 0) {
                //TODO:REMOVE THIS AFTER IVAN PATCH!!!
                var videoPos = sdp.indexOf('m=video');
                if (videoPos !== -1) {
                    var sendresvPos = sdp.indexOf('a=sendrecv', videoPos);
                    var sendonlyPos = sdp.indexOf('a=sendonly', videoPos);
                    var recvonlyPos = sdp.indexOf('a=recvonly', videoPos);
                    var inactivePos = sdp.indexOf('a=inactive', videoPos);
                    if (sendresvPos === -1 && sendonlyPos === -1 && recvonlyPos === -1 && inactivePos === -1) sdp += "a=inactive\r\n";
                }
                //ENDTODO
                c.peerConnection.processRemoteAnswer(headers, sdp);
            }
        }
    }, {
        key: "startEarlyMedia",
        value: function startEarlyMedia(id, headers, sdp) {
            var c = this.findCall(id, "startEarlyMedia");
            c.settings.hasEarlyMedia = true;
            if (typeof sdp != "undefined") {
                c.peerConnection.processRemoteAnswer(headers, sdp);
            }
            Client_1.Client.getInstance().stopProgressTone();
        }
    }, {
        key: "handleConnectionDisconnected",
        value: function handleConnectionDisconnected(id, headers, params) {
            var c = this.findCall(id, "handleConnectionDisconnected");
            if (!c) return;
            Client_1.Client.getInstance().stopProgressTone();
            c.onDisconnected(headers, params);
            delete this._calls[id];
        }
    }, {
        key: "handleSIPInfo",
        value: function handleSIPInfo(callId, type, subType, body, headers) {
            var c = this.findCall(callId, "handleSIPInfo");
            if (typeof c == "undefined") return;
            c.onInfo(c, type, subType, body, headers);
        }
    }, {
        key: "stopRinging",
        value: function stopRinging(id) {
            var c = this.findCall(id, "stopRinging");
            c.canStartSendingCandidates();
            if (typeof c == "undefined") return;
            Client_1.Client.getInstance().stopProgressTone();
            c.onStopRinging();
        }
    }, {
        key: "handleSipEvent",
        value: function handleSipEvent(id) {}
    }, {
        key: "handleTransferStarted",
        value: function handleTransferStarted(id) {}
    }, {
        key: "handleTransferComplete",
        value: function handleTransferComplete(id) {
            var c = this.findCall(id, "handleTransferComplete");
            if (typeof c == "undefined") return;
            c.onTransferComplete();
        }
    }, {
        key: "handleTransferFailed",
        value: function handleTransferFailed(id) {
            var c = this.findCall(id, "handleTransferFailed");
            if (typeof c == "undefined") return;
            c.onTransferFailed();
        }
    }, {
        key: "handleReinvite",
        value: function handleReinvite(id, headers, sdp) {
            var c = this.findCall(id, "handleReinvite");
            if (typeof c == "undefined") return;
            var hasVideo = this.isSDPHasVideo(sdp);
            c.peerConnection.handleReinvite(headers, sdp, hasVideo);
        }
    }, {
        key: "handleRejectReinvite",
        value: function handleRejectReinvite(id, headers, sdp) {
            var c = this.findCall(id, "handleReinvite");
            if (typeof c == "undefined") return;
            c.dispatchEvent({ code: 20, call: c });
        }
    }, {
        key: "handleInReinvite",
        value: function handleInReinvite(id, headers, sdp) {
            var c = this.findCall(id, "handleReinvite");
            if (typeof c == "undefined") return;
            c.runIncomingReInvite(headers, sdp);
            c.dispatchEvent({ name: CallEvents_1.CallEvents.PendingUpdate, result: true, call: c });
        }
    }, {
        key: "handleConnectionFailed",
        value: function handleConnectionFailed(id, code, reason, headers) {
            var c = this.findCall(id, "handleConnectionFailed");
            if (typeof c == "undefined") return;
            delete this._calls[id];
            Client_1.Client.getInstance().stopProgressTone();
            c.onFailed(code, reason, headers);
        }
    }, {
        key: "onSignalingConnected",
        value: function onSignalingConnected() {}
    }, {
        key: "onSignalingClosed",
        value: function onSignalingClosed() {
            for (var i in this._calls) {
                if (this._calls.hasOwnProperty(i)) {
                    this._calls[i].hangup();
                }
            }
        }
    }, {
        key: "onSignalingConnectionFailed",
        value: function onSignalingConnectionFailed(errorMessage) {}
    }, {
        key: "onMediaConnectionFailed",
        value: function onMediaConnectionFailed() {}
        // Recalculate active call count

    }, {
        key: "recalculateNumCalls",
        value: function recalculateNumCalls() {
            this._numCalls = 0;
            for (var i in this._calls) {
                if (this._calls.hasOwnProperty(i)) {
                    this._numCalls++;
                }
            }
        }
    }, {
        key: "transferCall",
        value: function transferCall(call1, call2) {
            var x = [call1, call2];
            for (var i = 0; i < x.length; i++) {
                var call = this._calls[x[i].id()];
                if (call) {
                    if (call.stateValue != Call_1.CallState.CONNECTED) {
                        this.log.error("trying to transfer call " + call.id() + " in state " + call.state());
                        return;
                    }
                } else {
                    this.log.error("trying to transfer unknown call " + call.id());
                    return;
                }
            }
            this.voxSignaling.callRemoteFunction(RemoteFunction_1.RemoteFunction.transferCall, call1.id(), call2.id());
        }
        /**
         * Fx for backward compatibility with hidden Fx Client.removeCall
         * @param call_id
         */

    }, {
        key: "removeCall",
        value: function removeCall(call_id) {
            delete this._calls[call_id];
        }
        /**
         * Remove all non X- headers
         * @param headers
         * @returns {{}}
         */

    }, {
        key: "setProtocolVersion",
        value: function setProtocolVersion(ver) {
            this.protocolVersion = ver;
        }
        /**
         * @hidden
         * @return {string}
         * @private
         */

    }, {
        key: "_traceName",
        value: function _traceName() {
            return 'CallManager';
        }
    }, {
        key: "numCalls",
        get: function get() {
            return this._numCalls;
        }
    }, {
        key: "calls",
        get: function get() {
            return this._calls;
        }
    }], [{
        key: "get",
        value: function get() {
            if (typeof this.inst == "undefined") {
                this.inst = new CallManager();
            }
            return this.inst;
        }
    }, {
        key: "addCustomDataToHeaders",
        value: function addCustomDataToHeaders(settings) {
            if (typeof settings.customData != "undefined") {
                if (typeof settings.extraHeaders == 'undefined') settings.extraHeaders = {};
                settings.extraHeaders["VI-CallData"] = settings.customData;
            }
            return settings;
        }
    }, {
        key: "cleanHeaders",
        value: function cleanHeaders(headers) {
            var res = {};
            for (var key in headers) {
                if (key.substring(0, 2) == "X-" || key == Constants_1.Constants.CALL_DATA_HEADER) {
                    res[key] = headers[key];
                }
            }
            return res;
        }
    }]);

    return CallManager;
}();

__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALLMANAGER)
// call(num: string, headers: { [id: string]: string } = {}, video: boolean = false, extraParams: { [id: string]: string } = {}): Call {
], CallManager.prototype, "call", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALLMANAGER)], CallManager.prototype, "handleIncomingConnection", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALLMANAGER)], CallManager.prototype, "findCall", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALLMANAGER)], CallManager.prototype, "handleRingOut", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALLMANAGER)], CallManager.prototype, "handleConnectionConnected", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALLMANAGER)], CallManager.prototype, "handleConnectionDisconnected", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALLEXSERVER)], CallManager.prototype, "handleSIPInfo", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALLMANAGER)], CallManager.prototype, "stopRinging", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALLMANAGER)], CallManager.prototype, "handleSipEvent", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALLMANAGER)], CallManager.prototype, "handleTransferStarted", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALLMANAGER)], CallManager.prototype, "handleTransferComplete", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALLMANAGER)], CallManager.prototype, "handleTransferFailed", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALLMANAGER)], CallManager.prototype, "handleReinvite", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALLMANAGER)], CallManager.prototype, "handleRejectReinvite", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALLMANAGER)], CallManager.prototype, "handleInReinvite", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALLMANAGER)], CallManager.prototype, "handleConnectionFailed", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALLMANAGER)], CallManager.prototype, "onSignalingConnected", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALLMANAGER)], CallManager.prototype, "onSignalingClosed", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALLMANAGER)], CallManager.prototype, "onSignalingConnectionFailed", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALLMANAGER)], CallManager.prototype, "removeCall", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALLMANAGER)], CallManager, "addCustomDataToHeaders", null);
exports.CallManager = CallManager;

/***/ }),
/* 7 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/*
 *  Copyright (c) 2016 The WebRTC project authors. All Rights Reserved.
 *
 *  Use of this source code is governed by a BSD-style license
 *  that can be found in the LICENSE file in the root of the source
 *  tree.
 */
 /* eslint-env node */


var logDisabled_ = true;
var deprecationWarnings_ = true;

// Utility methods.
var utils = {
  disableLog: function(bool) {
    if (typeof bool !== 'boolean') {
      return new Error('Argument type: ' + typeof bool +
          '. Please use a boolean.');
    }
    logDisabled_ = bool;
    return (bool) ? 'adapter.js logging disabled' :
        'adapter.js logging enabled';
  },

  /**
   * Disable or enable deprecation warnings
   * @param {!boolean} bool set to true to disable warnings.
   */
  disableWarnings: function(bool) {
    if (typeof bool !== 'boolean') {
      return new Error('Argument type: ' + typeof bool +
          '. Please use a boolean.');
    }
    deprecationWarnings_ = !bool;
    return 'adapter.js deprecation warnings ' + (bool ? 'disabled' : 'enabled');
  },

  log: function() {
    if (typeof window === 'object') {
      if (logDisabled_) {
        return;
      }
      if (typeof console !== 'undefined' && typeof console.log === 'function') {
        console.log.apply(console, arguments);
      }
    }
  },

  /**
   * Shows a deprecation warning suggesting the modern and spec-compatible API.
   */
  deprecated: function(oldMethod, newMethod) {
    if (!deprecationWarnings_) {
      return;
    }
    console.warn(oldMethod + ' is deprecated, please use ' + newMethod +
        ' instead.');
  },

  /**
   * Extract browser version out of the provided user agent string.
   *
   * @param {!string} uastring userAgent string.
   * @param {!string} expr Regular expression used as match criteria.
   * @param {!number} pos position in the version string to be returned.
   * @return {!number} browser version.
   */
  extractVersion: function(uastring, expr, pos) {
    var match = uastring.match(expr);
    return match && match.length >= pos && parseInt(match[pos], 10);
  },

  /**
   * Browser detector.
   *
   * @return {object} result containing browser and version
   *     properties.
   */
  detectBrowser: function(window) {
    var navigator = window && window.navigator;

    // Returned result object.
    var result = {};
    result.browser = null;
    result.version = null;

    // Fail early if it's not a browser
    if (typeof window === 'undefined' || !window.navigator) {
      result.browser = 'Not a browser.';
      return result;
    }

    // Firefox.
    if (navigator.mozGetUserMedia) {
      result.browser = 'firefox';
      result.version = this.extractVersion(navigator.userAgent,
          /Firefox\/(\d+)\./, 1);
    } else if (navigator.webkitGetUserMedia) {
      // Chrome, Chromium, Webview, Opera, all use the chrome shim for now
      if (window.webkitRTCPeerConnection) {
        result.browser = 'chrome';
        result.version = this.extractVersion(navigator.userAgent,
          /Chrom(e|ium)\/(\d+)\./, 2);
      } else { // Safari (in an unpublished version) or unknown webkit-based.
        if (navigator.userAgent.match(/Version\/(\d+).(\d+)/)) {
          result.browser = 'safari';
          result.version = this.extractVersion(navigator.userAgent,
            /AppleWebKit\/(\d+)\./, 1);
        } else { // unknown webkit-based browser.
          result.browser = 'Unsupported webkit-based browser ' +
              'with GUM support but no WebRTC support.';
          return result;
        }
      }
    } else if (navigator.mediaDevices &&
        navigator.userAgent.match(/Edge\/(\d+).(\d+)$/)) { // Edge.
      result.browser = 'edge';
      result.version = this.extractVersion(navigator.userAgent,
          /Edge\/(\d+).(\d+)$/, 2);
    } else if (navigator.mediaDevices &&
        navigator.userAgent.match(/AppleWebKit\/(\d+)\./)) {
        // Safari, with webkitGetUserMedia removed.
      result.browser = 'safari';
      result.version = this.extractVersion(navigator.userAgent,
          /AppleWebKit\/(\d+)\./, 1);
    } else { // Default fallthrough: not supported.
      result.browser = 'Not a supported browser.';
      return result;
    }

    return result;
  },

};

// Export.
module.exports = {
  log: utils.log,
  deprecated: utils.deprecated,
  disableLog: utils.disableLog,
  disableWarnings: utils.disableWarnings,
  extractVersion: utils.extractVersion,
  shimCreateObjectURL: utils.shimCreateObjectURL,
  detectBrowser: utils.detectBrowser.bind(utils)
};


/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var __decorate = undefined && undefined.__decorate || function (decorators, target, key, desc) {
    var c = arguments.length,
        r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc,
        d;
    if ((typeof Reflect === "undefined" ? "undefined" : _typeof(Reflect)) === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);else for (var i = decorators.length - 1; i >= 0; i--) {
        if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    }return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var PeerConnection_1 = __webpack_require__(19);
var VoxSignaling_1 = __webpack_require__(1);
var UserMediaManager_1 = __webpack_require__(4);
var Logger_1 = __webpack_require__(0);
var CallManager_1 = __webpack_require__(6);
var Call_1 = __webpack_require__(14);
var BrowserSpecific_1 = __webpack_require__(5);
var RemoteFunction_1 = __webpack_require__(2);
var RemoteEvent_1 = __webpack_require__(12);
var Client_1 = __webpack_require__(3);
var index_1 = __webpack_require__(13);
var SDPMuggle_1 = __webpack_require__(24);
/**
 * Peer connection manager
 * @hidden
 */

var PCFactory = function () {
    function PCFactory() {
        var _this = this;

        _classCallCheck(this, PCFactory);

        this.iceConfig = null;
        this._peerConnections = {};
        this.waitingPeerConnections = {};
        this.log = Logger_1.LogManager.get().createLogger(Logger_1.LogCategory.RTC, "PCFactory");
        this._requireMedia = true;
        VoxSignaling_1.VoxSignaling.get().setRPCHandler(RemoteEvent_1.RemoteEvent.createPC, function (id, sdpOffer) {
            _this.rpcHandlerCreatePC(id, sdpOffer);
        });
        VoxSignaling_1.VoxSignaling.get().setRPCHandler(RemoteEvent_1.RemoteEvent.destroyPC, function (id) {
            _this.rpcHandlerDestroyPC(id);
        });
        VoxSignaling_1.VoxSignaling.get().addHandler(this);
    }

    _createClass(PCFactory, [{
        key: "rpcHandlerCreatePC",
        value: function rpcHandlerCreatePC(id, sdpOffer) {
            sdpOffer = SDPMuggle_1.SDPMuggle.addSetupAttribute(sdpOffer);
            if (UserMediaManager_1.UserMediaManager.get().currentStream || !this._requireMedia) {
                VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.muteLocal, id, false);
                return this.createPC(id, sdpOffer, PeerConnection_1.PeerConnectionMode.CLIENT_SERVER_V1, PCFactory.sdpOffersVideo(sdpOffer));
            } else {
                //Postpone creating new PeerConnection until we have local media stream
                this.waitingPeerConnections[id] = sdpOffer;
            }
        }
    }, {
        key: "rpcHandlerDestroyPC",
        value: function rpcHandlerDestroyPC(id) {
            if (this._peerConnections[id]) {
                this._peerConnections[id].close();
                delete this._peerConnections[id];
            }
            delete this.waitingPeerConnections[id];
        }
        //Create new peer connection with remote SDP offer

    }, {
        key: "createPC",
        value: function createPC(id, sdpOffer, mode, videoEnabled) {
            var _this2 = this;

            var r = BrowserSpecific_1.default.peerConnectionFactory(id, mode, videoEnabled);
            UserMediaManager_1.UserMediaManager.get().attachTo(r);
            this._peerConnections[id] = r;
            var call = CallManager_1.CallManager.get().calls[id];
            if (sdpOffer) {
                r.processRemoteOffer(sdpOffer).then(function (localAnswer) {
                    if (typeof call === "undefined" || call.checkCallMode(Call_1.CallMode.SERVER)) VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.confirmPC, id, localAnswer);else VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.acceptCall, [id, CallManager_1.CallManager.cleanHeaders(call.headers()), localAnswer]);
                    if (id !== "__default" && typeof CallManager_1.CallManager.get().calls[id] !== "undefined") {
                        CallManager_1.CallManager.get().calls[id].peerConnection = r;
                    }
                }).catch(function (err) {
                    _this2.log.error("Error during PC creation: " + err);
                });
            }
            return r;
        }
    }, {
        key: "setupDirectPC",
        value: function setupDirectPC(id, mode, videoEnabled, pcHold) {
            var _this3 = this;

            var peerConnection = BrowserSpecific_1.default.peerConnectionFactory(id, mode, videoEnabled);
            peerConnection.setHoldKey(pcHold);
            var appConfig = Client_1.Client.getInstance().config();
            if (appConfig.experiments && appConfig.experiments.hardware) {
                var _sm = index_1.default.StreamManager.get();
                var _call = CallManager_1.CallManager.get().calls[id];
                return _sm.getCallStream(_call).then(function (stream) {
                    peerConnection.bindLocalMedia(stream);
                    _this3._peerConnections[id] = peerConnection;
                    return peerConnection.getLocalOffer();
                });
            } else {
                if (!videoEnabled.sendVideo) {
                    UserMediaManager_1.UserMediaManager.get().attachToSound(peerConnection);
                    this._peerConnections[id] = peerConnection;
                    return peerConnection.getLocalOffer();
                } else {
                    var constraints = UserMediaManager_1.UserMediaManager.get().getConstrainWithSendFlag(true, videoEnabled.sendVideo);
                    peerConnection.setVideoEnabled(videoEnabled);
                    return UserMediaManager_1.UserMediaManager.get().getQueryMediaSilent(constraints).then(function (stream) {
                        peerConnection.bindLocalMedia(stream);
                        _this3._peerConnections[id] = peerConnection;
                        return peerConnection.getLocalOffer();
                    });
                }
            }
        }
    }, {
        key: "incomeDirectPC",
        value: function incomeDirectPC(id, videoEnabled, sdp, pcHold) {
            var _this4 = this;

            var peerConnection = BrowserSpecific_1.default.peerConnectionFactory(id, PeerConnection_1.PeerConnectionMode.P2P, videoEnabled);
            peerConnection.setHoldKey(pcHold);
            return peerConnection._setRemoteDescription(sdp).then(function () {
                _this4._peerConnections[id] = peerConnection;
                return peerConnection;
            });
        }
    }, {
        key: "getPeerConnect",
        value: function getPeerConnect(id) {
            return this._peerConnections[id];
        }
    }, {
        key: "onMediaAccessRejected",
        value: function onMediaAccessRejected() {}
    }, {
        key: "onMediaAccessGranted",
        value: function onMediaAccessGranted(stream) {
            for (var i in this.waitingPeerConnections) {
                this.createPC(i, this.waitingPeerConnections[i], PeerConnection_1.PeerConnectionMode.CLIENT_SERVER_V1, PCFactory.sdpOffersVideo(this.waitingPeerConnections[i]));
            }
            this.waitingPeerConnections = {};
        }
    }, {
        key: "onSignalingConnected",
        value: function onSignalingConnected() {}
    }, {
        key: "onSignalingClosed",
        value: function onSignalingClosed() {
            this.log.info("Closing all peer connections because signaling connection has closed");
            this.waitingPeerConnections = {};
            for (var i in this._peerConnections) {
                this._peerConnections[i].close();
            }
            this._peerConnections = {};
        }
    }, {
        key: "onSignalingConnectionFailed",
        value: function onSignalingConnectionFailed(errorMessage) {}
    }, {
        key: "onMediaConnectionFailed",
        value: function onMediaConnectionFailed() {}
        //Specifies if user media access is required in current application.

    }, {
        key: "closeAll",

        /**
         * Close all current peer connections
         * @hidden
         */
        value: function closeAll() {
            for (var i in this._peerConnections) {
                this._peerConnections[i].close();
            }this._peerConnections = {};
        }
    }, {
        key: "setBandwidthParams",
        value: function setBandwidthParams(bandwidt) {
            this._bandwidthParams = bandwidt;
        }
    }, {
        key: "addBandwidthParams",
        value: function addBandwidthParams(sdp) {
            if (this._bandwidthParams) sdp.sdp = sdp.sdp.replace(/(a=mid:video.*\r\n)/g, '$1b=AS:' + this._bandwidthParams + '\r\n');
            return sdp;
        }
        /**
         * @hidden
         * @return {string}
         * @private
         */

    }, {
        key: "_traceName",
        value: function _traceName() {
            return 'PCFactory';
        }
    }, {
        key: "requireMedia",
        get: function get() {
            return this._requireMedia;
        },
        set: function set(b) {
            this._requireMedia = b;
        }
    }, {
        key: "peerConnections",
        get: function get() {
            return this._peerConnections;
        }
    }], [{
        key: "get",
        value: function get() {
            if (this.inst === null) {
                this.inst = new PCFactory();
            }
            return this.inst;
        }
        /**
         * Check if SDP contains video media
         */

    }, {
        key: "sdpOffersVideo",
        value: function sdpOffersVideo(sdpOffer) {
            return { receiveVideo: sdpOffer.indexOf("m=video") !== -1, sendVideo: true };
        }
    }]);

    return PCFactory;
}();

PCFactory.inst = null;
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.PCFACTORY)], PCFactory.prototype, "rpcHandlerCreatePC", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.PCFACTORY)], PCFactory.prototype, "rpcHandlerDestroyPC", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.PCFACTORY)], PCFactory.prototype, "createPC", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.PCFACTORY)], PCFactory.prototype, "setupDirectPC", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.PCFACTORY)], PCFactory.prototype, "incomeDirectPC", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.PCFACTORY)], PCFactory.prototype, "getPeerConnect", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.PCFACTORY)], PCFactory.prototype, "onMediaAccessRejected", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.PCFACTORY)], PCFactory.prototype, "onMediaAccessGranted", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.PCFACTORY)], PCFactory.prototype, "onSignalingConnected", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.PCFACTORY)], PCFactory.prototype, "onSignalingClosed", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.PCFACTORY)], PCFactory.prototype, "onSignalingConnectionFailed", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.PCFACTORY)], PCFactory.prototype, "onMediaConnectionFailed", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.PCFACTORY)], PCFactory.prototype, "closeAll", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.PCFACTORY)], PCFactory.prototype, "setBandwidthParams", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.PCFACTORY)], PCFactory.prototype, "addBandwidthParams", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.PCFACTORY)], PCFactory, "sdpOffersVideo", null);
exports.PCFactory = PCFactory;

/***/ }),
/* 9 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", { value: true });
/**
 * The events that are triggered by <a href="../classes/call.html">VoxImplant.Call</a> instance.
 * <br>
 * Use <a href="../classes/call.html#addeventlistener">VoxImplant.Call.addEventListener function</a> to subscribe on
 * any of these events.
 * <br>
 * Example:
 * <script src="https://gist.github.com/irbisadm/5d4ceec909be7bbcb14361ee8bba5e6d.js"></script>
 */
var CallEvents;
(function (CallEvents) {
  /**
   * Event is triggered when a realible connection is established for the call. Depending on network conditions there can be a 2-3 second delay between first audio data and this event.
   * <p>Handler function receives <a href="../interfaces/eventhandlers.calleventwithheaders.html">EventHandlers.CallEventWithHeaders</a> object as an argument.</p>
   */
  CallEvents[CallEvents["Connected"] = "Connected"] = "Connected";
  /**
   *  Event is triggered when a call was disconnected
   *  <p>Handler function receives <a href="../interfaces/eventhandlers.disconnected.html">EventHandlers.Disconnected</a> object as an argument.</p>
   */
  CallEvents[CallEvents["Disconnected"] = "Disconnected"] = "Disconnected";
  /**
   *  Event is triggered due to a call failure
   * <br>
   *  Most frequent status codes:
   * <table><thead>
   * <tr><th>Code   </th><th>Description                          </th></tr>
   * </thead>
   * <tbody>
   * <tr><td>486    </td><td>Destination number is busy           </td></tr>
   * <tr><td>487    </td><td>Request terminated                   </td></tr>
   * <tr><td>603    </td><td>Call was rejected                    </td></tr>
   * <tr><td>404    </td><td>Invalid number                       </td></tr>
   * <tr><td>480    </td><td>Destination number is unavailable    </td></tr>
   * <tr><td>402    </td><td>Insufficient funds                   </td></tr>
   * </tbody>
   * </table>
   *
   * <p>Handler function receives <a href="../interfaces/eventhandlers.failed.html">EventHandlers.Failed</a> object as an argument.</p>
   */
  CallEvents[CallEvents["Failed"] = "Failed"] = "Failed";
  /**
   *  Event is triggered when a progress tone playback starts
   *  <p>Handler function receives <a href="../interfaces/eventhandlers.callevent.html">EventHandlers.CallEvent</a> object as an argument.</p>
   */
  CallEvents[CallEvents["ProgressToneStart"] = "ProgressToneStart"] = "ProgressToneStart";
  /**
   *  Event is triggered when a progress tone playback stops
   *  <p>Handler function receives <a href="../interfaces/eventhandlers.callevent.html">EventHandlers.CallEvent</a> object as an argument.</p>
   */
  CallEvents[CallEvents["ProgressToneStop"] = "ProgressToneStop"] = "ProgressToneStop";
  /**
   *  Event is triggered when a text message is received
   *  <p>Handler function receives <a href="../interfaces/eventhandlers.messagereceived.html">EventHandlers.MessageReceived</a> object as an argument.</p>
   */
  CallEvents[CallEvents["MessageReceived"] = "onSendMessage"] = "MessageReceived";
  /**
   *  Event is triggered when INFO message is received
   *  <p>Handler function receives <a href="../interfaces/eventhandlers.inforeceived.html">EventHandlers.InfoReceived</a> object as an argument.</p>
   */
  CallEvents[CallEvents["InfoReceived"] = "InfoReceived"] = "InfoReceived";
  /**
   *  Event is triggered when a call has been transferred successfully
   *  <p>Handler function receives <a href="../interfaces/eventhandlers.callevent.html">EventHandlers.CallEvent</a> object as an argument.</p>
   */
  CallEvents[CallEvents["TransferComplete"] = "TransferComplete"] = "TransferComplete";
  /**
   *  Event is triggered when a call transfer failed
   *  <p>Handler function receives <a href="../interfaces/eventhandlers.callevent.html">EventHandlers.CallEvent</a> object as an argument.</p>
   */
  CallEvents[CallEvents["TransferFailed"] = "TransferFailed"] = "TransferFailed";
  /**
   *  Event is triggered when connection was not established due to a network connection problem between 2 peers
   *  <p>Handler function receives <a href="../interfaces/eventhandlers.callevent.html">EventHandlers.CallEvent</a> object as an argument.</p>
   */
  CallEvents[CallEvents["ICETimeout"] = "ICETimeout"] = "ICETimeout";
  CallEvents[CallEvents["RTCStatsReceived"] = "RTCStatsReceived"] = "RTCStatsReceived";
  /**
   * Event is triggered when a new HTMLMediaElement for the call's media playback has been created
   * <p>Handler function receives <a href="../interfaces/eventhandlers.mediaelementcreated.html">EventHandlers.MediaElementCreated</a> object as an argument.</p>
   */
  CallEvents[CallEvents["MediaElementCreated"] = "MediaElementCreated"] = "MediaElementCreated";
  /**
   * @hidden
   * @type {string}
   */
  CallEvents[CallEvents["MediaElementRemoved"] = "MediaElementRemoved"] = "MediaElementRemoved";
  // VideoPlaybackStarted =<any>"VideoPlaybackStarted",
  /**
   *  Event is triggered when ICE connection is complete
   *  <p>Handler function receives <a href="../interfaces/eventhandlers.callevent.html">EventHandlers.CallEvent</a> object as an argument.</p>
   */
  CallEvents[CallEvents["ICECompleted"] = "ICECompleted"] = "ICECompleted";
  /**
   * Event is triggered when a call was updated. For example, video was added/removed.
   * <p>Handler function receives <a href="../interfaces/eventhandlers.updated.html">EventHandlers.Updated</a> object as an argument.</p>
   */
  CallEvents[CallEvents["Updated"] = "Updated"] = "Updated";
  /**
   * Event is triggered when user receives the call update from another side. For example, a video was added/removed on the remote side.
   * <p>Handler function receives <a href="../interfaces/eventhandlers.callevent.html">EventHandlers.CallEvent</a> object as an argument.</p>
   * @hidden
   */
  CallEvents[CallEvents["PendingUpdate"] = "PendingUpdate"] = "PendingUpdate";
  /**
   * Event is triggered when multiple participants tried to update the same call simultaneously. For example, video added/removed on a local and remote side at the same time.
   * <p>Handler function receives <a href="../interfaces/eventhandlers.updatefailed.html">EventHandlers.UpdateFailed</a> object as an argument.</p>
   * @hidden
   */
  CallEvents[CallEvents["UpdateFailed"] = "UpdateFailed"] = "UpdateFailed";
  /**
   * <p>Handler function receives <a href="../interfaces/eventhandlers.localvideostreamadded.html">EventHandlers.LocalVideoStreamAdded</a> object as an argument.</p>
   */
  CallEvents[CallEvents["LocalVideoStreamAdded"] = "LocalVideoStreamAdded"] = "LocalVideoStreamAdded";
  /**
   * @hidden
   * @type {any}
   */
  CallEvents[CallEvents["EndPointCreated"] = "EndPointCreated"] = "EndPointCreated";
  /**
   * @hidden
   * @type {any}
   */
  CallEvents[CallEvents["EndPointRemoved"] = "EndPointRemoved"] = "EndPointRemoved";
})(CallEvents = exports.CallEvents || (exports.CallEvents = {}));

/***/ }),
/* 10 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var __decorate = undefined && undefined.__decorate || function (decorators, target, key, desc) {
    var c = arguments.length,
        r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc,
        d;
    if ((typeof Reflect === "undefined" ? "undefined" : _typeof(Reflect)) === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);else for (var i = decorators.length - 1; i >= 0; i--) {
        if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    }return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var VoxSignaling_1 = __webpack_require__(1);
var Logger_1 = __webpack_require__(0);
var PCFactory_1 = __webpack_require__(8);
var RemoteFunction_1 = __webpack_require__(2);
var RemoteEvent_1 = __webpack_require__(12);
var CallstatsIo_1 = __webpack_require__(16);
/**
 * @hidden
 */
var AuthenticatorState;
(function (AuthenticatorState) {
    AuthenticatorState[AuthenticatorState["IDLE"] = 0] = "IDLE";
    AuthenticatorState[AuthenticatorState["IN_PROGRESS"] = 1] = "IN_PROGRESS";
})(AuthenticatorState = exports.AuthenticatorState || (exports.AuthenticatorState = {}));
;
/**
 * Class that performs user login
 * Implemented as singleton
 * @hidden
 */

var Authenticator = function () {
    function Authenticator() {
        var _this = this;

        _classCallCheck(this, Authenticator);

        this.FAIL_CODE_SECOND_STAGE = 301;
        this.FAIL_CODE_ONE_TIME_KEY = 302;
        this._displayName = null;
        this._username = null;
        this._authorized = false;
        this.signaling = VoxSignaling_1.VoxSignaling.get();
        this.currentState = AuthenticatorState.IDLE;
        this.log = Logger_1.LogManager.get().createLogger(Logger_1.LogCategory.SIGNALING, "Authenticator");
        //Register handlers for Server->Client RPC
        this.signaling.setRPCHandler(RemoteEvent_1.RemoteEvent.loginFailed, function (code, extra) {
            _this.onLoginFailed(code, extra);
        });
        this.signaling.setRPCHandler(RemoteEvent_1.RemoteEvent.loginSuccessful, function (displayName, params) {
            _this.onLoginSuccesful(displayName, params);
        });
        this.signaling.setRPCHandler(RemoteEvent_1.RemoteEvent.refreshOauthTokenFailed, function (code) {
            _this.handler.onRefreshTokenFailed(code);
        });
        this.signaling.setRPCHandler(RemoteEvent_1.RemoteEvent.refreshOauthTokenSuccessful, function (oauth) {
            _this.handler.onRefreshTokenSuccess(oauth.OAuth);
        });
        this.signaling.addHandler(this);
    }

    _createClass(Authenticator, [{
        key: "setHandler",
        value: function setHandler(h) {
            this.handler = h;
        }
    }, {
        key: "onLoginFailed",
        value: function onLoginFailed(code, extra) {
            this.currentState = AuthenticatorState.IDLE;
            switch (code) {
                case this.FAIL_CODE_ONE_TIME_KEY:
                    this.handler.onOneTimeKeyGenerated(extra);
                    break;
                case this.FAIL_CODE_SECOND_STAGE:
                    this.handler.onSecondStageInitiated();
                    break;
                default:
                    this.handler.onLoginFailed(code);
                    break;
            }
        }
    }, {
        key: "onLoginSuccesful",
        value: function onLoginSuccesful(displayName, params) {
            this.currentState = AuthenticatorState.IDLE;
            this._authorized = true;
            if (params) PCFactory_1.PCFactory.get().iceConfig = params.iceConfig;
            this._displayName = displayName;
            CallstatsIo_1.CallstatsIo.get().init({ userName: this._username, aliasName: this._displayName });
            this.handler.onLoginSuccessful(displayName, params.OAuth);
        }
        /**
         * User display name. Is returned by server`
         */

    }, {
        key: "basicLogin",
        value: function basicLogin(username, password, options) {
            if (this.currentState != AuthenticatorState.IDLE) {
                this.log.error("Login operation already in progress");
                return;
            }
            this._username = username;
            this.currentState = AuthenticatorState.IN_PROGRESS;
            this.signaling.callRemoteFunction(RemoteFunction_1.RemoteFunction.login, username, password, options);
        }
    }, {
        key: "tokenLogin",
        value: function tokenLogin(username, options) {
            if (this.currentState != AuthenticatorState.IDLE) {
                this.log.error("Login operation already in progress");
                return;
            }
            this._username = username;
            this.currentState = AuthenticatorState.IN_PROGRESS;
            this.signaling.callRemoteFunction(RemoteFunction_1.RemoteFunction.login, username, '', options);
        }
    }, {
        key: "tokenRefresh",
        value: function tokenRefresh(username, refreshToken, deviceToken) {
            if (deviceToken) this.signaling.callRemoteFunction(RemoteFunction_1.RemoteFunction.refreshOauthToken, username, { refreshToken: refreshToken, deviceToken: deviceToken });else this.signaling.callRemoteFunction(RemoteFunction_1.RemoteFunction.refreshOauthToken, username, refreshToken);
        }
    }, {
        key: "loginUsingOneTimeKey",
        value: function loginUsingOneTimeKey(username, hash, options) {
            if (this.currentState != AuthenticatorState.IDLE) {
                this.log.error("Login operation already in progress");
                return;
            }
            this._username = username;
            this.currentState = AuthenticatorState.IN_PROGRESS;
            this.signaling.callRemoteFunction(RemoteFunction_1.RemoteFunction.loginUsingOneTimeKey, username, hash, options);
        }
    }, {
        key: "loginStage2",
        value: function loginStage2(username, code, options) {
            if (this.currentState != AuthenticatorState.IDLE) {
                this.log.error("Login operation already in progress");
                return;
            }
            this._username = username;
            this.currentState = AuthenticatorState.IN_PROGRESS;
            this.signaling.callRemoteFunction(RemoteFunction_1.RemoteFunction.loginStage2, username, code, options);
        }
    }, {
        key: "generateOneTimeKey",
        value: function generateOneTimeKey(username) {
            if (this.currentState != AuthenticatorState.IDLE) {
                this.log.error("Login operation already in progress");
                return;
            }
            this.currentState = AuthenticatorState.IN_PROGRESS;
            this.signaling.callRemoteFunction(RemoteFunction_1.RemoteFunction.loginGenerateOneTimeKey, username);
        }
    }, {
        key: "username",
        value: function username() {
            return this._username;
        }
    }, {
        key: "authorized",
        value: function authorized() {
            return this._authorized;
        }
    }, {
        key: "onSignalingConnected",
        value: function onSignalingConnected() {}
    }, {
        key: "onSignalingConnectionFailed",
        value: function onSignalingConnectionFailed(errorMessage) {}
    }, {
        key: "onSignalingClosed",
        value: function onSignalingClosed() {
            this._authorized = false;
            this._displayName = null;
            this._username = null;
        }
    }, {
        key: "onMediaConnectionFailed",
        value: function onMediaConnectionFailed() {}
    }, {
        key: "ziAuthorized",
        value: function ziAuthorized(state) {
            this._authorized = state;
        }
        /**
         * @hidden
         * @return {string}
         * @private
         */

    }, {
        key: "_traceName",
        value: function _traceName() {
            return 'Authenticator';
        }
    }, {
        key: "displayName",
        get: function get() {
            return this._displayName;
        }
    }], [{
        key: "get",
        value: function get() {
            if (typeof this.inst == "undefined") {
                this.inst = new Authenticator();
            }
            return this.inst;
        }
    }]);

    return Authenticator;
}();

__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.AUTHENTICATOR)], Authenticator.prototype, "setHandler", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.AUTHENTICATOR)], Authenticator.prototype, "onLoginFailed", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.AUTHENTICATOR)], Authenticator.prototype, "onLoginSuccesful", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.AUTHENTICATOR)], Authenticator.prototype, "basicLogin", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.AUTHENTICATOR)], Authenticator.prototype, "tokenLogin", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.AUTHENTICATOR)], Authenticator.prototype, "tokenRefresh", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.AUTHENTICATOR)], Authenticator.prototype, "loginUsingOneTimeKey", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.AUTHENTICATOR)], Authenticator.prototype, "loginStage2", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.AUTHENTICATOR)], Authenticator.prototype, "generateOneTimeKey", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.AUTHENTICATOR)], Authenticator.prototype, "username", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.AUTHENTICATOR)], Authenticator.prototype, "authorized", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.AUTHENTICATOR)], Authenticator.prototype, "onSignalingConnected", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.AUTHENTICATOR)], Authenticator.prototype, "onSignalingConnectionFailed", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.AUTHENTICATOR)], Authenticator.prototype, "onSignalingClosed", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.AUTHENTICATOR)], Authenticator.prototype, "onMediaConnectionFailed", null);
exports.Authenticator = Authenticator;

/***/ }),
/* 11 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

Object.defineProperty(exports, "__esModule", { value: true });
/**
 * @hidden
 */

var Constants = function Constants() {
  _classCallCheck(this, Constants);
};

Constants.DIRECT_CALL_HEADER = "X-DirectCall";
Constants.VIAMEDIA_CALL_HEADER = "X-ViaMedia";
Constants.CALLSTATSIOID_HEADER = "X-CallstatsIOID";
Constants.CALL_DATA_HEADER = "VI-CallData";
Constants.ZINGAYA_IM_MIME_TYPE = "application/zingaya-im";
Constants.P2P_SPD_FRAG_MIME_TYPE = "voximplant/sdpfrag";
Constants.VI_HOLD_EMUL = "vi/holdemul";
Constants.VI_SPD_OFFER_MIME_TYPE = "vi/sdpoffer";
Constants.VI_SPD_ANSWER_MIME_TYPE = "vi/sdpanswer";
Constants.VI_CONF_PARTICIPANT_INFO_ADDED = "vi/conf-info-added";
Constants.VI_CONF_PARTICIPANT_INFO_REMOVED = "vi/conf-info-removed";
Constants.VI_CONF_PARTICIPANT_INFO_UPDATED = "vi/conf-info-updated";
exports.Constants = Constants;

/***/ }),
/* 12 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", { value: true });
/**
 * Enum for handle remote events
 * @hidden
 */
var RemoteEvent;
(function (RemoteEvent) {
    RemoteEvent[RemoteEvent["loginFailed"] = "loginFailed"] = "loginFailed";
    RemoteEvent[RemoteEvent["loginSuccessful"] = "loginSuccessful"] = "loginSuccessful";
    RemoteEvent[RemoteEvent["handleError"] = "handleError"] = "handleError";
    RemoteEvent[RemoteEvent["onPCStats"] = "__onPCStats"] = "onPCStats";
    RemoteEvent[RemoteEvent["handleIncomingConnection"] = "handleIncomingConnection"] = "handleIncomingConnection";
    RemoteEvent[RemoteEvent["handleConnectionConnected"] = "handleConnectionConnected"] = "handleConnectionConnected";
    RemoteEvent[RemoteEvent["handleConnectionDisconnected"] = "handleConnectionDisconnected"] = "handleConnectionDisconnected";
    RemoteEvent[RemoteEvent["handleRingOut"] = "handleRingOut"] = "handleRingOut";
    RemoteEvent[RemoteEvent["startEarlyMedia"] = "startEarlyMedia"] = "startEarlyMedia";
    RemoteEvent[RemoteEvent["stopRinging"] = "stopRinging"] = "stopRinging";
    RemoteEvent[RemoteEvent["handleConnectionFailed"] = "handleConnectionFailed"] = "handleConnectionFailed";
    RemoteEvent[RemoteEvent["handleSIPInfo"] = "handleSIPInfo"] = "handleSIPInfo";
    RemoteEvent[RemoteEvent["handleSipEvent"] = "handleSipEvent"] = "handleSipEvent";
    RemoteEvent[RemoteEvent["handleTransferStarted"] = "handleTransferStarted"] = "handleTransferStarted";
    RemoteEvent[RemoteEvent["handleTransferComplete"] = "handleTransferComplete"] = "handleTransferComplete";
    RemoteEvent[RemoteEvent["handleTransferFailed"] = "handleTransferFailed"] = "handleTransferFailed";
    RemoteEvent[RemoteEvent["handleReInvite"] = "handleReInvite"] = "handleReInvite";
    RemoteEvent[RemoteEvent["handleAcceptReinvite"] = "handleAcceptReinvite"] = "handleAcceptReinvite";
    RemoteEvent[RemoteEvent["handleRejectReinvite"] = "handleRejectReinvite"] = "handleRejectReinvite";
    RemoteEvent[RemoteEvent["createPC"] = "__createPC"] = "createPC";
    RemoteEvent[RemoteEvent["destroyPC"] = "__destroyPC"] = "destroyPC";
    RemoteEvent[RemoteEvent["connectionSuccessful"] = "__connectionSuccessful"] = "connectionSuccessful";
    RemoteEvent[RemoteEvent["connectionFailed"] = "__connectionFailed"] = "connectionFailed";
    RemoteEvent[RemoteEvent["createConnection"] = "__createConnection"] = "createConnection";
    RemoteEvent[RemoteEvent["pong"] = "__pong"] = "pong";
    RemoteEvent[RemoteEvent["increaseGain"] = "increaseGain"] = "increaseGain";
    RemoteEvent[RemoteEvent["handlePreFlightCheckResult"] = "handlePreFlightCheckResult"] = "handlePreFlightCheckResult";
    RemoteEvent[RemoteEvent["handleVoicemail"] = "handleVoicemail"] = "handleVoicemail";
    RemoteEvent[RemoteEvent["onCallRemoteFunctionError"] = "onCallRemoteFunctionError"] = "onCallRemoteFunctionError";
    RemoteEvent[RemoteEvent["refreshOauthTokenFailed"] = "refreshOauthTokenFailed"] = "refreshOauthTokenFailed";
    RemoteEvent[RemoteEvent["refreshOauthTokenSuccessful"] = "refreshOauthTokenSuccessful"] = "refreshOauthTokenSuccessful";
})(RemoteEvent = exports.RemoteEvent || (exports.RemoteEvent = {}));

/***/ }),
/* 13 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var __decorate = undefined && undefined.__decorate || function (decorators, target, key, desc) {
    var c = arguments.length,
        r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc,
        d;
    if ((typeof Reflect === "undefined" ? "undefined" : _typeof(Reflect)) === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);else for (var i = decorators.length - 1; i >= 0; i--) {
        if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    }return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var Logger_1 = __webpack_require__(0);
var EventDispatcher_1 = __webpack_require__(15);
/**
 * @hidden
 */
var Hardware;
(function (Hardware) {
    var StreamEvents = void 0;
    (function (StreamEvents) {
        StreamEvents[StreamEvents["MirrorUpdated"] = "MirrorUpdated"] = "MirrorUpdated";
    })(StreamEvents = Hardware.StreamEvents || (Hardware.StreamEvents = {}));
    var VideoQuality = void 0;
    (function (VideoQuality) {
        VideoQuality[VideoQuality["VIDEO_QUALITY_HIGH"] = "video_quality_high"] = "VIDEO_QUALITY_HIGH";
        VideoQuality[VideoQuality["VIDEO_QUALITY_LOW"] = "video_quality_low"] = "VIDEO_QUALITY_LOW";
        VideoQuality[VideoQuality["VIDEO_QUALITY_MEDIUM"] = "video_quality_medium"] = "VIDEO_QUALITY_MEDIUM";
        VideoQuality[VideoQuality["VIDEO_QUALITY_QQVGA"] = "video_quality_qqvga"] = "VIDEO_QUALITY_QQVGA";
        VideoQuality[VideoQuality["VIDEO_QUALITY_QCIF"] = "video_quality_qcif"] = "VIDEO_QUALITY_QCIF";
        VideoQuality[VideoQuality["VIDEO_QUALITY_QVGA"] = "video_quality_qvga"] = "VIDEO_QUALITY_QVGA";
        VideoQuality[VideoQuality["VIDEO_QUALITY_CIF"] = "video_quality_cif"] = "VIDEO_QUALITY_CIF";
        VideoQuality[VideoQuality["VIDEO_QUALITY_nHD"] = "video_quality_nhd"] = "VIDEO_QUALITY_nHD";
        VideoQuality[VideoQuality["VIDEO_QUALITY_VGA"] = "video_quality_vga"] = "VIDEO_QUALITY_VGA";
        VideoQuality[VideoQuality["VIDEO_QUALITY_SVGA"] = "video_quality_svga"] = "VIDEO_QUALITY_SVGA";
        VideoQuality[VideoQuality["VIDEO_QUALITY_HD"] = "video_quality_hd"] = "VIDEO_QUALITY_HD";
        VideoQuality[VideoQuality["VIDEO_QUALITY_UXGA"] = "video_quality_uxga"] = "VIDEO_QUALITY_UXGA";
        VideoQuality[VideoQuality["VIDEO_QUALITY_FHD"] = "video_quality_fhd"] = "VIDEO_QUALITY_FHD";
        VideoQuality[VideoQuality["VIDEO_QUALITY_UHD"] = "video_quality_uhd"] = "VIDEO_QUALITY_UHD";
    })(VideoQuality = Hardware.VideoQuality || (Hardware.VideoQuality = {}));

    var StreamManager = function (_EventDispatcher_1$Ev) {
        _inherits(StreamManager, _EventDispatcher_1$Ev);

        /**
         * @hidden
         */
        function StreamManager() {
            _classCallCheck(this, StreamManager);

            var _this = _possibleConstructorReturn(this, (StreamManager.__proto__ || Object.getPrototypeOf(StreamManager)).call(this));

            if (typeof StreamManager.instance !== "undefined") throw new Error("Error - use StreamManager.get()");
            _this._callStreams = {};
            return _this;
        }
        /**
         * Get the StreamManager instance
         */


        _createClass(StreamManager, [{
            key: "onMirrorEnded",

            /**
             * For onended and onmute callback of the mirror stream
             * @hidden
             */
            value: function onMirrorEnded() {
                var _this2 = this;

                this.remMirrorStream();
                this.getMirrorStream().then(function (stream) {
                    _this2.dispatchEvent({ name: StreamEvents.MirrorUpdated, stream: stream });
                });
            }
            /**
             * Return link to the mirror stream, if exist. Or get a new one.
             * @hidden
             * @return {Promise<MediaStream>}
             */

        }, {
            key: "getMirrorStream",
            value: function getMirrorStream() {
                var _this3 = this;

                return new Promise(function (resolve, reject) {
                    if (typeof _this3._mirrorStream !== "undefined") resolve(_this3._mirrorStream);else {
                        return navigator.mediaDevices.getUserMedia({ video: CameraManager.get().getCallConstraints('__local__') }).then(function (stream) {
                            _this3._mirrorStream = stream;
                            _this3._mirrorStream.getTracks().forEach(function (track) {
                                track.onended = _this3.onMirrorEnded;
                                track.onmute = _this3.onMirrorEnded;
                            });
                            resolve(stream);
                        }, reject);
                    }
                });
            }
            /**
             *
             */

        }, {
            key: "remMirrorStream",
            value: function remMirrorStream() {
                if (typeof this._mirrorStream === "undefined") return;
                this._mirrorStream.getTracks().forEach(function (track) {
                    track.onended = undefined;
                    track.onmute = undefined;
                    track.stop();
                });
                this._mirrorStream = undefined;
            }
            /**
             * For onended and onmute callback of the mirror stream
             * @hidden
             */

        }, {
            key: "onEchoEnded",
            value: function onEchoEnded() {
                var _this4 = this;

                this.getEchoStream();
                this.getEchoStream().then(function (stream) {
                    _this4.dispatchEvent({ name: StreamEvents.MirrorUpdated, stream: stream });
                });
            }
            /**
             * Return link to the mirror stream, if exist. Or get a new one.
             * @hidden
             * @return {Promise<MediaStream>}
             */

        }, {
            key: "getEchoStream",
            value: function getEchoStream() {
                var _this5 = this;

                return new Promise(function (resolve, reject) {
                    if (typeof _this5._echoStream !== "undefined") resolve(_this5._echoStream);else {
                        return navigator.mediaDevices.getUserMedia({ audio: AudioDeviceManager.get().getCallConstraints('__local__') }).then(function (stream) {
                            _this5._echoStream = stream;
                            _this5._echoStream.getTracks().forEach(function (track) {
                                track.onended = _this5.onEchoEnded;
                                track.onmute = _this5.onEchoEnded;
                            });
                            resolve(stream);
                        }, reject);
                    }
                });
            }
            /**
             *
             */

        }, {
            key: "remEchoStream",
            value: function remEchoStream() {
                if (typeof this._echoStream === "undefined") return;
                this._echoStream.getTracks().forEach(function (track) {
                    track.onended = undefined;
                    track.onmute = undefined;
                    track.stop();
                });
                this._echoStream = undefined;
            }
        }, {
            key: "getCallStream",
            value: function getCallStream(call) {
                var _this6 = this;

                return new Promise(function (resolve, reject) {
                    if (_this6._callStreams[call.id()]) return _this6._callStreams[call.id()];else return navigator.mediaDevices.getUserMedia(_this6._composeConstraints(call)).then(function (stream) {
                        _this6._callStreams[call.id()] = stream;
                        stream.getTracks().forEach(function (track) {
                            track.onended = _this6.omCallEnded;
                            track.onmute = _this6.omCallEnded;
                        });
                        resolve(stream);
                    }, reject);
                });
            }
        }, {
            key: "omCallEnded",
            value: function omCallEnded() {}
        }, {
            key: "_updateCallStream",
            value: function _updateCallStream(call) {
                this.remCallStream(call);
                return this.getCallStream(call);
            }
        }, {
            key: "remCallStream",
            value: function remCallStream(call) {
                if (this._callStreams[call.id()]) {
                    this._callStreams[call.id()].getTracks().forEach(function (track) {
                        track.onended = undefined;
                        track.onmute = undefined;
                        track.stop();
                    });
                    this._callStreams[call.id()] = undefined;
                }
            }
        }, {
            key: "clear",
            value: function clear() {
                this._mirrorStream.getTracks().forEach(function (track) {
                    track.onended = undefined;
                    track.onmute = undefined;
                    track.stop();
                });
                this._mirrorStream = undefined;
                for (var key in this._callStreams) {
                    if (this._callStreams.hasOwnProperty(key)) {
                        var stream = this._callStreams[key];
                        stream.getTracks().forEach(function (track) {
                            track.onended = undefined;
                            track.onmute = undefined;
                            track.stop();
                        });
                    }
                }
                this._callStreams = {};
            }
        }, {
            key: "_composeConstraints",
            value: function _composeConstraints(call) {
                var constraints = {};
                if (call.settings.videoDirections.sendVideo) {
                    constraints.video = CameraManager.get().getCallConstraints(call.id());
                } else {
                    constraints.video = false;
                }
                if (call.settings.audioDirections.sendAudio) {
                    constraints.audio = AudioDeviceManager.get().getCallConstraints(call.id());
                } else {
                    constraints.audio = false;
                }
                return constraints;
            }
            /**
             * @hidden
             * @return {string}
             * @private
             */

        }, {
            key: "_traceName",
            value: function _traceName() {
                return 'StreamManager';
            }
        }], [{
            key: "get",
            value: function get() {
                if (typeof StreamManager.instance === "undefined") StreamManager.instance = new StreamManager();
                return StreamManager.instance;
            }
        }]);

        return StreamManager;
    }(EventDispatcher_1.EventDispatcher);

    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], StreamManager.prototype, "onMirrorEnded", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], StreamManager.prototype, "getMirrorStream", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], StreamManager.prototype, "remMirrorStream", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], StreamManager.prototype, "onEchoEnded", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], StreamManager.prototype, "getEchoStream", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], StreamManager.prototype, "remEchoStream", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], StreamManager.prototype, "getCallStream", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], StreamManager.prototype, "omCallEnded", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], StreamManager.prototype, "_updateCallStream", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], StreamManager.prototype, "remCallStream", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], StreamManager.prototype, "clear", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], StreamManager.prototype, "_composeConstraints", null);
    Hardware.StreamManager = StreamManager;

    var AudioDeviceManager = function () {
        /**
         * @hidden
         */
        function AudioDeviceManager() {
            _classCallCheck(this, AudioDeviceManager);

            if (typeof AudioDeviceManager.instance !== "undefined") throw new Error("Error - use StreamManager.get()");
            this._supportedConstraints = navigator.mediaDevices.getSupportedConstraints();
            this._defaultParams = {};
            this._lastAudioInputDevices = [];
            this._lastAudioOutputDevices = [];
            this._callParams = {};
        }
        /**
         * Get the StreamManager instance
         */


        _createClass(AudioDeviceManager, [{
            key: "getInputDevices",
            value: function getInputDevices() {
                var _this7 = this;

                return navigator.mediaDevices.enumerateDevices().then(function (devices) {
                    _this7._lastAudioInputDevices = devices.map(function (device) {
                        if (device.kind === 'audio' || device.kind === 'audioinput') {
                            return {
                                id: device.deviceId,
                                name: device.label,
                                group: device.groupId
                            };
                        }
                    });
                    return _this7._lastAudioInputDevices;
                });
            }
        }, {
            key: "getCameraDevices",
            value: function getCameraDevices() {
                var _this8 = this;

                return navigator.mediaDevices.enumerateDevices().then(function (devices) {
                    _this8._lastAudioOutputDevices = devices.map(function (device) {
                        if (device.kind === 'audiooutput') {
                            return {
                                id: device.deviceId,
                                name: device.label,
                                group: device.groupId
                            };
                        }
                    });
                    return _this8._lastAudioOutputDevices;
                });
            }
        }, {
            key: "setDefaultAudio",
            value: function setDefaultAudio(params) {
                this._defaultParams = params;
            }
        }, {
            key: "setAudio",
            value: function setAudio(call, params) {
                this._callParams[call.id()] = params;
            }
        }, {
            key: "getCallConstraints",
            value: function getCallConstraints(callID) {
                if (this._callParams[callID]) return this._getAudioConstraints(this._callParams[callID]);else {
                    this._callParams[callID] = this._defaultParams;
                    return this._getAudioConstraints(this._defaultParams);
                }
            }
        }, {
            key: "_getAudioConstraints",
            value: function _getAudioConstraints(params) {
                var constraintsType = 'ideal';
                if (params.strict) constraintsType = 'exact';
                var audioConstraints = {};
                if (params.inputId) {
                    if (this._lastAudioInputDevices.some(function (item) {
                        return item.id === params.inputId;
                    })) {
                        audioConstraints['deviceId'] = {};
                        audioConstraints['deviceId'][constraintsType] = params.inputId;
                    } else Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.USERMEDIA, 'Warning:', Logger_1.LogLevel.WARNING, "There is no audio input device with id " + params.inputId);
                }
                if (params.echoCancellation && this._supportedConstraints['echoCancellation']) {
                    audioConstraints['echoCancellation'] = params.echoCancellation;
                }
                if (params.noiseSuppression && this._supportedConstraints['noiseSuppression']) {
                    audioConstraints['noiseSuppression'] = params.echoCancellation;
                }
                return audioConstraints;
            }
            /**
             * @hidden
             * @return {string}
             * @private
             */

        }, {
            key: "_traceName",
            value: function _traceName() {
                return 'AudioDeviceManager';
            }
        }], [{
            key: "get",
            value: function get() {
                if (typeof AudioDeviceManager.instance === "undefined") AudioDeviceManager.instance = new AudioDeviceManager();
                return AudioDeviceManager.instance;
            }
        }]);

        return AudioDeviceManager;
    }();

    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], AudioDeviceManager.prototype, "getInputDevices", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], AudioDeviceManager.prototype, "getCameraDevices", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], AudioDeviceManager.prototype, "setDefaultAudio", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], AudioDeviceManager.prototype, "setAudio", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], AudioDeviceManager.prototype, "getCallConstraints", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], AudioDeviceManager.prototype, "_getAudioConstraints", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], AudioDeviceManager, "get", null);
    Hardware.AudioDeviceManager = AudioDeviceManager;

    var CameraManager = function () {
        /**
         * @hidden
         */
        function CameraManager() {
            _classCallCheck(this, CameraManager);

            if (typeof CameraManager.instance !== "undefined") throw new Error("Error - use StreamManager.get()");
            this._supportedConstraints = navigator.mediaDevices.getSupportedConstraints();
            this._callParams = {};
            this._lastCameraDevices = [];
            this._defaultParams = {};
        }
        /**
         * Get the StreamManager instance
         */


        _createClass(CameraManager, [{
            key: "setDefaultCamera",
            value: function setDefaultCamera(params) {
                var validParams = this._validateCameraParams(params);
                this._defaultParams = validParams;
            }
        }, {
            key: "setCamera",
            value: function setCamera(call, params) {
                var validParams = this._validateCameraParams(params);
                this._callParams[call.id()] = validParams;
            }
        }, {
            key: "getCallConstraints",
            value: function getCallConstraints(callID) {
                if (this._callParams[callID]) return this._getVideoConstraints(this._callParams[callID]);else {
                    this._callParams[callID] = this._defaultParams;
                    return this._getVideoConstraints(this._defaultParams);
                }
            }
        }, {
            key: "getOutputDevices",
            value: function getOutputDevices() {
                var _this9 = this;

                return navigator.mediaDevices.enumerateDevices().then(function (devices) {
                    _this9._lastCameraDevices = devices.map(function (device) {
                        if (device.kind === 'video' || device.kind === 'videoinput') {
                            return {
                                id: device.deviceId,
                                name: device.label,
                                group: device.groupId
                            };
                        }
                    });
                    return _this9._lastCameraDevices;
                });
            }
        }, {
            key: "_getVideoConstraints",
            value: function _getVideoConstraints(params) {
                var constraintsType = 'ideal';
                var videoConstraints = {};
                if (params.cameraId) {
                    if (this._lastCameraDevices.some(function (item) {
                        return item.id === params.cameraId;
                    })) {
                        videoConstraints['deviceId'] = {};
                        videoConstraints['deviceId'][constraintsType] = params.cameraId;
                    } else Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.USERMEDIA, 'Warning:', Logger_1.LogLevel.WARNING, "There is no video device with id " + params.cameraId);
                } else if (typeof params.facingMode !== "undefined") {
                    if (params.facingMode === false) {
                        videoConstraints['facingMode'] = 'environment';
                    } else {
                        videoConstraints['facingMode'] = 'user';
                    }
                }
                if (params.frameHeight) {
                    videoConstraints['height'] = {};
                    if (params.strict) {
                        videoConstraints['height']['min'] = params.frameHeight;
                        videoConstraints['height']['max'] = params.frameHeight;
                    } else videoConstraints['height'][constraintsType] = params.frameHeight;
                }
                if (params.frameWidth) {
                    videoConstraints['width'] = {};
                    if (params.strict) {
                        videoConstraints['width']['min'] = params.frameWidth;
                        videoConstraints['width']['max'] = params.frameWidth;
                    } else videoConstraints['width'][constraintsType] = params.frameWidth;
                }
                if (params.frameRate && params.frameRate > 0 && this._supportedConstraints['frameRate']) {
                    videoConstraints['frameRate'] = params.frameRate + '';
                }
                return videoConstraints;
            }
        }, {
            key: "_validateCameraParams",
            value: function _validateCameraParams(params) {
                if (params.videoQuality) {
                    if (params.frameHeight || params.frameWidth) Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.USERMEDIA, 'Warning:', Logger_1.LogLevel.WARNING, '"videoQuality" parameter detected. The "frameHeight" ' + 'and the "frameWidth" params will be ignored');
                    switch (params.videoQuality) {
                        case VideoQuality.VIDEO_QUALITY_HIGH:
                            params.frameWidth = 1280;
                            params.frameHeight = 720;
                            break;
                        case VideoQuality.VIDEO_QUALITY_MEDIUM:
                            params.frameWidth = 640;
                            params.frameHeight = 480;
                            break;
                        case VideoQuality.VIDEO_QUALITY_LOW:
                            params.frameWidth = 320;
                            params.frameHeight = 240;
                            break;
                        case VideoQuality.VIDEO_QUALITY_QQVGA:
                            params.frameWidth = 160;
                            params.frameHeight = 120;
                            break;
                        case VideoQuality.VIDEO_QUALITY_QCIF:
                            params.frameWidth = 176;
                            params.frameHeight = 144;
                            break;
                        case VideoQuality.VIDEO_QUALITY_QVGA:
                            params.frameWidth = 320;
                            params.frameHeight = 240;
                            break;
                        case VideoQuality.VIDEO_QUALITY_CIF:
                            params.frameWidth = 352;
                            params.frameHeight = 288;
                            break;
                        case VideoQuality.VIDEO_QUALITY_nHD:
                            params.frameWidth = 640;
                            params.frameHeight = 360;
                            break;
                        case VideoQuality.VIDEO_QUALITY_VGA:
                            params.frameWidth = 640;
                            params.frameHeight = 480;
                            break;
                        case VideoQuality.VIDEO_QUALITY_SVGA:
                            params.frameWidth = 800;
                            params.frameHeight = 600;
                            break;
                        case VideoQuality.VIDEO_QUALITY_HD:
                            params.frameWidth = 1280;
                            params.frameHeight = 720;
                            break;
                        case VideoQuality.VIDEO_QUALITY_UXGA:
                            params.frameWidth = 1600;
                            params.frameHeight = 1200;
                            break;
                        case VideoQuality.VIDEO_QUALITY_FHD:
                            params.frameWidth = 1920;
                            params.frameHeight = 1080;
                            break;
                        case VideoQuality.VIDEO_QUALITY_UHD:
                            params.frameWidth = 3840;
                            params.frameHeight = 2160;
                            break;
                        default:
                            params.frameWidth = 320;
                            params.frameHeight = 240;
                            break;
                    }
                }
                return params;
            }
        }, {
            key: "_traceName",

            /**
             * @hidden
             * @return {string}
             * @private
             */
            value: function _traceName() {
                return 'CameraManager';
            }
        }], [{
            key: "get",
            value: function get() {
                if (typeof CameraManager.instance === "undefined") CameraManager.instance = new CameraManager();
                return CameraManager.instance;
            }
        }, {
            key: "legacyParamConverter",
            value: function legacyParamConverter(videoParams) {
                var params = {
                    videoQuality: VideoQuality.VIDEO_QUALITY_MEDIUM
                };
                if (videoParams.width) {
                    if (typeof videoParams.width === "string" || typeof videoParams.width === "number") {
                        delete params.videoQuality;
                        params.frameWidth = videoParams.width;
                    } else if (typeof videoParams.width.exact === "string" || typeof videoParams.width.exact === "number") {
                        delete params.videoQuality;
                        params.frameWidth = videoParams.width.exact;
                        params.strict = true;
                    } else if (typeof videoParams.width.min === "string" || typeof videoParams.width.min === "number") {
                        delete params.videoQuality;
                        params.frameWidth = videoParams.width.min;
                    } else if (typeof videoParams.width.max === "string" || typeof videoParams.width.max === "number") {
                        delete params.videoQuality;
                        params.frameWidth = videoParams.width.max;
                    } else if (typeof videoParams.width.ideal === "string" || typeof videoParams.width.ideal === "number") {
                        delete params.videoQuality;
                        params.frameWidth = videoParams.width.ideal;
                    }
                }
                if (videoParams.height) {
                    if (typeof videoParams.height === "string" || typeof videoParams.height === "number") {
                        delete params.videoQuality;
                        params.frameHeight = videoParams.height;
                    } else if (typeof videoParams.height.exact === "string" || typeof videoParams.height.exact === "number") {
                        delete params.videoQuality;
                        params.frameHeight = videoParams.height.exact;
                        params.strict = true;
                    } else if (typeof videoParams.height.min === "string" || typeof videoParams.height.min === "number") {
                        delete params.videoQuality;
                        params.frameHeight = videoParams.height.min;
                    } else if (typeof videoParams.height.max === "string" || typeof videoParams.height.max === "number") {
                        delete params.videoQuality;
                        params.frameHeight = videoParams.height.max;
                    } else if (typeof videoParams.height.ideal === "string" || typeof videoParams.height.ideal === "number") {
                        delete params.videoQuality;
                        params.frameHeight = videoParams.height.ideal;
                    }
                }
                return params;
            }
        }]);

        return CameraManager;
    }();

    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], CameraManager.prototype, "setDefaultCamera", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], CameraManager.prototype, "setCamera", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], CameraManager.prototype, "getCallConstraints", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], CameraManager.prototype, "getOutputDevices", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], CameraManager.prototype, "_getVideoConstraints", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], CameraManager.prototype, "_validateCameraParams", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], CameraManager, "get", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.HARDWARE)], CameraManager, "legacyParamConverter", null);
    Hardware.CameraManager = CameraManager;
    // function on(event:HardwareEvents,handler:Function):void{
    //     if(event === <any>HardwareEvents[HardwareEvents.DeviceChange]){
    //         navigator.mediaDevices.addEventListener('devicechange',handler);
    //     }
    // }
    // function off(event:HardwareEvents,handler?:Function){
    //     if(event === <any>HardwareEvents[HardwareEvents.DeviceChange]){
    //         if(handler)
    //             navigator.mediaDevices.removeEventListener('devicechange',handler);
    //         else
    //             navigator.mediaDevices.removeEventListener('devicechange');
    //     }
    // }
})(Hardware = exports.Hardware || (exports.Hardware = {}));
exports.default = Hardware;

/***/ }),
/* 14 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _get = function get(object, property, receiver) { if (object === null) object = Function.prototype; var desc = Object.getOwnPropertyDescriptor(object, property); if (desc === undefined) { var parent = Object.getPrototypeOf(object); if (parent === null) { return undefined; } else { return get(parent, property, receiver); } } else if ("value" in desc) { return desc.value; } else { var getter = desc.get; if (getter === undefined) { return undefined; } return getter.call(receiver); } };

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var __decorate = undefined && undefined.__decorate || function (decorators, target, key, desc) {
    var c = arguments.length,
        r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc,
        d;
    if ((typeof Reflect === "undefined" ? "undefined" : _typeof(Reflect)) === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);else for (var i = decorators.length - 1; i >= 0; i--) {
        if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    }return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var CallEvents_1 = __webpack_require__(9);
var VoxSignaling_1 = __webpack_require__(1);
var Constants_1 = __webpack_require__(11);
var Logger_1 = __webpack_require__(0);
var UserMediaManager_1 = __webpack_require__(4);
var PCFactory_1 = __webpack_require__(8);
var CallManager_1 = __webpack_require__(6);
var BrowserSpecific_1 = __webpack_require__(5);
var RemoteFunction_1 = __webpack_require__(2);
var ReusableRenderer_1 = __webpack_require__(34);
var Client_1 = __webpack_require__(3);
var EventDispatcher_1 = __webpack_require__(15);
/**
 * @hidden
 */
var CallState;
(function (CallState) {
    CallState[CallState["ALERTING"] = "ALERTING"] = "ALERTING";
    CallState[CallState["PROGRESSING"] = "PROGRESSING"] = "PROGRESSING";
    CallState[CallState["CONNECTED"] = "CONNECTED"] = "CONNECTED";
    CallState[CallState["UPDATING"] = "UPDATING"] = "UPDATING";
    CallState[CallState["ENDED"] = "ENDED"] = "ENDED";
})(CallState = exports.CallState || (exports.CallState = {}));
/**
 * @hidden
 */
var CallMode;
(function (CallMode) {
    CallMode[CallMode["P2P"] = 0] = "P2P";
    CallMode[CallMode["SERVER"] = 1] = "SERVER";
})(CallMode = exports.CallMode || (exports.CallMode = {}));
/**
 *
 */

var Call = function (_EventDispatcher_1$Ev) {
    _inherits(Call, _EventDispatcher_1$Ev);

    /**
     * @hidden
     */
    function Call(id, dn, incoming, settings) {
        _classCallCheck(this, Call);

        /**
         * @hidden
         */
        var _this = _possibleConstructorReturn(this, (Call.__proto__ || Object.getPrototypeOf(Call)).call(this));

        _this.remoteMuteState = true;
        _this.signalingConnected = false;
        _this.settings = settings;
        _this.settings.id = id;
        _this.settings.displayName = dn;
        _this.settings.mode = CallMode.P2P;
        _this.settings.active = true;
        _this.settings.usedSinkId = null;
        _this.settings.incoming = incoming;
        _this.settings.state = incoming ? CallState.ALERTING : CallState.PROGRESSING;
        var appConfig = Client_1.Client.getInstance().config();
        if (appConfig.experiments && appConfig.experiments.hardware) {
            _this.settings.audioDirections = { sendAudio: true };
        } else {
            _this.settings.audioDirections = { sendAudio: false };
        }
        _this.settings.videoDirections = typeof settings.video === "boolean" ? { sendVideo: settings.video, receiveVideo: true } : settings.video;
        _this.settings.hasEarlyMedia = false;
        _this.log = Logger_1.LogManager.get().createLogger(Logger_1.LogCategory.CALL, "Call " + id);
        _this._callManager = CallManager_1.CallManager.get();
        _this.on(CallEvents_1.CallEvents.Connected, function () {
            _this.startTime = Date.now();
        });
        return _this;
    }
    /**
     * Returns call id
     * @returns {String}
     */


    _createClass(Call, [{
        key: "id",
        value: function id() {
            return this.settings.id;
        }
        /**
         * Returns dialed number or caller id
         * @returns {String}
         */

    }, {
        key: "number",
        value: function number() {
            return this.settings.number;
        }
        /**
         * Returns display name
         */

    }, {
        key: "displayName",
        value: function displayName() {
            return this.settings.displayName;
        }
        /**
         * Returns headers
         * @returns {Object}
         */

    }, {
        key: "headers",
        value: function headers() {
            return this.settings.extraHeaders;
        }
        /**
         * Returns information about the call's media state (active/inactive)
         */

    }, {
        key: "active",
        value: function active() {
            return this.settings.active;
        }
        /**
         * Get call's current state
         * may be "ALERTING", "PROGRESSING", "CONNECTED", "ENDED"
         * @returns {String}
         */

    }, {
        key: "state",
        value: function state() {
            return CallState[this.settings.state];
        }
        /**
         * @hidden
         * @returns {CallState}
         */

    }, {
        key: "answer",

        /**
         * Answer on incoming call
         * @param {String} customData Set custom string associated with call session. It can be later obtained from Call History using HTTP API. Maximum size is 200 bytes.
         * @param {Object} extraHeaders Optional custom parameters (SIP headers) that should be sent after accepting incoming call. Parameter names must start with "X-" to be processed by application
         * @param {boolean} useVideo Optional parameter that can attach or detach video for current call. By default equal Config.videoSupport flag
         */
        value: function answer(customData, extraHeaders, useVideo) {
            if (typeof customData != 'undefined') {
                if (typeof extraHeaders == 'undefined' || (typeof extraHeaders === "undefined" ? "undefined" : _typeof(extraHeaders)) !== "object") extraHeaders = {};
                extraHeaders[Constants_1.Constants.CALL_DATA_HEADER] = customData;
            }
            if (typeof useVideo !== "undefined") useVideo = {
                sendVideo: Client_1.Client.getInstance().config().videoSupport,
                receiveVideo: Client_1.Client.getInstance().config().videoSupport
            };
            if (this.settings.state != CallState.ALERTING) throw new Error("WRONG_CALL_STATE");
            if (typeof useVideo != "undefined") {
                this._peerConnection.setVideoFlags(useVideo);
            }
        }
        /**
         * @name VoxImplant.Call.decline
         * Reject incoming call on all devices, where this user logged in.
         * @param {Object} extraHeaders Optional custom parameters (SIP headers) that should be sent after rejecting incoming call. Parameter names must start with "X-" to be processed by applicatio
         */

    }, {
        key: "decline",
        value: function decline(extraHeaders) {
            if (this.settings.state != CallState.ALERTING) throw new Error("WRONG_CALL_STATE");
            VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.rejectCall, this.settings.id, false, CallManager_1.CallManager.cleanHeaders(extraHeaders));
        }
        /**
         * Reject incoming call only on current device.
         * @param {Object} extraHeaders Optional custom parameters (SIP headers) that should be sent after rejecting incoming call. Parameter names must start with "X-" to be processed by application
         */

    }, {
        key: "reject",
        value: function reject(extraHeaders) {
            if (this.settings.state != CallState.ALERTING) throw new Error("WRONG_CALL_STATE");
            VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.rejectCall, this.settings.id, true, CallManager_1.CallManager.cleanHeaders(extraHeaders));
        }
        /**
         * Hangup call
         * @param {[id:string]:string} extraHeaders Optional custom parameters (SIP headers) that should be sent after disconnecting/cancelling call. Parameter names must start with "X-" to be processed by application
         */

    }, {
        key: "hangup",
        value: function hangup(extraHeaders) {
            if (this.settings.state == CallState.CONNECTED || this.settings.state == CallState.UPDATING || this.settings.state == CallState.PROGRESSING) VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.disconnectCall, this.settings.id, CallManager_1.CallManager.cleanHeaders(extraHeaders));else if (this.settings.state == CallState.ALERTING) VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.rejectCall, this.settings.id, true, CallManager_1.CallManager.cleanHeaders(extraHeaders));else throw new Error("WRONG_CALL_STATE");
        }
        /**
         * Send tone (DTMF)
         * @param {String} key Send tone according to pressed key: 0-9 , * , #
         */

    }, {
        key: "sendTone",
        value: function sendTone(key) {
            if (this.settings.active) this._peerConnection.sendDTMF(key);
        }
        /**
         * Mute sound
         */

    }, {
        key: "mutePlayback",
        value: function mutePlayback() {
            this.remoteMuteState = false;
            UserMediaManager_1.UserMediaManager.updateMuteState(this.settings.id, UserMediaManager_1.MediaDirection.REMOTE, false);
        }
        /**
         * Unmute sound
         */

    }, {
        key: "unmutePlayback",
        value: function unmutePlayback() {
            this.remoteMuteState = true;
            UserMediaManager_1.UserMediaManager.updateMuteState(this.settings.id, UserMediaManager_1.MediaDirection.REMOTE, true);
        }
        /**
         * @hidden
         */

    }, {
        key: "restoreRMute",
        value: function restoreRMute() {
            if (this.settings.active) UserMediaManager_1.UserMediaManager.updateMuteState(this.settings.id, UserMediaManager_1.MediaDirection.REMOTE, this.remoteMuteState);
        }
        /**
         * Mute microphone
         */

    }, {
        key: "muteMicrophone",
        value: function muteMicrophone() {
            this.peerConnection.muteMicrophone(true);
        }
        /**
         * Unmute microphone
         */

    }, {
        key: "unmuteMicrophone",
        value: function unmuteMicrophone() {
            this.peerConnection.muteMicrophone(false);
        }
        /**
         * Show/hide remote party video
         * @param {Boolean} [flag=true] Show/hide - true/false
         */

    }, {
        key: "showRemoteVideo",
        value: function showRemoteVideo() {
            var flag = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

            if (typeof flag == "undefined") flag = true;
            if (this.peerConnection.videoRenderer) this.peerConnection.videoRenderer.style.display = flag ? "block" : "none";
        }
        /**
         * Set remote video position
         * @name VoxImplant.Call.setRemoteVideoPosition
         * @param {Number} x Horizontal position (px)
         * @param {Number} y Vertical position (px)
         * @function
         * @hidden
         */

    }, {
        key: "setRemoteVideoPosition",
        value: function setRemoteVideoPosition(x, y) {
            throw new Error("Deprecated: please use CSS to position '#voximplantcontainer' element");
        }
        /**
         * Set remote video size
         * @name VoxImplant.Call.setRemoteVideoSize
         * @param {Number} width Width in pixels
         * @param {Number} height Height in pixels
         * @function
         * @hidden
         */

    }, {
        key: "setRemoteVideoSize",
        value: function setRemoteVideoSize(width, height) {
            throw new Error("Deprecated: please use CSS to set size of '#voximplantcontainer' element");
        }
        /**
         * Send Info (SIP INFO) message inside the call
         * <br>
         * You can receive this message by <a href="http://voximplant.com/docs/references/appengine/CallEvents.html#CallEvents_InfoReceived">VoxEngine CallEvents.InfoReceived</a> in our cloud.
         * <br>
         * You can receive this message by <a href="../enums/callevents.html#inforeceived">CallEvents.InfoReceived</a> in WebSDK on other side.
         * @param {String} mimeType MIME type of the message
         * @param {String} body Message content
         * @param {[id:string]:string} extraHeaders Optional headers to be passed with the message
         */

    }, {
        key: "sendInfo",
        value: function sendInfo(mimeType, body, extraHeaders) {
            var type,
                subtype,
                i = mimeType.indexOf('/');
            if (i == -1) {
                type = "application";
                subtype = mimeType;
            } else {
                type = mimeType.substring(0, i);
                subtype = mimeType.substring(i + 1);
            }
            //if (this._state != CallState.CONNECTED)
            //    throw new Error("WRONG_CALL_STATE");
            VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.sendSIPInfo, this.settings.id, type, subtype, body, CallManager_1.CallManager.cleanHeaders(extraHeaders));
        }
        /**
         * Send text message
         * @param {String} msg Message text
         */

    }, {
        key: "sendMessage",
        value: function sendMessage(msg) {
            this.sendInfo(Constants_1.Constants.ZINGAYA_IM_MIME_TYPE, msg, {});
        }
        /**
         * Set video settings
         * @param {VoxImplant.VideoSettings|VoxImplant.FlashVideoSettings} settings Video settings for current call
         * @param {Function} [successCallback] Called in WebRTC mode if video settings were applied successfully
         * @param {Function} [failedCallback] Called in WebRTC mode if video settings couldn't be applied
         */

    }, {
        key: "setVideoSettings",
        value: function setVideoSettings(settings, successCallback, failedCallback) {
            //this.zingayaAPI().setConstraints(settings, successCallback, failedCallback, true);
            UserMediaManager_1.UserMediaManager.get().setConstraints(settings, true).then(function (stream) {
                if (typeof successCallback == "function") successCallback(stream);
            }).catch(function (err) {
                if (typeof failedCallback == "function") failedCallback(err);
            });
        }
        /**
         * Returns HTML video element's id for the call
         */

    }, {
        key: "getVideoElementId",
        value: function getVideoElementId() {
            if (this.peerConnection && this.settings.wiredRemote) {
                var videoRender = this.peerConnection.videoRenderer;
                return videoRender != null ? videoRender.id : null;
            } else {
                return null;
            }
        }
        /**
         * Register handler for specified event
         * <br>
         * Example:
         * <script src="https://gist.github.com/irbisadm/5d4ceec909be7bbcb14361ee8bba5e6d.js"></script>
         * @param {Function} event Event class (i.e. {<a href="../enums/callevents.html#connected">VoxImplant.CallEvents.Connected</a>). See <a href="../enums/callevents.html#connected">VoxImplant.CallEvents</a>
         * @param {Function} handler Handler function. A single parameter is passed - object with event information
         */

    }, {
        key: "addEventListener",
        value: function addEventListener(event, handler) {
            _get(Call.prototype.__proto__ || Object.getPrototypeOf(Call.prototype), "addEventListener", this).call(this, event, handler);
        }
        /**
         * Register handler for specified event
         * @param {Function} event Event class (i.e. <a href="../enums/callevents.html#connected">VoxImplant.CallEvents.Connected</a>). See <a href="../enums/callevents.html">VoxImplant.CallEvents</a>
         * @param {Function} handler Handler function. A single parameter is passed - object with event information
         */

    }, {
        key: "on",
        value: function on(event, handler) {
            _get(Call.prototype.__proto__ || Object.getPrototypeOf(Call.prototype), "on", this).call(this, event, handler);
        }
        /**
         * Remove handler for specified event
         * @param {Function} event Event class (i.e. <a href="../enums/callevents.html#connected">VoxImplant.CallEvents.Connected</a>). See <a href="../enums/callevents.html">VoxImplant.CallEvents</a>
         * @param {Function} handler Handler function, if not specified all event handlers will be removed
         */

    }, {
        key: "removeEventListener",
        value: function removeEventListener(event, handler) {
            _get(Call.prototype.__proto__ || Object.getPrototypeOf(Call.prototype), "removeEventListener", this).call(this, event, handler);
        }
        /**
         * Remove handler for specified event
         * @param {Function} event Event class (i.e. {@link VoxImplant.CallEvents.Connected}). See {@link VoxImplant.CallEvents}
         * @param {Function} handler Handler function, if not specified all event handlers will be removed
         * @function
         */

    }, {
        key: "off",
        value: function off(event, handler) {
            _get(Call.prototype.__proto__ || Object.getPrototypeOf(Call.prototype), "off", this).call(this, event, handler);
        }
        /**
         * @hidden
         */

    }, {
        key: "dispatchEvent",
        value: function dispatchEvent(e) {
            if (e.name === CallEvents_1.CallEvents.Updated || e.name === CallEvents_1.CallEvents.UpdateFailed) {
                this.settings.state = CallState.CONNECTED;
            }
            _get(Call.prototype.__proto__ || Object.getPrototypeOf(Call.prototype), "dispatchEvent", this).call(this, e);
        }
        /**
         *
         * @param validState
         * @param functionName
         * @returns {boolean}
         * @hidden
         */

    }, {
        key: "checkState",
        value: function checkState(validState, functionName) {
            if (validState) {
                if (typeof validState != "string") {
                    var valid = false;
                    var validStateList = validState;
                    for (var i = 0; i < validStateList.length; i++) {
                        if (validStateList[i] == this.settings.state) {
                            valid = true;
                        }
                    }
                    if (!valid) {
                        this.log.warning("Received " + functionName + " in invalid state " + this.settings.state);
                        return false;
                    }
                } else if (this.settings.state != validState) {
                    this.log.warning("Received " + functionName + " in invalid state " + this.settings.state);
                    return false;
                }
            }
            return true;
        }
        /**
         * @hidden
         * @param headers
         * @param sdp
         * @returns {boolean}
         */

    }, {
        key: "onConnected",
        value: function onConnected(headers, sdp) {
            if (this.signalingConnected) {
                if (!this.checkState([CallState.PROGRESSING, CallState.ALERTING], "onConnected")) return false;
                this.settings.state = CallState.CONNECTED;
                this.dispatchEvent({ name: 'Connected', call: this, headers: headers });
                return true;
            }
        }
        /**
         * @hidden
         * @param headers
         * @param params
         * @returns {boolean}
         */

    }, {
        key: "onDisconnected",
        value: function onDisconnected(headers, params) {
            if (!this.checkState([CallState.CONNECTED, CallState.ALERTING, CallState.PROGRESSING, CallState.UPDATING], "onDisconnected")) return false;
            this.settings.state = CallState.ENDED;
            this.dispatchEvent({ name: 'Disconnected', call: this, headers: headers, params: params });
            ReusableRenderer_1.ReusableRenderer.get().freeRendersByCallId(this.settings.id);
            return true;
        }
        /**
         * @hidden
         * @param code
         * @param reason
         * @param headers
         * @returns {boolean}
         */

    }, {
        key: "onFailed",
        value: function onFailed(code, reason, headers) {
            // if (!this.checkState(CallState.PROGRESSING, "onFailed"))
            //     return false;
            this.dispatchEvent({ name: 'Failed', call: this, headers: headers, code: code, reason: reason });
            this.settings.state = CallState.ENDED;
            ReusableRenderer_1.ReusableRenderer.get().freeRendersByCallId(this.settings.id);
            return true;
        }
        /**
         * @hidden
         * @returns {boolean}
         */

    }, {
        key: "onStopRinging",
        value: function onStopRinging() {
            if (!this.checkState([CallState.PROGRESSING, CallState.CONNECTED], "onStopRinging")) return false;
            this.dispatchEvent({ name: 'ProgressToneStop', call: this });
            return true;
        }
        /**
         * @hidden
         * @returns {boolean}
         */

    }, {
        key: "onRingOut",
        value: function onRingOut() {
            if (!this.checkState(CallState.PROGRESSING, "onRingOut")) return false;
            this.dispatchEvent({ name: 'ProgressToneStart', call: this });
            return true;
        }
        /**
         * @hidden
         * @returns {boolean}
         */

    }, {
        key: "onTransferComplete",
        value: function onTransferComplete() {
            if (!this.checkState(CallState.CONNECTED, "onTransferComplete")) return false;
            this.dispatchEvent({ name: 'TransferComplete', call: this });
            return true;
        }
        /**
         * @hidden
         * @returns {boolean}
         */

    }, {
        key: "onTransferFailed",
        value: function onTransferFailed() {
            if (!this.checkState(CallState.CONNECTED, "onTransferFailed")) return false;
            this.dispatchEvent({ name: 'TransferFailed', call: this });
            return true;
        }
        /**
         * @hidden
         * @param call
         * @param type
         * @param subType
         * @param body
         * @param headers
         * @returns {boolean}
         */

    }, {
        key: "onInfo",
        value: function onInfo(call, type, subType, body, headers) {
            if (call.stateValue == CallState.CONNECTED || call.stateValue == CallState.PROGRESSING || call.stateValue == CallState.ALERTING || call.stateValue == CallState.UPDATING) {
                var mimeType = type + "/" + subType;
                if (mimeType == Constants_1.Constants.ZINGAYA_IM_MIME_TYPE) {
                    this.dispatchEvent({ name: 'onSendMessage', call: this, text: body });
                } else if (mimeType == Constants_1.Constants.P2P_SPD_FRAG_MIME_TYPE) {
                    var candidates = JSON.parse(body);
                    for (var i in candidates) {
                        call.peerConnection.addRemoteCandidate(candidates[i][1], candidates[i][0]);
                    }
                } else if (mimeType == Constants_1.Constants.VI_SPD_OFFER_MIME_TYPE) {
                    call.peerConnection.processRemoteOffer(body);
                } else if (mimeType == Constants_1.Constants.VI_SPD_ANSWER_MIME_TYPE) {
                    call.peerConnection.processRemoteAnswer({}, body);
                } else {
                    this.dispatchEvent({
                        name: 'InfoReceived',
                        call: this,
                        body: body,
                        headers: headers,
                        mimeType: mimeType
                    });
                }
                return true;
            } else {
                this.log.warning("received handleSIPInfo for call: " + call.id() + " in invalid state: " + call.state());
            }
        }
    }, {
        key: "setActive",
        value: function setActive(flag) {
            var _this2 = this;

            return new Promise(function (resolve, reject) {
                if (flag === _this2.settings.active) {
                    resolve({ name: CallEvents_1.CallEvents['Updated'], result: false, call: _this2 });
                    return;
                }
                if (BrowserSpecific_1.default.getWSVendor() === "firefox") {
                    _this2.sendInfo(Constants_1.Constants.VI_HOLD_EMUL, JSON.stringify({ hold: !flag }));
                    resolve({ name: CallEvents_1.CallEvents['Updated'], call: _this2, result: true });
                    return;
                }
                if (_this2.settings.state == CallState.CONNECTED) {
                    _this2.settings.state = CallState.UPDATING;
                    _this2.settings.active = flag;
                    if (!flag) VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.hold, _this2.settings.id);else VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.unhold, _this2.settings.id);
                    resolve({ name: CallEvents_1.CallEvents['Updated'], result: true, call: _this2 });
                } else {
                    reject({ name: CallEvents_1.CallEvents.UpdateFailed, code: 11, call: _this2 });
                }
            });
        }
        /**
         * @hidden
         * @returns {Promise<Object>}
         */

    }, {
        key: "checkCallMode",

        /**
         * @hidden
         */
        value: function checkCallMode(mode) {
            return this.settings.mode == mode;
        }
        /**
         * @hidden
         */

    }, {
        key: "canStartSendingCandidates",
        value: function canStartSendingCandidates() {
            if (typeof this._peerConnection == "undefined") this._peerConnection = PCFactory_1.PCFactory.get().peerConnections[this.settings.id];
            this._peerConnection.canStartSendingCandidates();
        }
    }, {
        key: "notifyICETimeout",

        /**
         * @hidden
         */
        value: function notifyICETimeout() {
            this.dispatchEvent({ name: 'ICETimeout', call: this });
        }
        /**
         *
         * @param flag
         */

    }, {
        key: "sendVideo",
        value: function sendVideo(flag) {
            var _this3 = this;

            var appConfig = Client_1.Client.getInstance().config();
            if (appConfig.experiments && appConfig.experiments.reinvite) {
                if (!this.peerConnection) {
                    return new Promise(function (resolve, reject) {
                        resolve({ call: _this3, name: CallEvents_1.CallEvents[CallEvents_1.CallEvents.Updated], result: true });
                    });
                }
                var oldSendVideo = this.settings.videoDirections.sendVideo;
                this.settings.videoDirections.sendVideo = flag;
                if (this.peerConnection.hasLocalVideo()) {
                    if (!flag && !oldSendVideo) {
                        return this.sendMedia(null, flag);
                    } else {
                        this.peerConnection.enableVideo(flag);
                        return new Promise(function (resolve, reject) {
                            resolve({
                                call: _this3,
                                name: CallEvents_1.CallEvents[CallEvents_1.CallEvents.Updated],
                                result: true
                            });
                        });
                    }
                } else if (flag) {
                    return this.sendMedia(null, flag);
                }
            } else {
                if (this.settings.videoDirections.sendVideo === flag) return new Promise(function (resolve, reject) {
                    resolve();
                });
                return this.sendMedia(null, flag);
            }
        }
        /**
         * @hidden
         */

    }, {
        key: "receiveVideo",
        value: function receiveVideo() {
            var _this4 = this;

            this.settings.state = CallState.UPDATING;
            return new Promise(function (resolve, reject) {
                if (_this4.settings.videoDirections.receiveVideo === true) {
                    reject();
                    return;
                }
                _this4.settings.videoDirections.receiveVideo = true;
                _this4._peerConnection.hdnFRS().then(resolve, reject);
            });
        }
        /**
         * @hidden
         * @param audio
         * @param video
         */

    }, {
        key: "sendMedia",
        value: function sendMedia(audio, video) {
            var _this5 = this;

            this.settings.state = CallState.UPDATING;
            if (typeof audio === "undefined" || audio === null) audio = this.settings.audioDirections.sendAudio;
            if (typeof video === "undefined" || video === null) video = this.settings.videoDirections.sendVideo;
            return this.peerConnection.sendMedia(audio, video).then(function (e) {
                if (typeof video !== "undefined" && video !== null) {
                    _this5.settings.videoDirections.sendVideo = video;
                }
                if (typeof audio !== "undefined" && audio !== null) {
                    _this5.settings.audioDirections.sendAudio = audio;
                }
                return e;
            });
        }
        /**
         * @hidden
         * @param flag
         */

    }, {
        key: "sendAudio",
        value: function sendAudio(flag) {
            var _this6 = this;

            var appConfig = Client_1.Client.getInstance().config();
            if (appConfig.experiments && appConfig.experiments.reinvite) {
                this.settings.audioDirections.sendAudio = flag;
                if (this.peerConnection.hasLocalAudio()) {
                    this.peerConnection.muteMicrophone(!flag);
                    return new Promise(function (resolve, reject) {
                        resolve({ call: _this6, name: CallEvents_1.CallEvents[CallEvents_1.CallEvents.Updated], result: true });
                    });
                } else if (flag) {
                    return this.sendMedia(null, flag);
                }
            } else {
                this.settings.state = CallState.UPDATING;
                return this.sendMedia(flag, null);
            }
        }
        // New stream api
        /**
         * Get current PeerConnection LocalStream OR if set wiredLocal === false - try get newOne from UserMediaManager
         * @hidden
         */

    }, {
        key: "getLocalStream",
        value: function getLocalStream() {
            var _this7 = this;

            if (this.settings.wiredLocal) return new Promise(function (resolve, reject) {
                if (_this7.peerConnection) resolve(_this7.peerConnection.localStream);else reject(new Error('We have no PC for this call yet'));
            });else return UserMediaManager_1.UserMediaManager.get().queryMedia();
        }
        /**
         * @hidden
         * @param stream
         * @returns {Promise<void>|Promise}
         */

    }, {
        key: "setLocalStream",
        value: function setLocalStream(stream) {
            //TODO: Not implemented
            return new Promise(function (resolve, reject) {
                reject(new Error('Not implemented'));
            });
        }
        /**
         * Enable screen sharing. Works in Chrome and Firefox. For Chrome, custom
         * extension must be created and installed from this template:
         * "https://github.com/voximplant/voximplant-chrome-extension". "matches"
         * section in the extension's "manifest.json" should be set to app website url(s).
         * Browser will ask user for a window or screen to share. Can be called multiple times
         * to share multiple windows.
         * @param {boolean} showLocalView if set to true, screen sharing preview will be displayed locally same
         * way as it's done for video calls. Default is 'false'
         *
        */

    }, {
        key: "shareScreen",
        value: function shareScreen() {
            var showLocalView = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

            this.settings.state = CallState.UPDATING;
            return this.peerConnection.shareScreen(showLocalView);
        }
        /**
         * Stops screen sharing. If 'shareScreen' was called multiple times, this will stop
         * sharing for all windows/screens
         */

    }, {
        key: "stopSharingScreen",
        value: function stopSharingScreen() {
            this.settings.state = CallState.UPDATING;
            return this.peerConnection.stopSharingScreen();
        }
        /**
         * @hidden
         * @param stream
         */

    }, {
        key: "addLocalStream",
        value: function addLocalStream(stream) {
            var showLocalView = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

            this.settings.state = CallState.UPDATING;
            return this.peerConnection.addMediaStream(stream, showLocalView);
        }
        /**
         * @hidden
         * @returns {Promise<void>|Promise}
         */

    }, {
        key: "wireRemoteStream",
        value: function wireRemoteStream() {
            var _this8 = this;

            return new Promise(function (resolve, reject) {
                if (_this8.peerConnection) {
                    if (typeof _this8.peerConnection.remoteStreams[0] != "undefined") {
                        _this8.peerConnection.wireRemoteStream(true).then(function () {
                            _this8.settings.wiredRemote = true;
                            resolve();
                        }).catch(reject);
                    } else reject(new Error('We have no remote MediaStream for this call yet'));
                } else reject(new Error('We have no PC for this call yet'));
            });
        }
        // TODO: fix if many streams
        /**
         * @hidden
         * @returns {Promise<MediaStream>|Promise}
         */

    }, {
        key: "getRemoteAudioStreams",
        value: function getRemoteAudioStreams() {
            var _this9 = this;

            return new Promise(function (resolve, reject) {
                if (_this9.peerConnection) {
                    _this9.peerConnection.remoteStreams.forEach(function (stream) {
                        if (stream.getAudioTracks().length) {
                            resolve(new MediaStream(stream.getAudioTracks()));
                            return;
                        }
                    });
                    reject(new Error('We have no remote MediaStream for this call yet'));
                } else reject(new Error('We have no PC for this call yet'));
            });
        }
        // TODO: fix if many streams
        /**
         * @hidden
         * @returns {Promise<MediaStream>|Promise}
         */

    }, {
        key: "getRemoteVideoStreams",
        value: function getRemoteVideoStreams() {
            var _this10 = this;

            return new Promise(function (resolve, reject) {
                if (_this10.peerConnection) {
                    if (typeof _this10.peerConnection.remoteStreams[0] != "undefined" && _this10.peerConnection.remoteStreams[0].getVideoTracks().length != 0) resolve(new MediaStream(_this10.peerConnection.remoteStreams[0].getVideoTracks()));else reject(new Error('We have no remote MediaStream for this call yet'));
                } else reject(new Error('We have no PC for this call yet'));
            });
        }
        /**
         * get wired state for remote audio streams
         * @hidden
         * @returns {boolean}
         */

    }, {
        key: "getRemoteWiredState",
        value: function getRemoteWiredState() {
            return this.settings.wiredRemote;
        }
        /**
         * get wired state for local audio streams
         * @hidden
         * @returns {boolean}
         */

    }, {
        key: "getLocalWiredState",
        value: function getLocalWiredState() {
            return this.settings.wiredLocal;
        }
        /**
         * Use specified audio output , use <a href="#audiooutputs">audioOutputs</a> to get the list of available audio output
         * @param {String} id Id of the audio source
         */

    }, {
        key: "useAudioOutput",
        value: function useAudioOutput(id) {
            return this.peerConnection.updateRenderersSink(id);
            // return new Promise<void>((resolve, reject) => {
            //     if (BrowserSpecific.getWSVendor(true) !== "chrome")
            //         reject(new Error("Unsupported browser. Only Google Chrome 49 and above."));
            //     return this.peerConnection.updateRenderersSink(id);
            //
            // })
        }
        /**
         * Returns HTML audio element's id for the audio call
         * @returns string
         */

    }, {
        key: "getAudioElementId",
        value: function getAudioElementId() {
            if (this._peerConnection.remoteStreams.length = 0) return null;
            if (this._peerConnection.remoteStreams[0].getAudioTracks().length = 0) return null;
            return this._peerConnection.remoteStreams[0].getAudioTracks()[0].id;
        }
        /**
         * For testing and debug
         * @hidden
         */

    }, {
        key: "getDirections",
        value: function getDirections() {
            if (typeof this.peerConnection !== "undefined") return this.peerConnection.getDirections();
        }
        /**
         * For testing and debug
         * @hidden
         */

    }, {
        key: "getStreamActivity",
        value: function getStreamActivity() {
            return {};
            // if(typeof this.peerConnection !=="undefined")
            //     return this.peerConnection.getStreamActivity();
        }
        /**
         * @hidden
         */

    }, {
        key: "hdnFRS",
        value: function hdnFRS() {
            this.peerConnection._hdnFRS();
        }
        /**
         * @hidden
         */

    }, {
        key: "hdnFRSPrep",
        value: function hdnFRSPrep() {
            var _this11 = this;

            if (typeof this.peerConnection !== "undefined") this.peerConnection._hdnFRSPrep();else setTimeout(function () {
                _this11.hdnFRSPrep();
            }, 1000);
        }
        /**
         * @hidden
         * @param headers
         * @param sdp
         */

    }, {
        key: "runIncomingReInvite",
        value: function runIncomingReInvite(headers, sdp) {
            var _this12 = this;

            if (this.settings.state === CallState.UPDATING) {
                VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.rejectReInvite, this.settings.id, {});
            } else {
                this.settings.state = CallState.UPDATING;
                var hasVideo = CallManager_1.CallManager.get().isSDPHasVideo(sdp);
                this.peerConnection.handleReinvite(headers, sdp, hasVideo).then(function () {
                    _this12.peerConnection.restoreMute();
                });
            }
        }
        /**
         * @hidden
         * @param state
         */

    }, {
        key: "setActiveForce",
        value: function setActiveForce(state) {
            this.settings.active = state;
        }
        /**
         * @hidden
         * @return {string}
         * @private
         */

    }, {
        key: "_traceName",
        value: function _traceName() {
            return 'Call';
        }
        /**
         * Get the call duration
         * @return the call duration in milliseconds
         */

    }, {
        key: "getCallDuration",
        value: function getCallDuration() {
            return Date.now() - this.startTime;
        }
    }, {
        key: "stateValue",
        get: function get() {
            return this.settings.state;
        }
    }, {
        key: "promise",
        get: function get() {
            return this._promise;
        }
        /**
         * @hidden
         * @param peerConnection
         */

    }, {
        key: "peerConnection",
        set: function set(peerConnection) {
            this._peerConnection = peerConnection;
        }
        /**
         * @hidden
         * @returns {PeerConnection}
         */
        ,
        get: function get() {
            return this._peerConnection;
        }
    }]);

    return Call;
}(EventDispatcher_1.EventDispatcher);

__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "id", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "number", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "displayName", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "headers", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "active", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "state", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "answer", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "decline", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "reject", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "hangup", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "sendTone", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "mutePlayback", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "unmutePlayback", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "restoreRMute", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "muteMicrophone", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "unmuteMicrophone", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "showRemoteVideo", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "setRemoteVideoPosition", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "setRemoteVideoSize", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "sendInfo", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "sendMessage", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "setVideoSettings", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "getVideoElementId", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "addEventListener", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "on", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "removeEventListener", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "off", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "dispatchEvent", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "checkState", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "onConnected", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "onDisconnected", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "onFailed", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "onStopRinging", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "onRingOut", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "onTransferComplete", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "onTransferFailed", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "onInfo", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "setActive", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "checkCallMode", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "canStartSendingCandidates", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "notifyICETimeout", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "sendVideo", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "receiveVideo", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "sendAudio", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "getLocalStream", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "setLocalStream", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "addLocalStream", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "wireRemoteStream", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "getRemoteAudioStreams", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "getRemoteVideoStreams", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "getRemoteWiredState", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], Call.prototype, "getLocalWiredState", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Call.prototype, "useAudioOutput", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Call.prototype, "getAudioElementId", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Call.prototype, "getStreamActivity", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Call.prototype, "hdnFRS", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Call.prototype, "hdnFRSPrep", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Call.prototype, "runIncomingReInvite", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CLIENT)], Call.prototype, "setActiveForce", null);
exports.Call = Call;

/***/ }),
/* 15 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

Object.defineProperty(exports, "__esModule", { value: true });
/**
 * @hidden
 */

var EventDispatcher = function () {
    function EventDispatcher() {
        _classCallCheck(this, EventDispatcher);

        /**
         * @hidden
         * @type {{}}
         */
        this.eventListeners = {};
    }
    /**
     * @hidden
     * @deprecated
     * @param {EventType} event
     * @param {Function} handler
     */


    _createClass(EventDispatcher, [{
        key: "addEventListener",
        value: function addEventListener(event, handler) {
            if (typeof this.eventListeners[event] == 'undefined') this.eventListeners[event] = [];
            this.eventListeners[event].push(handler);
        }
        /**
         * @hidden
         * @param e
         */

    }, {
        key: "dispatchEvent",
        value: function dispatchEvent(e) {
            var event = e.name;
            if (typeof this.eventListeners[event] != 'undefined') {
                for (var i = 0; i < this.eventListeners[event].length; i++) {
                    if (typeof this.eventListeners[event][i] == "function") {
                        this.eventListeners[event][i](e);
                    }
                }
            }
        }
        /**
         * @hidden
         * @deprecated
         * @param {EventType} event
         * @param {Function} handler
         */

    }, {
        key: "removeEventListener",
        value: function removeEventListener(event, handler) {
            if (typeof this.eventListeners[event] == 'undefined') return;
            if (typeof handler === "function") {
                for (var i = 0; i < this.eventListeners[event].length; i++) {
                    if (this.eventListeners[event][i] == handler) {
                        this.eventListeners[event].splice(i, 1);
                        break;
                    }
                }
            } else {
                this.eventListeners[event] = [];
            }
        }
    }, {
        key: "on",
        value: function on(event, handler) {
            this.addEventListener(event, handler);
        }
    }, {
        key: "off",
        value: function off(event, handler) {
            this.removeEventListener(event, handler);
        }
    }]);

    return EventDispatcher;
}();

exports.EventDispatcher = EventDispatcher;

/***/ }),
/* 16 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

Object.defineProperty(exports, "__esModule", { value: true });
var Logger_1 = __webpack_require__(0);
/**
 * Created by irbisadm on 18/10/2016.
 */
/**
 * @hidden
 */
var CallstatsIoFabricUsage;
(function (CallstatsIoFabricUsage) {
    CallstatsIoFabricUsage[CallstatsIoFabricUsage["multiplex"] = "multiplex"] = "multiplex";
    CallstatsIoFabricUsage[CallstatsIoFabricUsage["audio"] = "audio"] = "audio";
    CallstatsIoFabricUsage[CallstatsIoFabricUsage["video"] = "video"] = "video";
    CallstatsIoFabricUsage[CallstatsIoFabricUsage["screen"] = "screen"] = "screen";
    CallstatsIoFabricUsage[CallstatsIoFabricUsage["data"] = "data"] = "data";
    CallstatsIoFabricUsage[CallstatsIoFabricUsage["unbundled"] = "unbundled"] = "unbundled";
})(CallstatsIoFabricUsage = exports.CallstatsIoFabricUsage || (exports.CallstatsIoFabricUsage = {}));
/**
 * @hidden
 */
var CallstatsIoFabricEvent;
(function (CallstatsIoFabricEvent) {
    CallstatsIoFabricEvent[CallstatsIoFabricEvent["fabricHold"] = "fabricHold"] = "fabricHold";
    CallstatsIoFabricEvent[CallstatsIoFabricEvent["fabricResume"] = "fabricResume"] = "fabricResume";
    CallstatsIoFabricEvent[CallstatsIoFabricEvent["audioMute"] = "audioMute"] = "audioMute";
    CallstatsIoFabricEvent[CallstatsIoFabricEvent["audioUnmute"] = "audioUnmute"] = "audioUnmute";
    CallstatsIoFabricEvent[CallstatsIoFabricEvent["videoPause"] = "videoPause"] = "videoPause";
    CallstatsIoFabricEvent[CallstatsIoFabricEvent["videoResume"] = "videoResume"] = "videoResume";
    CallstatsIoFabricEvent[CallstatsIoFabricEvent["fabricTerminated"] = "fabricTerminated"] = "fabricTerminated";
    CallstatsIoFabricEvent[CallstatsIoFabricEvent["screenShareStart"] = "screenShareStart"] = "screenShareStart";
    CallstatsIoFabricEvent[CallstatsIoFabricEvent["screenShareStop"] = "screenShareStop"] = "screenShareStop";
    CallstatsIoFabricEvent[CallstatsIoFabricEvent["dominantSpeaker"] = "dominantSpeaker"] = "dominantSpeaker";
    CallstatsIoFabricEvent[CallstatsIoFabricEvent["activeDeviceList"] = "activeDeviceList"] = "activeDeviceList";
})(CallstatsIoFabricEvent = exports.CallstatsIoFabricEvent || (exports.CallstatsIoFabricEvent = {}));
/**
 * @hidden
 */
var CallstatsioWrtcFuncNames;
(function (CallstatsioWrtcFuncNames) {
    CallstatsioWrtcFuncNames[CallstatsioWrtcFuncNames["getUserMedia"] = "getUserMedia"] = "getUserMedia";
    CallstatsioWrtcFuncNames[CallstatsioWrtcFuncNames["createOffer"] = "createOffer"] = "createOffer";
    CallstatsioWrtcFuncNames[CallstatsioWrtcFuncNames["createAnswer"] = "createAnswer"] = "createAnswer";
    CallstatsioWrtcFuncNames[CallstatsioWrtcFuncNames["setLocalDescription"] = "setLocalDescription"] = "setLocalDescription";
    CallstatsioWrtcFuncNames[CallstatsioWrtcFuncNames["setRemoteDescription"] = "setRemoteDescription"] = "setRemoteDescription";
    CallstatsioWrtcFuncNames[CallstatsioWrtcFuncNames["addIceCandidate"] = "addIceCandidate"] = "addIceCandidate";
    CallstatsioWrtcFuncNames[CallstatsioWrtcFuncNames["iceConnectionFailure"] = "iceConnectionFailure"] = "iceConnectionFailure";
    CallstatsioWrtcFuncNames[CallstatsioWrtcFuncNames["signalingError"] = "signalingError"] = "signalingError";
    CallstatsioWrtcFuncNames[CallstatsioWrtcFuncNames["applicationLog"] = "applicationLog"] = "applicationLog";
})(CallstatsioWrtcFuncNames = exports.CallstatsioWrtcFuncNames || (exports.CallstatsioWrtcFuncNames = {}));
/**
 * @hidden
 */

var CallstatsIo = function () {
    function CallstatsIo(params) {
        _classCallCheck(this, CallstatsIo);

        this._params = params;
        this.inited = false;
        this.pendingFabric = [];
        var x_window = window;
        if (typeof x_window.callstats != "undefined") this.callstats = new x_window.callstats(null, x_window.io);
    }

    _createClass(CallstatsIo, [{
        key: "init",
        value: function init(userId) {
            var _this = this;

            if (!CallstatsIo.moduleEnabled) return false;
            Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.UTILS, "[CallstatsIo]", Logger_1.LogLevel.INFO, " Callstats.io SDK initialization start");
            this.callstats.initialize(this._params.AppID, this._params.AppSecret, userId, function () {
                Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.UTILS, "[CallstatsIo]", Logger_1.LogLevel.INFO, " Callstats.io SDK initialization successful");
                _this.inited = true;
                _this.pendingFabric.map(function (item) {
                    _this.callstats.addNewFabric(item.pc, item.remoteUser, item.fabricUsage, item.callID);
                });
            }, function () {}, this.packParams());
            return true;
        }
    }, {
        key: "packParams",
        value: function packParams() {
            var ax = {};
            if (this._params.disableBeforeUnloadHandler) ax['disableBeforeUnloadHandler'] = this._params.disableBeforeUnloadHandler;
            if (this._params.applicationVersion) ax['applicationVersion'] = this._params.applicationVersion;
            return ax;
        }
    }, {
        key: "addNewFabric",
        value: function addNewFabric(pc, remoteUser, fabricUsage, callID) {
            if (!CallstatsIo.moduleEnabled) return false;
            if (this.inited) {
                Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.UTILS, "[CallstatsIo]", Logger_1.LogLevel.INFO, " Callstats.io addNewFabric");
                this.callstats.addNewFabric(pc, remoteUser, fabricUsage, callID);
            } else {
                this.pendingFabric.push({ pc: pc, remoteUser: remoteUser, fabricUsage: fabricUsage, callID: callID });
            }
        }
    }, {
        key: "sendFabricEvent",
        value: function sendFabricEvent(pc, fabricEvent, callID) {
            if (!CallstatsIo.moduleEnabled) return false;
            this.callstats.sendFabricEvent(pc, fabricEvent, callID);
        }
    }, {
        key: "reportError",
        value: function reportError(pc, callID, wrtcFuncName, domError, localSDP, remoteSDP) {
            if (!CallstatsIo.moduleEnabled) return false;
            this.callstats.reportError(pc, callID, wrtcFuncName, domError, localSDP, remoteSDP);
        }
        /**
         * @hidden
         * @return {string}
         * @private
         */

    }, {
        key: "_traceName",
        value: function _traceName() {
            return 'CallstatsIo';
        }
    }], [{
        key: "isModuleEnabled",
        value: function isModuleEnabled() {
            return CallstatsIo.moduleEnabled;
        }
    }, {
        key: "get",
        value: function get(params) {
            var x_window = window;
            if (typeof x_window.callstats != "undefined") CallstatsIo.moduleEnabled = true;
            if (typeof CallstatsIo.instance == "undefined") {
                CallstatsIo.instance = new CallstatsIo(params);
            }
            if (typeof params != "undefined") {
                CallstatsIo.instance._params = params;
            }
            return CallstatsIo.instance;
        }
    }]);

    return CallstatsIo;
}();

CallstatsIo.moduleEnabled = false;
exports.CallstatsIo = CallstatsIo;

/***/ }),
/* 17 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

Object.defineProperty(exports, "__esModule", { value: true });
var VoxSignaling_1 = __webpack_require__(1);
var RemoteFunction_1 = __webpack_require__(2);
/**
 * @hidden
 */

var SignalingDTMFSender = function () {
    function SignalingDTMFSender(_id) {
        _classCallCheck(this, SignalingDTMFSender);

        this._id = _id;
    }

    _createClass(SignalingDTMFSender, [{
        key: "insertDTMF",
        value: function insertDTMF(tones, duration, interToneGap) {
            var _this = this;

            tones.split('').forEach(function (key) {
                return _this.sendKey(key);
            });
        }
    }, {
        key: "sendKey",
        value: function sendKey(key) {
            var k = void 0;
            if (key == '*') k = 10;else if (key == '#') k = 11;else {
                k = parseInt(key);
            }
            if (k >= 0 || k <= 11) VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.sendDTMF, this._id, k);
        }
        /**
         * @hidden
         * @return {string}
         * @private
         */

    }, {
        key: "_traceName",
        value: function _traceName() {
            return 'SignalingDTMFSender';
        }
    }]);

    return SignalingDTMFSender;
}();

exports.SignalingDTMFSender = SignalingDTMFSender;

/***/ }),
/* 18 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", { value: true });
/**
 * Events dispatched by <a href="../classes/client.html">VoxImplant.Client</a> instance. See <a href="../globals.html#getinstance">VoxImplant.getInstance</a>.
 */
var Events;
(function (Events) {
  /**
   *    Event dispatched after SDK was successfully initialized after init function call
   *
   *    <p>Handler function receives <a href="../interfaces/eventhandlers.sdkready.html">EventHandlers.SDKReady</a> object as an argument.</p>
   */
  Events[Events["SDKReady"] = "SDKReady"] = "SDKReady";
  /**
   *    Event dispatched after connection to VoxImplant Cloud was established successfully.
   *    <br>
   *    See <a href="../classes/client.html#connect">connect</a> function
   *
   *    <p>Handler function receives no arguments.</p>
   */
  Events[Events["ConnectionEstablished"] = "ConnectionEstablished"] = "ConnectionEstablished";
  /**
   *    Event dispatched if connection to VoxImplant Cloud couldn't be established.
   *    <br>
   *    See <a href="../classes/client.html#connect">connect</a> function
   *
   *    <p>Handler function receives <a href="../interfaces/eventhandlers.connectionfailed.html">EventHandlers.ConnectionFailed</a> object as an argument.</p>
   */
  Events[Events["ConnectionFailed"] = "ConnectionFailed"] = "ConnectionFailed";
  /**
   *    Event dispatched if connection to VoxImplant Cloud was closed because of network problems.
   *    <br>
   *    See <a href="../classes/client.html#connect">connect</a> function
   *
   *    <p>Handler function receives no arguments.</p>
   */
  Events[Events["ConnectionClosed"] = "ConnectionClosed"] = "ConnectionClosed";
  /**
   *    Event dispatched after
   *    <ul>
   *      <li><a href="../classes/client.html#login">login</a></li>
   *      <li><a href="../classes/client.html#loginwithonetimekey">loginWithOneTimeKey</a></li>
   *      <li><a href="../classes/client.html#requestonetimeloginkey">requestOneTimeLoginKey</a></li>
   *      <li><a href="../classes/client.html#loginwithcode">loginWithCode</a></li>
   *    </ul>
   *    function call
   *
   *    <p>Handler function receives <a href="../interfaces/eventhandlers.authresult.html">EventHandlers.AuthResult</a> object as an argument.</p>
   */
  Events[Events["AuthResult"] = "AuthResult"] = "AuthResult";
  /**
   *   Refresh tokens by LoginTokens.refreshToken
   *
   *   <p>Handler function receives <a href="../interfaces/eventhandlers.authtokenresult.html">EventHandlers.AuthTokenResult</a> object as an argument.</p>
   */
  Events[Events["RefreshTokenResult"] = "RefreshTokenResult"] = "RefreshTokenResult";
  /**
   *    Event dispatched after sound playback was stopped.
   *    <br>
   *    See <a href="../classes/client.html#playtonescript">playToneScript</a>
   *    and <a href="../classes/client.html#stopplayback">stopPlayback</a> functions
   *
   *    <p>Handler function receives no arguments.</p>
   */
  Events[Events["PlaybackFinished"] = "PlaybackFinished"] = "PlaybackFinished";
  /**
   *    Event dispatched after user interaction with the mic access dialog.
   *
   *    <p>Handler function receives <a href="../interfaces/eventhandlers.micaccessresult.html">EventHandlers.MicAccessResult</a> object as an argument.</p>
   */
  Events[Events["MicAccessResult"] = "MicAccessResult"] = "MicAccessResult";
  /**
   *    Event dispatched when there is a new incoming call to current user
   *
   *    <p>Handler function receives <a href="../interfaces/eventhandlers.incomingcall.html">EventHandlers.IncomingCall</a> object as an argument.</p>
   */
  Events[Events["IncomingCall"] = "IncomingCall"] = "IncomingCall";
  /**
   *    Event dispatched when audio and video sources information was updated.
   *    <br>
   *    See audioSources and videoSources for details
   *
   *    <p>Handler function receives no arguments.</p>
   */
  Events[Events["SourcesInfoUpdated"] = "SourcesInfoUpdated"] = "SourcesInfoUpdated";
  /**
   *    Event dispatched when packet loss data received from VoxImplant servers
   *
   *    <p>Handler function receives <a href="../interfaces/eventhandlers.netstatsreceived.html">EventHandlers.NetStatsReceived</a> object as an argument.</p>
   */
  Events[Events["NetStatsReceived"] = "NetStatsReceived"] = "NetStatsReceived";
})(Events = exports.Events || (exports.Events = {}));

/***/ }),
/* 19 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var __decorate = undefined && undefined.__decorate || function (decorators, target, key, desc) {
    var c = arguments.length,
        r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc,
        d;
    if ((typeof Reflect === "undefined" ? "undefined" : _typeof(Reflect)) === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);else for (var i = decorators.length - 1; i >= 0; i--) {
        if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    }return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var Logger_1 = __webpack_require__(0);
var VoxSignaling_1 = __webpack_require__(1);
var Renderer_1 = __webpack_require__(20);
var CallManager_1 = __webpack_require__(6);
var Constants_1 = __webpack_require__(11);
var RemoteFunction_1 = __webpack_require__(2);
var ReInviteQ_1 = __webpack_require__(23);
var BrowserSpecific_1 = __webpack_require__(5);
var UserMediaManager_1 = __webpack_require__(4);
var Client_1 = __webpack_require__(3);
var index_1 = __webpack_require__(13);
/**
 * @hidden
 */
var PeerConnectionState;
(function (PeerConnectionState) {
    PeerConnectionState[PeerConnectionState["IDLE"] = 0] = "IDLE";
    PeerConnectionState[PeerConnectionState["REMOTEOFFER"] = 1] = "REMOTEOFFER";
    PeerConnectionState[PeerConnectionState["LOCALOFFER"] = 2] = "LOCALOFFER";
    PeerConnectionState[PeerConnectionState["ESTABLISHING"] = 3] = "ESTABLISHING";
    PeerConnectionState[PeerConnectionState["ESTABLISHED"] = 4] = "ESTABLISHED";
    PeerConnectionState[PeerConnectionState["CLOSED"] = 5] = "CLOSED";
})(PeerConnectionState = exports.PeerConnectionState || (exports.PeerConnectionState = {}));
/**
 * @hidden
 */
var PeerConnectionMode;
(function (PeerConnectionMode) {
    PeerConnectionMode[PeerConnectionMode["CLIENT_SERVER_V1"] = 0] = "CLIENT_SERVER_V1";
    PeerConnectionMode[PeerConnectionMode["P2P"] = 1] = "P2P";
})(PeerConnectionMode = exports.PeerConnectionMode || (exports.PeerConnectionMode = {}));
/**
 * Peer connection wrapper. Will have implementations for WebRTC/ORTC
 * @hidden
 */

var PeerConnection = function () {
    function PeerConnection(id, mode, videoEnabled) {
        _classCallCheck(this, PeerConnection);

        this.id = id;
        this.mode = mode;
        this.videoEnabled = videoEnabled;
        this.SEND_CANDIDATE_DELAY = 1000;
        this.mediaRepository = [];
        this.candidateList = [];
        this.localCandidateTimer = -1;
        /**
         * @hidden
         * @param state
         */
        this.onHold = false;
        this.muteMicState = false;
        this.log = Logger_1.LogManager.get().createLogger(Logger_1.LogCategory.RTC, "PeerConnection " + id);
        this.state = PeerConnectionState.IDLE;
        this.log.info("Created PC");
        this.renderers = [];
        this.pendingCandidates = [];
        if (id !== "_default" && CallManager_1.CallManager.get().calls[id]) this.reInviteQ = new ReInviteQ_1.ReInviteQ(CallManager_1.CallManager.get().calls[id], this._canReInvite);
    }

    _createClass(PeerConnection, [{
        key: "getId",
        value: function getId() {
            return this.id;
        }
    }, {
        key: "setState",
        value: function setState(st) {
            this.log.info("Transmitting from " + PeerConnectionState[this.state] + " to " + PeerConnectionState[st]);
            this.state = st;
        }
    }, {
        key: "getState",
        value: function getState() {
            return this.state;
        }
    }, {
        key: "processRemoteAnswer",
        value: function processRemoteAnswer(headers, sdp) {
            // if (this.state == PeerConnectionState.ESTABLISHING) {
            this.log.info("Called processRemoteAnswer");
            this.state = PeerConnectionState.ESTABLISHING;
            return this._processRemoteAnswer(headers, sdp);
            // } else {
            //     this.log.error("Called processRemoteAnswer in state " + PeerConnectionState[this.state]);
            // }
        }
    }, {
        key: "getLocalOffer",
        value: function getLocalOffer() {
            if (this.state === PeerConnectionState.IDLE || this.state === PeerConnectionState.ESTABLISHED || PeerConnectionState.LOCALOFFER) {
                this.log.info("Called getLocalOffer");
                this.state = PeerConnectionState.LOCALOFFER;
                return this._getLocalOffer();
            } else {
                this.log.error("Called getLocalOffer in state " + PeerConnectionState[this.state]);
                return new Promise(function (resolve, reject) {
                    reject("Invalid state");
                });
            }
        }
    }, {
        key: "getLocalAnswer",
        value: function getLocalAnswer() {
            return this._getLocalAnswer();
        }
    }, {
        key: "processRemoteOffer",
        value: function processRemoteOffer(sdp) {
            if (this.state === PeerConnectionState.IDLE || this.state === PeerConnectionState.ESTABLISHED) {
                this.log.info("Called processRemoteOffer");
                this.state = PeerConnectionState.ESTABLISHING;
                return this._processRemoteOffer(sdp);
            } else {
                this.log.error("Called processRemoteOffer in state " + PeerConnectionState[this.state]);
                return new Promise(function (resolve, reject) {
                    reject("Invalid state");
                });
            }
        }
    }, {
        key: "close",
        value: function close() {
            this.log.info("Called close");
            this._stopSharing();
            this._close();
            this.renderers.forEach(function (renderer) {
                return Renderer_1.Renderer.get().releaseElement(renderer);
            });
            this.mediaRepository.forEach(function (track) {
                track.stop();
            });
            this.mediaRepository = [];
        }
    }, {
        key: "addRemoteCandidate",
        value: function addRemoteCandidate(candidate, mLineIndex) {
            this.log.info("Called addRemoteCandidate");
            return this._addRemoteCandidate(candidate, mLineIndex);
        }
    }, {
        key: "sendLocalCandidateToPeer",
        value: function sendLocalCandidateToPeer(cand, mLineIndex) {
            var _this = this;

            this._call = CallManager_1.CallManager.get().calls[this.id];
            switch (this.mode) {
                case PeerConnectionMode.P2P:
                    this.candidateList.push([mLineIndex, cand]);
                    if (this.localCandidateTimer <= 0) {
                        this.localCandidateTimer = window.setTimeout(function () {
                            window.clearTimeout(_this.localCandidateTimer);
                            _this.localCandidateTimer = -1;
                            if (CallManager_1.CallManager.get().calls[_this.id]) CallManager_1.CallManager.get().calls[_this.id].sendInfo(Constants_1.Constants.P2P_SPD_FRAG_MIME_TYPE, JSON.stringify(_this.candidateList), {});
                            _this.candidateList = [];
                        }, 200);
                    }
                    break;
                case PeerConnectionMode.CLIENT_SERVER_V1:
                    VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.addCandidate, this.id, cand, mLineIndex);
                    break;
            }
        }
    }, {
        key: "bindLocalMedia",
        value: function bindLocalMedia(localStream) {
            var _this2 = this;

            if (typeof this._localStream !== "undefined") this._unbindLocalMegia();
            if (typeof localStream !== "undefined" && localStream !== null) {
                var newMs = new MediaStream(localStream.getTracks().map(function (item) {
                    var track = item;
                    if (typeof item.clone !== "undefined") track = item.clone();
                    _this2.mediaRepository.push(track);
                    return track;
                }));
                this._localStream = newMs;
                if (this._call) {
                    if (newMs.getAudioTracks().length > 0) this._call.settings.audioDirections.sendAudio = true;
                }
                this._bindLocalMedia();
            }
        }
    }, {
        key: "handleReinvite",
        value: function handleReinvite(headers, sdp, hasVideo) {
            return this._handleReinvite(headers, sdp, hasVideo);
        }
    }, {
        key: "addCandidateToSend",
        value: function addCandidateToSend(attrString, mLineIndex) {
            this.pendingCandidates.push([mLineIndex, attrString]);
            if (this.canSendCandidates) this.startCandidateSendTimer();
        }
    }, {
        key: "startCandidateSendTimer",
        value: function startCandidateSendTimer() {
            var _this3 = this;

            if (this.candidateSendTimer === null || typeof this.candidateSendTimer === "undefined") {
                this.candidateSendTimer = setTimeout(function () {
                    _this3.candidateSendTimer = null;
                    if (_this3.pendingCandidates.length > 0) {
                        if (CallManager_1.CallManager.get().calls[_this3.id]) CallManager_1.CallManager.get().calls[_this3.id].sendInfo(Constants_1.Constants.P2P_SPD_FRAG_MIME_TYPE, JSON.stringify(_this3.pendingCandidates), {});
                    }
                    _this3.pendingCandidates = [];
                }, this.SEND_CANDIDATE_DELAY);
            }
        }
    }, {
        key: "canStartSendingCandidates",
        value: function canStartSendingCandidates() {
            this.canSendCandidates = true;
            this.startCandidateSendTimer();
        }
    }, {
        key: "sendDTMF",
        value: function sendDTMF(key) {
            // const duration = 3000;
            var duration = 500;
            var gap = 50;
            this._sendDTMF(key, duration, gap);
        }
    }, {
        key: "setVideoEnabled",
        value: function setVideoEnabled(newVal) {
            var oldvalRecieve = this.videoEnabled.receiveVideo;
            this.videoEnabled = newVal;
            if (oldvalRecieve != newVal.receiveVideo) {
                this._hold(this.onHold);
            }
        }
    }, {
        key: "setVideoFlags",
        value: function setVideoFlags(newFlags) {
            this.videoEnabled = newFlags;
        }
    }, {
        key: "updateRenderersSink",
        value: function updateRenderersSink(sinkId) {
            var _this4 = this;

            return new Promise(function (resolve, reject) {
                if (BrowserSpecific_1.default.getWSVendor(true) !== "chrome") {
                    reject(new Error("Unsupported browser. Only Google Chrome 49 and above."));
                    return;
                }
                _this4.sinkId = sinkId;
                var renderers = [];
                var _iteratorNormalCompletion = true;
                var _didIteratorError = false;
                var _iteratorError = undefined;

                try {
                    for (var _iterator = _this4.renderers[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                        var item = _step.value;

                        renderers.push(item.setSinkId(sinkId));
                    }
                } catch (err) {
                    _didIteratorError = true;
                    _iteratorError = err;
                } finally {
                    try {
                        if (!_iteratorNormalCompletion && _iterator.return) {
                            _iterator.return();
                        }
                    } finally {
                        if (_didIteratorError) {
                            throw _iteratorError;
                        }
                    }
                }

                Promise.all(renderers).then(function () {
                    resolve();
                }, reject);
            });
        }
        /**
         * Get sdp audio/video directions from sdp
         * @hidden
         */

    }, {
        key: "getDirections",
        value: function getDirections() {
            return this._getDirections();
        }
        /**
         * @hidden
         * @param state
         */

    }, {
        key: "setHoldKey",
        value: function setHoldKey(state) {
            this.onHold = state;
        }
    }, {
        key: "getTrackKind",
        value: function getTrackKind() {
            return this._getTrackKind();
        }
    }, {
        key: "shareScreen",
        value: function shareScreen(showLocalView) {
            var _this5 = this;

            if (BrowserSpecific_1.default.isScreenSharingSupported) {
                return new Promise(function (_resolve, reject) {
                    if (_this5.onHold) {
                        reject({ result: false, call: _this5._call });
                        return;
                    }
                    if (_this5.onHold) reject();
                    _this5.reInviteQ.add({
                        fx: function fx() {
                            BrowserSpecific_1.default.getScreenMedia().then(function (media) {
                                _this5._addMediaStream(media, showLocalView);
                                media.getTracks().forEach(function (track) {
                                    return _this5.mediaRepository.push(track);
                                });
                            }).catch(reject);
                        }, reject: reject, resolve: function resolve(e) {
                            _this5.restoreMute();_resolve(e);
                        }
                    });
                });
            } else {
                return new Promise(function (resolve, reject) {
                    if (_this5.onHold) {
                        reject({ result: false, call: _this5._call });
                        return;
                    }
                    Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.CALL, 'SCREEN SHARING', Logger_1.LogLevel.WARNING, "Sorry, this browser does not support screen sharing.");
                    reject(new Error("Sorry, this browser does not support screen sharing."));
                });
            }
        }
    }, {
        key: "sendMedia",
        value: function sendMedia(audio, video) {
            var _this6 = this;

            return new Promise(function (_resolve2, reject) {
                if (_this6.onHold) {
                    reject({ result: false, call: _this6._call });
                    return;
                }
                _this6.reInviteQ.add({ fx: function fx() {
                        var appConfig = Client_1.Client.getInstance().config();
                        if (appConfig.experiments && appConfig.experiments.hardware) {
                            index_1.default.StreamManager.get()._updateCallStream(_this6._call).then(function (stream) {
                                _this6.bindLocalMedia(stream);
                            });
                        } else {
                            var constraints = UserMediaManager_1.UserMediaManager.get().getConstrainWithSendFlag(audio, video);
                            _this6.setVideoEnabled({ receiveVideo: _this6.videoEnabled.receiveVideo, sendVideo: video });
                            UserMediaManager_1.UserMediaManager.get().getQueryMediaSilent(constraints).then(function (stream) {
                                _this6.bindLocalMedia(stream);
                            });
                        }
                    }, reject: reject, resolve: function resolve(e) {
                        _this6.restoreMute();_resolve2(e);
                    } });
            });
        }
    }, {
        key: "stopSharingScreen",
        value: function stopSharingScreen() {
            var _this7 = this;

            if (BrowserSpecific_1.default.isScreenSharingSupported) {
                return new Promise(function (_resolve3, reject) {
                    if (_this7.onHold) {
                        reject({ result: false, call: _this7._call });
                        return;
                    }
                    if (_this7.onHold) reject();
                    _this7.reInviteQ.add({
                        fx: function fx() {
                            _this7._stopSharing();
                        }, reject: reject, resolve: function resolve(e) {
                            _this7.restoreMute();_resolve3(e);
                        }
                    });
                });
            } else return new Promise(function (resolve, reject) {
                if (_this7.onHold) {
                    reject({ result: false, call: _this7._call });
                    return;
                }
                Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.CALL, 'SCREEN SHARING', Logger_1.LogLevel.WARNING, "Sorry, this browser does not support screen sharing.");
                reject(new Error("Sorry, this browser does not support screen sharing."));
            });
        }
        /**
        * Hold/Unhold action for protocol v3 (Fully implement RFC 4566
        * @param newState
        */

    }, {
        key: "hold",
        value: function hold(newState) {
            var _this8 = this;

            return new Promise(function (_resolve4, reject) {
                _this8.reInviteQ.add({ fx: function fx() {
                        _this8._hold(newState);
                    }, reject: reject, resolve: function resolve(e) {
                        _this8.restoreMute();_resolve4(e);
                    } });
            });
        }
    }, {
        key: "hdnFRS",
        value: function hdnFRS() {
            var _this9 = this;

            return new Promise(function (_resolve5, reject) {
                if (_this9.onHold) {
                    reject({ result: false, call: _this9._call });
                    return;
                }
                _this9.reInviteQ.add({ fx: function fx() {
                        _this9._hdnFRS();
                    }, reject: reject, resolve: function resolve(e) {
                        _this9.restoreMute();_resolve5(e);
                    } });
            });
        }
    }, {
        key: "addMediaStream",
        value: function addMediaStream(stream, showLocalView) {
            var _this10 = this;

            return new Promise(function (_resolve6, reject) {
                if (_this10.onHold) {
                    reject({ result: false, call: _this10._call });
                    return;
                }
                _this10.reInviteQ.add({ fx: function fx() {
                        _this10._addMediaStream(stream, showLocalView);
                    }, reject: reject, resolve: function resolve(e) {
                        _this10.restoreMute();_resolve6(e);
                    } });
            });
        }
    }, {
        key: "muteMicrophone",
        value: function muteMicrophone(newState) {
            var _this11 = this;

            if (this.muteMicState === newState) return;
            this.muteMicState = newState;
            this.localStream.getAudioTracks().forEach(function (track) {
                track.enabled = !_this11.muteMicState;
            });
        }
    }, {
        key: "restoreMute",
        value: function restoreMute() {
            var _this12 = this;

            if (this._call.settings.active) {
                var that = this;
                setTimeout(function () {
                    _this12._call.restoreRMute();
                    if (_this12.localStream) _this12.localStream.getAudioTracks().forEach(function (track) {
                        track.enabled = !that.muteMicState;
                    });
                }, 300);
            }
        }
        /**
         * @hidden
         * @return {string}
         * @private
         */

    }, {
        key: "_traceName",
        value: function _traceName() {
            return 'PeerConnection';
        }
    }, {
        key: "localStream",
        get: function get() {
            return this._localStream;
        }
    }, {
        key: "remoteStreams",
        get: function get() {
            return this._remoteStreams;
        }
    }, {
        key: "videoRenderer",
        get: function get() {
            var result = null;
            this.renderers.forEach(function (item) {
                if (item.tagName.toLowerCase() === "video") result = item;
            });
            return result;
        }
    }, {
        key: "audioRenderer",
        get: function get() {
            this.renderers.forEach(function (item) {
                if (item.tagName.toLowerCase() === "audio") return item;
            });
            return null;
        }
    }]);

    return PeerConnection;
}();

__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "setState", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "getState", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "processRemoteAnswer", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "getLocalOffer", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "getLocalAnswer", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "processRemoteOffer", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "close", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "addRemoteCandidate", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "sendLocalCandidateToPeer", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "bindLocalMedia", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "handleReinvite", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "addCandidateToSend", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "startCandidateSendTimer", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "canStartSendingCandidates", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "sendDTMF", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "setVideoEnabled", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "setVideoFlags", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "updateRenderersSink", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "setHoldKey", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "getTrackKind", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "shareScreen", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "sendMedia", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "stopSharingScreen", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "hold", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "hdnFRS", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "addMediaStream", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "muteMicrophone", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], PeerConnection.prototype, "restoreMute", null);
exports.PeerConnection = PeerConnection;

/***/ }),
/* 20 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var __decorate = undefined && undefined.__decorate || function (decorators, target, key, desc) {
    var c = arguments.length,
        r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc,
        d;
    if ((typeof Reflect === "undefined" ? "undefined" : _typeof(Reflect)) === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);else for (var i = decorators.length - 1; i >= 0; i--) {
        if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    }return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var BrowserSpecific_1 = __webpack_require__(5);
var Logger_1 = __webpack_require__(0);
/**
 * Singleton that provides audio/video rendering
 * Reuses audio/video elements
 * @hidden
 */

var Renderer = function () {
    function Renderer() {
        _classCallCheck(this, Renderer);

        this.videoElements = new Array();
        this.audioElements = new Array();
        //this.usedElements = {};     
        this.log = Logger_1.LogManager.get().createLogger(Logger_1.LogCategory.RTC, "Renderer");
    }

    _createClass(Renderer, [{
        key: "getElement",

        /**
         * Get new renderer element and put it into specified container
         */
        value: function getElement(id, video, container) {
            var containerToUse = container || this._defaultContainer || document.body;
            if (document.getElementById(id)) {
                // this.log.info("Element with id " + id + " already exists");
                var exist = document.getElementById(id);
                if (containerToUse) exist.parentElement.removeChild(exist);
            }
            var elementToUse = void 0;
            elementToUse = document.createElement(video ? "video" : "audio");
            elementToUse.autoplay = true;
            elementToUse.setAttribute('playsinline', null);
            if (video) {
                this.videoElements.push(elementToUse);
                elementToUse.width = 400;
                elementToUse.height = 300;
            } else {
                this.audioElements.push(elementToUse);
            }
            elementToUse.id = id;
            if (containerToUse) {
                containerToUse.appendChild(elementToUse);
            }
            return elementToUse;
        }
    }, {
        key: "getElementId",
        value: function getElementId(id, video) {}
        /**
         * Get new renderer element and attach it to specified media stream.
         */

    }, {
        key: "renderStream",
        value: function renderStream(stream, container) {
            var elementToUse = this.getElement(stream.id, stream.getVideoTracks().length > 0, container);
            BrowserSpecific_1.default.attachMedia(stream, elementToUse);
            return elementToUse;
        }
    }, {
        key: "setPlaybackVolume",
        value: function setPlaybackVolume(vol) {
            for (var i in this.audioElements) {
                if (this.audioElements.hasOwnProperty(i)) {
                    this.audioElements[i].volume = vol;
                }
            }
            for (var _i in this.videoElements) {
                if (this.videoElements.hasOwnProperty(_i)) {
                    this.videoElements[_i].volume = vol;
                }
            }
        }
        /**
         * Remove renderer element from parent and detach it from media stream
         *  */

    }, {
        key: "releaseElement",
        value: function releaseElement(el) {
            if (el) {
                el.id = "";
                if (typeof el.parentElement != "undefined" && el.parentElement !== null) {
                    if (el.parentElement) el.parentElement.removeChild(el);
                } else if (el.parentNode) {
                    if (el.parentNode) el.parentNode.removeChild(el);
                }
                BrowserSpecific_1.default.detachMedia(el);
            }
        }
        /**
         * @hidden
         * @return {string}
         * @private
         */

    }, {
        key: "_traceName",
        value: function _traceName() {
            return 'Renderer';
        }
    }, {
        key: "defaultContainer",
        get: function get() {
            return this._defaultContainer;
        },
        set: function set(d) {
            this._defaultContainer = d;
        }
    }], [{
        key: "get",
        value: function get() {
            if (!this.inst) this.inst = new Renderer();
            return this.inst;
        }
    }]);

    return Renderer;
}();

__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], Renderer.prototype, "getElement", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], Renderer.prototype, "getElementId", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], Renderer.prototype, "renderStream", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], Renderer.prototype, "setPlaybackVolume", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.USERMEDIA)], Renderer.prototype, "releaseElement", null);
exports.Renderer = Renderer;

/***/ }),
/* 21 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var __decorate = undefined && undefined.__decorate || function (decorators, target, key, desc) {
    var c = arguments.length,
        r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc,
        d;
    if ((typeof Reflect === "undefined" ? "undefined" : _typeof(Reflect)) === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);else for (var i = decorators.length - 1; i >= 0; i--) {
        if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    }return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var Logger_1 = __webpack_require__(0);
var Client_1 = __webpack_require__(3);
var Authenticator_1 = __webpack_require__(10);
var UserMediaManager_1 = __webpack_require__(4);
/**
 * @hidden
 */

var Utils = function () {
    function Utils() {
        _classCallCheck(this, Utils);
    }

    _createClass(Utils, [{
        key: "_traceName",

        /**
         * @hidden
         * @return {string}
         * @private
         */
        value: function _traceName() {
            return 'Logger';
        }
    }], [{
        key: "extend",

        /**
         * @param objects Objects for merging
         * @hidden
         * @returns {Object}
         */
        value: function extend() {
            for (var _len = arguments.length, objects = Array(_len), _key = 0; _key < _len; _key++) {
                objects[_key] = arguments[_key];
            }

            var extended = {};
            var merge = function merge(obj) {
                for (var prop in obj) {
                    if (Object.prototype.hasOwnProperty.call(obj, prop)) {
                        extended[prop] = obj[prop];
                    }
                }
            };
            merge(arguments[0]);
            for (var i = 1; i < arguments.length; i++) {
                var obj = arguments[i];
                merge(obj);
            }
            return extended;
        }
        /**
         * Convert <tt>headersObj</tt> to string
         * @param {Object} headersObj Object contains headers (as properties) to stringify
         * @returns {String}
         * @hidden
         */

    }, {
        key: "stringifyExtraHeaders",
        value: function stringifyExtraHeaders(headersObj) {
            if (Object.prototype.toString.call(headersObj) == '[object Object]') headersObj = JSON.stringify(headersObj);else headersObj = null;
            return headersObj;
        }
        /**
         * Parse cadence sections
         * @param {String} script
         * @retruns {Object}
         * @hidden
         */

    }, {
        key: "cadScript",
        value: function cadScript(script) {
            var cads = script.split(';');
            return cads.map(function (cad) {
                if (cad.length === 0) {
                    return;
                }
                var matchParens = cad.match(/\([0-9\/\.,\*\+]*\)$/),
                    ringLength = cad.substring(0, matchParens.index),
                    segments = matchParens.pop();
                if (matchParens.length) {
                    throw new Error('cadence script should be of the form `%f(%f/%f[,%f/%f])`');
                }
                ringLength = ringLength === '*' ? Infinity : parseFloat(ringLength);
                if (isNaN(ringLength)) {
                    throw new Error('cadence length should be of the form `%f`');
                }
                segments = segments.slice(1, segments.length - 1).split(',').map(function (segment) {
                    try {
                        var onOff = segment.split('/');
                        if (onOff.length > 3) {
                            throw new Error();
                        }
                        onOff = onOff.map(function (string, i) {
                            if (i === 2) {
                                // Special rules for frequencies
                                var freqs = string.split('+').map(function (f) {
                                    var integer = parseInt(f, 10);
                                    if (isNaN(integer)) {
                                        throw new Error();
                                    }
                                    return integer - 1;
                                });
                                return freqs;
                            }
                            var flt;
                            // Special rules for Infinity;
                            if (string == '*') {
                                flt = Infinity;
                            }
                            flt = flt ? flt : parseFloat(string);
                            if (isNaN(flt)) {
                                throw new Error();
                            }
                            return flt;
                        });
                        return {
                            on: onOff[0],
                            off: onOff[1],
                            // frequency is an extension for full toneScript.
                            frequencies: onOff[2]
                        };
                    } catch (err) {
                        throw new Error('cadence segments should be of the form `%f/%f[%d[+%d]]`');
                    }
                });
                return {
                    duration: ringLength,
                    sections: segments
                };
            });
        }
        /**
         * Parse frequency sections
         * @param {String} script
         * @returns {Object}
         * @hidden
         */

    }, {
        key: "freqScript",
        value: function freqScript(script) {
            var freqs = script.split(',');
            return freqs.map(function (freq) {
                try {
                    var tonePair = freq.split('@'),
                        frequency = parseInt(tonePair.shift()),
                        dB = parseFloat(tonePair.shift());
                    if (tonePair.length) {
                        throw Error();
                    }
                    return {
                        frequency: frequency,
                        decibels: dB
                    };
                } catch (err) {
                    throw new Error('freqScript pairs are expected to be of the form `%d@%f[,%d@%f]`');
                }
            });
        }
        /**
         * Parse full tonescripts
         * @param {String} script Tonescript string
         * @returns {Object} Object with frequencies and cadences properties
         * @hidden
         */

    }, {
        key: "toneScript",
        value: function toneScript(script) {
            var sections = script.split(';'),
                frequencies = this.freqScript(sections.shift()),
                cadences = this.cadScript(sections.join(';'));
            return {
                frequencies: frequencies,
                cadences: cadences
            };
        }
        /**
         * Plays tonescript using WebAudio API
         * @param {String} script Tonescript string to be parsed and played
         * @param {Boolean} [loop=false] Plays tonescript audio in a loop if true
         * @hidden
         */

    }, {
        key: "playToneScript",
        value: function playToneScript(script) {
            var loop = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

            if (typeof window.AudioContext != 'undefined' || typeof window.webkitAudioContext != 'undefined') {
                var context = UserMediaManager_1.UserMediaManager.get().audioContext;
                if (context === null) return;
                var parsedToneScript = this.toneScript(script),
                    samples = [],
                    fullDuration = 0;
                var addSilence = function addSilence(sec) {
                    for (var t = 0; t < context.sampleRate * sec; t++) {
                        samples.push(0);
                    }
                };
                var addSound = function addSound(freq, sec) {
                    for (var t = 0; t < context.sampleRate * sec; t++) {
                        var sample = 0;
                        for (var f = 0; f < freq.length; f++) {
                            sample += Math.pow(10, parsedToneScript.frequencies[freq[f]].decibels / 20) * Math.sin((samples.length + t) * (3.14159265359 / context.sampleRate) * parsedToneScript.frequencies[freq[f]].frequency);
                            if (t < 10) sample *= t / 10;
                            if (t > context.sampleRate * sec - 10) sample *= (context.sampleRate * sec - t) / 10;
                        }
                        samples.push(sample);
                    }
                };
                var processSection = function processSection(section, duration) {
                    if (duration != Infinity) var t = duration;else t = duration = 20;
                    if (section.off !== 0 && section.off != Infinity) {
                        while (t > 0) {
                            addSound(section.frequencies, section.on);
                            t -= section.on;
                            addSilence(section.off);
                            t -= section.off;
                            var tt = t * 10;
                            t = parseInt(String(t * 10)) / 10;
                        }
                    } else {
                        addSound(section.frequencies, duration);
                    }
                };
                var processCadence = function processCadence(cadence) {
                    if (cadence.duration != Infinity) fullDuration += cadence.duration;else fullDuration += 20;
                    for (var i = 0; i < cadence.sections.length; i++) {
                        processSection(cadence.sections[i], cadence.duration);
                    }
                };
                this.source = context.createBufferSource();
                for (var k = 0; k < parsedToneScript.cadences.length; k++) {
                    if (parsedToneScript.cadences[k].duration == Infinity) this.source.loop = true;
                    processCadence(parsedToneScript.cadences[k]);
                }
                this.source.connect(context.destination);
                var sndBuffer = context.createBuffer(1, fullDuration * context.sampleRate, context.sampleRate);
                var bufferData = sndBuffer.getChannelData(0);
                for (var i = 0; i < fullDuration * context.sampleRate; i++) {
                    bufferData[i] = samples[i];
                }
                samples = null;
                this.source.buffer = sndBuffer;
                if (loop === true) this.source.loop = true;
                this.source.start(0);
            }
        }
        /**
         * Stops tonescript audio playback
         * @returns {Boolean} True if audio playback was stopped
         * @hidden
         */

    }, {
        key: "stopPlayback",
        value: function stopPlayback() {
            if (typeof this.source !== "undefined" && this.source !== null) {
                this.source.stop(0);
                this.source = null;
                return true;
            }
            return false;
        }
        /**
         * Makes cross-browser XmlHttpRequest
         * @param {String} url URL for HTTP request
         * @param {Function} [callback] Function to be called on compvarion
         * @param {Function} [error] Function to be called in case of error
         * @param {String} [postData] Data to be sent with POST request
         * @hidden
         */

    }, {
        key: "sendRequest",
        value: function sendRequest(url, callback, error, postData) {
            var xdr = false;
            var createXMLHTTPObject = function createXMLHTTPObject() {
                var XMLHttpFactories = [
                //function() { return new XDomainRequest(); },
                function () {
                    return new XMLHttpRequest();
                }, function () {
                    return new ActiveXObject("Msxml2.XMLHTTP");
                }, function () {
                    return new ActiveXObject("Msxml3.XMLHTTP");
                }, function () {
                    return new ActiveXObject("Microsoft.XMLHTTP");
                }];
                var xmlhttp;
                for (var i = 0; i < XMLHttpFactories.length; i++) {
                    try {
                        xmlhttp = XMLHttpFactories[i]();
                        if (i === 0) xdr = true;
                    } catch (e) {
                        continue;
                    }
                    break;
                }
                return xmlhttp;
            };
            var req = createXMLHTTPObject();
            if (!req) return;
            var method = postData ? "POST" : "GET";
            if (!xdr) {
                req.open(method, url, true);
                if (postData) req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                req.onreadystatechange = function () {
                    if (req.readyState != 4) return;
                    if (req.status != 200 && req.status != 304) {
                        error(req);
                        return;
                    }
                    callback(req);
                };
                if (req.readyState == 4) return;
                req.send(postData);
            } else {
                req.onerror = function () {
                    error(req);
                };
                req.ontimeout = function () {
                    error(req);
                };
                req.onload = function () {
                    callback(req);
                };
                req.open(method, url);
                req.timeout = 5000;
                req.send();
            }
        }
        /**
         * Makes request to VoxImplant Load Balancer to get media gateway IP address
         * @param {Function} callback Function to be called on compvarion
         * @param {Boolean} [reservedBalancer=false] Try reserved balancer if true
         * @hidden
         */

    }, {
        key: "getServers",
        value: function getServers(callback) {
            var reservedBalancer = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
            var vi = arguments[2];

            var protocol = 'https:' == document.location.protocol ? 'https://' : 'http://';
            if (reservedBalancer === true) var balancer_url = protocol + "balancer.voximplant.com/getNearestHost";else balancer_url = protocol + "balancer.voximplant.com/getNearestHost";
            this.sendRequest(balancer_url, function (XHR) {
                balancerCompvare(XHR.responseText);
            }, function (XHR) {
                balancerCompvare(null);
            });
            function balancerCompvare(data) {
                if (data !== null) callback(data);else if (reservedBalancer !== true) this.getServers(callback, true, vi);else vi.dispatchEvent({ name: 'ConnectionFailed', message: "VoxImplant Cloud is unavailable" });
            }
        }
        /**
         * @hidden
         * The simplest function to get an UUID string.
         * @returns {string} A version 4 UUID string.
         */

    }, {
        key: "generateUUID",
        value: function generateUUID() {
            var rand = this._gri,
                hex = this._ha;
            return hex(rand(32), 8) + "-" + hex(rand(16), 4) + "-" + hex(0x4000 | rand(12), 4) + "-" + hex(0x8000 | rand(14), 4) + "-" + hex(rand(48), 12);
        }
        /**
         * Returns an unsigned x-bit random integer.
         * @hidden
         * @param {int} x A positive integer ranging from 0 to 53, inclusive.
         * @returns {int} An unsigned x-bit random integer (0 <= f(x) < 2^x).
         */

    }, {
        key: "_gri",
        value: function _gri(x) {
            if (x < 0) return NaN;
            if (x <= 30) return 0 | Math.random() * (1 << x);
            if (x <= 53) return (0 | Math.random() * (1 << 30)) + (0 | Math.random() * (1 << x - 30)) * (1 << 30);
            return NaN;
        }
        /**
         * Converts an integer to a zero-filled hexadecimal string.
         * @hidden
         * @param {int} num
         * @param {int} length
         * @returns {string}
         */

    }, {
        key: "_ha",
        value: function _ha(num, length) {
            var str = num.toString(16),
                i = length - str.length,
                z = "0";
            for (; i > 0; i >>>= 1, z += z) {
                if (i & 1) {
                    str = z + str;
                }
            }
            return str;
        }
    }, {
        key: "filterXSS",
        value: function filterXSS(content) {
            var div = document.createElement("div");
            div.appendChild(document.createTextNode(content));
            content = div.innerHTML;
            return content;
        }
        /**
         * Check if !connected
         * @hidden
         */

    }, {
        key: "checkCA",
        value: function checkCA() {
            if (!Client_1.Client.getInstance().connected()) throw new Error("NOT_CONNECTED_TO_VOXIMPLANT");
            if (!Authenticator_1.Authenticator.get().authorized()) throw new Error("NOT_AUTHORIZED");
        }
        /**
         * Promise to check browser compability level
         * @param level 'webrtc'|'signaling'
         */

    }, {
        key: "canRTC",
        value: function canRTC(level) {
            return;
        }
        /**
         * Complite defaults with settings
         * @param left defaults
         * @param right settings
         * @returns {Object}
         */

    }, {
        key: "mixObjectToLeft",
        value: function mixObjectToLeft(left, right) {
            for (var left_key in left) {
                if (typeof right[left_key] == "undefined") continue;
                left[left_key] = right[left_key];
            }
            return left;
        }
    }, {
        key: "makeRandomString",
        value: function makeRandomString(length) {
            var possible = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789+/";
            var randomSrtring = '';
            for (var i = 0; i < length; i++) {
                randomSrtring += possible.charAt(Math.floor(Math.random() * possible.length));
            }
            return randomSrtring;
        }
    }]);

    return Utils;
}();

Utils.source = null;
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.UTILS)], Utils, "extend", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.UTILS)], Utils, "stringifyExtraHeaders", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.UTILS)], Utils, "cadScript", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.UTILS)], Utils, "freqScript", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.UTILS)], Utils, "toneScript", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.UTILS)], Utils, "playToneScript", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.UTILS)], Utils, "stopPlayback", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.UTILS)], Utils, "sendRequest", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.UTILS)], Utils, "getServers", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.UTILS)], Utils, "generateUUID", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.UTILS)], Utils, "_gri", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.UTILS)], Utils, "_ha", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.UTILS)], Utils, "filterXSS", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.UTILS)], Utils, "checkCA", null);
exports.Utils = Utils;

/***/ }),
/* 22 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * Created by i on 11.01.2017.
 */

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

Object.defineProperty(exports, "__esModule", { value: true });
var UserMediaManager_1 = __webpack_require__(4);
/**
 * Singleton that provides audio/video media
 * Reuses audio/video tracks
 * @hidden
 */

var MediaCache = function () {
    _createClass(MediaCache, null, [{
        key: "get",
        value: function get() {
            if (!this.inst) this.inst = new MediaCache();
            return this.inst;
        }
    }]);

    function MediaCache() {
        _classCallCheck(this, MediaCache);

        this.audioCache = {};
        this.videoCache = {};
    }

    _createClass(MediaCache, [{
        key: "clear",
        value: function clear() {
            var _iteratorNormalCompletion = true;
            var _didIteratorError = false;
            var _iteratorError = undefined;

            try {
                for (var _iterator = this.videoCache[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                    var track = _step.value;

                    track.stop();
                }
            } catch (err) {
                _didIteratorError = true;
                _iteratorError = err;
            } finally {
                try {
                    if (!_iteratorNormalCompletion && _iterator.return) {
                        _iterator.return();
                    }
                } finally {
                    if (_didIteratorError) {
                        throw _iteratorError;
                    }
                }
            }

            var _iteratorNormalCompletion2 = true;
            var _didIteratorError2 = false;
            var _iteratorError2 = undefined;

            try {
                for (var _iterator2 = this.audioCache[Symbol.iterator](), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
                    var _track = _step2.value;

                    _track.stop();
                }
            } catch (err) {
                _didIteratorError2 = true;
                _iteratorError2 = err;
            } finally {
                try {
                    if (!_iteratorNormalCompletion2 && _iterator2.return) {
                        _iterator2.return();
                    }
                } finally {
                    if (_didIteratorError2) {
                        throw _iteratorError2;
                    }
                }
            }
        }
    }, {
        key: "getTrack",
        value: function getTrack(source, kind) {
            var _this = this;

            return new Promise(function (resolve, reject) {
                if (source == '') source = 'def';
                switch (kind) {
                    case "audio":
                        resolve(_this.audioCache[source]);
                        break;
                    case "video":
                        resolve(_this.videoCache[source]);
                        break;
                    default:
                        resolve();
                        break;
                }
            });
        }
    }, {
        key: "setTrack",
        value: function setTrack(source, kind, track) {
            if (source == '') source = 'def';
            switch (kind) {
                case "audio":
                    this.audioCache[source] = track;
                    break;
                case "video":
                    this.videoCache[source] = track;
                    break;
                default:
                    return;
            }
            track.onmute = function (e) {
                MediaCache.get().removeTrackByTrack(e.target);
            };
        }
    }, {
        key: "removeTrackByTrack",
        value: function removeTrackByTrack(track) {
            switch (track.kind) {
                case "audio":
                    this._removeTrackFromCache(this.audioCache, track);
                    break;
                case "video":
                    this._removeTrackFromCache(this.videoCache, track);
                    break;
                default:
                    return;
            }
            UserMediaManager_1.UserMediaManager.get().resetLocalVideo();
        }
    }, {
        key: "_removeTrackFromCache",
        value: function _removeTrackFromCache(cache, track) {
            for (var id in cache) {
                if (cache.hasOwnProperty(id)) {
                    if (cache[id].id === track.id) delete cache[id];
                }
            }
            track.stop();
        }
    }, {
        key: "getAudioCache",
        value: function getAudioCache() {
            return this.audioCache;
        }
    }, {
        key: "getVideoCache",
        value: function getVideoCache() {
            return this.videoCache;
        }
        /**
         * @hidden
         * @return {string}
         * @private
         */

    }, {
        key: "_traceName",
        value: function _traceName() {
            return 'MediaCache';
        }
    }]);

    return MediaCache;
}();

exports.MediaCache = MediaCache;

/***/ }),
/* 23 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var __decorate = undefined && undefined.__decorate || function (decorators, target, key, desc) {
    var c = arguments.length,
        r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc,
        d;
    if ((typeof Reflect === "undefined" ? "undefined" : _typeof(Reflect)) === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);else for (var i = decorators.length - 1; i >= 0; i--) {
        if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    }return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var CallEvents_1 = __webpack_require__(9);
var Logger_1 = __webpack_require__(0);
/**
 * @hidden
 */

var ReInviteQ = function () {
    function ReInviteQ(call, _pcStatus) {
        var _this = this;

        _classCallCheck(this, ReInviteQ);

        this._pcStatus = _pcStatus;
        this._q = [];
        call.on(CallEvents_1.CallEvents.Updated, function (e) {
            Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.REINVITEQ, 'CallEvent', Logger_1.LogLevel.TRACE, "Updated with result " + e.result);
            if (ReInviteQ._currentReinvite) {
                var reinvite = ReInviteQ._currentReinvite;
                if (e.result) reinvite.resolve(e);else reinvite.reject(e);
                ReInviteQ._currentReinvite = undefined;
            }
            _this.runNext();
        });
        call.on(CallEvents_1.CallEvents.PendingUpdate, function (e) {
            Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.REINVITEQ, 'CallEvent', Logger_1.LogLevel.TRACE, "IncomingUpdate. Local RI==" + _typeof(ReInviteQ._currentReinvite));
            if (ReInviteQ._currentReinvite) {
                ReInviteQ._currentReinvite.reject();
                ReInviteQ._currentReinvite = undefined;
            }
        });
        call.on(CallEvents_1.CallEvents.UpdateFailed, function (e) {
            Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.REINVITEQ, 'CallEvent', Logger_1.LogLevel.TRACE, "UpdateFailed");
            if (ReInviteQ._currentReinvite) {
                ReInviteQ._currentReinvite.reject();
                ReInviteQ._currentReinvite = undefined;
            }
        });
    }

    _createClass(ReInviteQ, [{
        key: "runNext",
        value: function runNext() {
            if (typeof ReInviteQ._currentReinvite === "undefined" && this._q.length > 0 && this._pcStatus()) {
                ReInviteQ._currentReinvite = this._q.splice(0, 1)[0];
                ReInviteQ._currentReinvite.fx();
            }
        }
    }, {
        key: "add",
        value: function add(member) {
            this._q.push(member);
            this.runNext();
        }
    }, {
        key: "clear",
        value: function clear() {
            this._q.forEach(function (member) {
                member.reject();
            });
        }
        /**
         * @hidden
         * @return {string}
         * @private
         */

    }, {
        key: "_traceName",
        value: function _traceName() {
            return 'ReInviteQ';
        }
    }]);

    return ReInviteQ;
}();

__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.REINVITEQ)], ReInviteQ.prototype, "runNext", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.REINVITEQ)], ReInviteQ.prototype, "add", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.REINVITEQ)], ReInviteQ.prototype, "clear", null);
exports.ReInviteQ = ReInviteQ;

/***/ }),
/* 24 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var __decorate = undefined && undefined.__decorate || function (decorators, target, key, desc) {
    var c = arguments.length,
        r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc,
        d;
    if ((typeof Reflect === "undefined" ? "undefined" : _typeof(Reflect)) === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);else for (var i = decorators.length - 1; i >= 0; i--) {
        if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    }return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var Logger_1 = __webpack_require__(0);
/**
 * @hidden
 */

var SDPMuggle = function () {
    function SDPMuggle() {
        _classCallCheck(this, SDPMuggle);
    }

    _createClass(SDPMuggle, [{
        key: "_traceName",

        /**
         * @hidden
         * @return {string}
         * @private
         */
        value: function _traceName() {
            return 'SDPMuggle';
        }
    }], [{
        key: "detectDirections",
        value: function detectDirections(sdp) {
            var ret = [];
            var splitsdp = sdp.split(/(\r\n|\r|\n)/).filter(SDPMuggle.validLine);
            var currentSection = '';
            splitsdp.forEach(function (item) {
                if (item.indexOf('m=') === 0) {
                    var directionStr = item.substr(2);
                    currentSection = directionStr.split(' ')[0];
                }
                if (currentSection !== '' && (item === 'a=sendrecv' || item === 'a=sendonly' || item === 'a=recvonly' || item === 'a=inactive')) {
                    ret.push({ type: currentSection, direction: item.substr(2) });
                    currentSection = '';
                }
            });
            return ret;
        }
    }, {
        key: "removeTelephoneEvents",
        value: function removeTelephoneEvents(sdp) {
            if (sdp.sdp.indexOf('a=rtpmap:127 telephone-event/8000') !== -1) {
                var sdpLines = sdp.sdp.split(/(\r\n|\r|\n)/).filter(SDPMuggle.validLine);
                var removenumber = -1;
                for (var i = 0; i < sdpLines.length; i++) {
                    if (sdpLines[i].indexOf('m=audio') !== -1) {
                        var line = sdpLines[i];
                        if (typeof line === "string") sdpLines[i] = line.replace(' 127', '');
                    }
                    if (sdpLines[i].indexOf('a=rtpmap:127 telephone-event/8000') !== -1) removenumber = i;
                }
                sdpLines.splice(removenumber, 1);
                sdp.sdp = sdpLines.join('\r\n') + '\r\n';
            }
            return sdp;
        }
    }, {
        key: "removeDoubleOpus",
        value: function removeDoubleOpus(sdp) {
            if (sdp.sdp.indexOf('a=rtpmap:109 opus') !== -1 && sdp.sdp.indexOf('a=rtpmap:111 opus') !== -1) {
                var sdpLines = sdp.sdp.split(/(\r\n|\r|\n)/).filter(SDPMuggle.validLine);
                var removenumber = -1;
                for (var i = 0; i < sdpLines.length; i++) {
                    if (sdpLines[i].indexOf('m=audio') !== -1) {
                        var line = sdpLines[i];
                        if (typeof line === "string") sdpLines[i] = line.replace(' 109', '');
                    }
                    if (sdpLines[i].indexOf('a=rtpmap:109 opus') !== -1) removenumber = i;
                }
                sdpLines.splice(removenumber, 1);
                sdp.sdp = sdpLines.join('\r\n') + '\r\n';
            }
            return sdp;
        }
    }, {
        key: "removeTIAS",
        value: function removeTIAS(sdp) {
            if (sdp.sdp.indexOf('b=TIAS') !== -1) {
                var sdpLines = sdp.sdp.split(/(\r\n|\r|\n)/).filter(SDPMuggle.validLine);
                var removenumbers = [];
                sdpLines.forEach(function (item, index) {
                    if (item.indexOf('b=TIAS') !== -1) removenumbers.unshift(index);
                });
                removenumbers.forEach(function (item) {
                    return sdpLines.splice(item, 1);
                });
                sdp = { type: sdp.type, sdp: sdpLines.join('\r\n') + '\r\n' };
            }
            return sdp;
        }
    }, {
        key: "fixVideoRecieve",
        value: function fixVideoRecieve(sdp, recieveVideo) {
            var videoPosition = sdp.sdp.indexOf('m=video');
            if (videoPosition !== -1 && !recieveVideo) {
                var sdpLines = sdp.sdp.split(/(\r\n|\r|\n)/).filter(SDPMuggle.validLine);
                var videoindex = null;
                sdpLines = sdpLines.map(function (item, index) {
                    if (videoindex === null) {
                        if (item.indexOf('m=video') !== -1) videoindex = index;
                    } else {
                        if (item === 'a=sendrecv') item = 'a=sendonly';else if (item === 'a=recvonly') item = 'a=inactive';
                    }
                    return item;
                });
                sdp.sdp = sdpLines.join('\r\n') + '\r\n';
            }
            return sdp;
        }
    }, {
        key: "addSetupAttribute",
        value: function addSetupAttribute(sdp) {
            var setupPosition = sdp.indexOf('a=setup:');
            if (setupPosition == -1) {
                sdp += 'a=setup:actpass\r\n';
            }
            return sdp;
        }
    }]);

    return SDPMuggle;
}();

SDPMuggle.validLine = RegExp.prototype.test.bind(/^([a-z])=(.*)/);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], SDPMuggle, "removeTelephoneEvents", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], SDPMuggle, "removeDoubleOpus", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], SDPMuggle, "removeTIAS", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], SDPMuggle, "fixVideoRecieve", null);
exports.SDPMuggle = SDPMuggle;

/***/ }),
/* 25 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var __decorate = undefined && undefined.__decorate || function (decorators, target, key, desc) {
    var c = arguments.length,
        r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc,
        d;
    if ((typeof Reflect === "undefined" ? "undefined" : _typeof(Reflect)) === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);else for (var i = decorators.length - 1; i >= 0; i--) {
        if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    }return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var Logger_1 = __webpack_require__(0);
var MsgEnums_1 = __webpack_require__(26);
var VoxSignaling_1 = __webpack_require__(1);
var EventDispatcher_1 = __webpack_require__(15);
/**
 * Created by irbisadm on 24/10/2016.
 * @hidden
 */

var MsgSignaling = function (_EventDispatcher_1$Ev) {
    _inherits(MsgSignaling, _EventDispatcher_1$Ev);

    function MsgSignaling() {
        _classCallCheck(this, MsgSignaling);

        var _this = _possibleConstructorReturn(this, (MsgSignaling.__proto__ || Object.getPrototypeOf(MsgSignaling)).call(this));

        if (MsgSignaling.instance) {
            throw new Error("Error - use Client.getMessagingInstance()");
        }
        _this.eventListeners = [];
        _this.query = [];
        setInterval(function () {
            _this.updateQuery();
        }, 220);
        return _this;
    }
    /**
     * Core event handler
     * @hidden
     * @param parsedData
     */


    _createClass(MsgSignaling, [{
        key: "handleWsData",
        value: function handleWsData(parsedData) {
            var validEvents = ["onCreateConversation", "onEditConversation", "onRemoveConversation", "onJoinConversation", "onLeaveConversation", "onGetConversation", "onSendMessage", "onEditMessage", "onRemoveMessage", "onTyping", "onRetransmitEvents", "onEditUser", "onGetUser", "isRead", "isDelivered", "onError", "onSubscribe", "onUnSubscribe", "onSetStatus"];
            if (validEvents.indexOf(parsedData.event) != -1) this.dispatchEvent(parsedData.event, parsedData);else throw new Error('Unknown messaging event ' + parsedData.event + ' with payload ' + JSON.stringify(parsedData.payload));
        }
    }, {
        key: "sendPayload",

        /**
         * Core messaging sender
         * @param event
         * @param payload
         * @returns {boolean}
         */
        value: function sendPayload(event, payload) {
            var rawTemplate = {
                service: MsgEnums_1.MsgService.Chat,
                event: event,
                payload: payload
            };
            this.query.push(rawTemplate);
            return true;
        }
    }, {
        key: "updateQuery",
        value: function updateQuery() {
            if (this.query.length) {
                var item = this.query.splice(0, 1);
                VoxSignaling_1.VoxSignaling.get().sendRaw(item[0]);
            }
        }
    }, {
        key: "dispatchEvent",
        value: function dispatchEvent(event, data) {
            if (typeof this.eventListeners[event] != 'undefined') for (var i = 0; i < this.eventListeners[event].length; i++) {
                if (typeof this.eventListeners[event][i] == "function") this.eventListeners[event][i](data.payload);
            }
        }
        /**
         * @hidden
         * @return {string}
         * @private
         */

    }, {
        key: "_traceName",
        value: function _traceName() {
            return 'MsgSignaling';
        }
    }], [{
        key: "get",
        value: function get() {
            MsgSignaling.instance = MsgSignaling.instance || new MsgSignaling();
            return MsgSignaling.instance;
        }
    }]);

    return MsgSignaling;
}(EventDispatcher_1.EventDispatcher);

__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], MsgSignaling.prototype, "handleWsData", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], MsgSignaling.prototype, "sendPayload", null);
exports.MsgSignaling = MsgSignaling;

/***/ }),
/* 26 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", { value: true });
/**
 * Created by irbisadm on 22/09/16.
 */
/**
 * @hidden
 */
var MsgService;
(function (MsgService) {
  MsgService[MsgService["Chat"] = "chat"] = "Chat";
})(MsgService = exports.MsgService || (exports.MsgService = {}));
/**
 * @hidden
 */
var MsgAction;
(function (MsgAction) {
  MsgAction[MsgAction["createConversation"] = "createConversation"] = "createConversation";
  MsgAction[MsgAction["editConversation"] = "editConversation"] = "editConversation";
  MsgAction[MsgAction["removeConversation"] = "removeConversation"] = "removeConversation";
  MsgAction[MsgAction["joinConversation"] = "joinConversation"] = "joinConversation";
  MsgAction[MsgAction["leaveConversation"] = "leaveConversation"] = "leaveConversation";
  MsgAction[MsgAction["getConversation"] = "getConversation"] = "getConversation";
  MsgAction[MsgAction["getConversations"] = "getConversations"] = "getConversations";
  MsgAction[MsgAction["sendMessage"] = "sendMessage"] = "sendMessage";
  MsgAction[MsgAction["editMessage"] = "editMessage"] = "editMessage";
  MsgAction[MsgAction["removeMessage"] = "removeMessage"] = "removeMessage";
  MsgAction[MsgAction["typingMessage"] = "typingMessage"] = "typingMessage";
  MsgAction[MsgAction["editUser"] = "editUser"] = "editUser";
  MsgAction[MsgAction["getUser"] = "getUser"] = "getUser";
  MsgAction[MsgAction["getUsers"] = "getUsers"] = "getUsers";
  MsgAction[MsgAction["retransmitEvents"] = "retransmitEvents"] = "retransmitEvents";
  MsgAction[MsgAction["isRead"] = "isRead"] = "isRead";
  MsgAction[MsgAction["isDelivered"] = "isDelivered"] = "isDelivered";
  MsgAction[MsgAction["addParticipants"] = "addParticipants"] = "addParticipants";
  MsgAction[MsgAction["editParticipants"] = "editParticipants"] = "editParticipants";
  MsgAction[MsgAction["removeParticipants"] = "removeParticipants"] = "removeParticipants";
  MsgAction[MsgAction["addModerators"] = "addModerators"] = "addModerators";
  MsgAction[MsgAction["removeModerators"] = "removeModerators"] = "removeModerators";
  MsgAction[MsgAction["subscribe"] = "subscribe"] = "subscribe";
  MsgAction[MsgAction["unsubscribe"] = "unsubscribe"] = "unsubscribe";
  MsgAction[MsgAction["setStatus"] = "setStatus"] = "setStatus";
})(MsgAction = exports.MsgAction || (exports.MsgAction = {}));
/**
 * @hidden
 */
var MsgEvent;
(function (MsgEvent) {
  MsgEvent[MsgEvent["onCreateConversation"] = "onCreateConversation"] = "onCreateConversation";
  MsgEvent[MsgEvent["onEditConversation"] = "onEditConversation"] = "onEditConversation";
  MsgEvent[MsgEvent["onRemoveConversation"] = "onRemoveConversation"] = "onRemoveConversation";
  MsgEvent[MsgEvent["onJoinConversation"] = "onJoinConversation"] = "onJoinConversation";
  MsgEvent[MsgEvent["onLeaveConversation"] = "onLeaveConversation"] = "onLeaveConversation";
  MsgEvent[MsgEvent["onGetConversation"] = "onGetConversation"] = "onGetConversation";
  MsgEvent[MsgEvent["onSendMessage"] = "onSendMessage"] = "onSendMessage";
  MsgEvent[MsgEvent["onEditMessage"] = "onEditMessage"] = "onEditMessage";
  MsgEvent[MsgEvent["onRemoveMessage"] = "onRemoveMessage"] = "onRemoveMessage";
  MsgEvent[MsgEvent["onTyping"] = "onTyping"] = "onTyping";
  MsgEvent[MsgEvent["onRetransmitEvents"] = "onRetransmitEvents"] = "onRetransmitEvents";
  MsgEvent[MsgEvent["onEditUser"] = "onEditUser"] = "onEditUser";
  MsgEvent[MsgEvent["onGetUser"] = "onGetUser"] = "onGetUser";
  MsgEvent[MsgEvent["onError"] = "onError"] = "onError";
  MsgEvent[MsgEvent["isRead"] = "isRead"] = "isRead";
  MsgEvent[MsgEvent["isDelivered"] = "isDelivered"] = "isDelivered";
  MsgEvent[MsgEvent["onsubscribe"] = "onsubscribe"] = "onsubscribe";
  MsgEvent[MsgEvent["onUnSubscribe"] = "onUnSubscribe"] = "onUnSubscribe";
  MsgEvent[MsgEvent["onSetStatus"] = "onSetStatus"] = "onSetStatus";
})(MsgEvent = exports.MsgEvent || (exports.MsgEvent = {}));

/***/ }),
/* 27 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

Object.defineProperty(exports, "__esModule", { value: true });
/**
 * Created by irbisadm on 23/09/2016.
 * @hidden
 */

var GUID = function () {
    function GUID(str) {
        _classCallCheck(this, GUID);

        this.str = str || GUID.getNewGUIDString();
    }

    _createClass(GUID, [{
        key: "toString",
        value: function toString() {
            return this.str;
        }
    }, {
        key: "_traceName",

        /**
         * @hidden
         * @return {string}
         * @private
         */
        value: function _traceName() {
            return 'GUID';
        }
    }], [{
        key: "getNewGUIDString",
        value: function getNewGUIDString() {
            // your favourite guid generation function could go here
            // ex: http://stackoverflow.com/a/8809472/188246
            var d = new Date().getTime();
            if (window.performance && typeof window.performance.now === "function") {
                d += performance.now(); //use high-precision timer if available
            }
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                var r = (d + Math.random() * 16) % 16 | 0;
                d = Math.floor(d / 16);
                return (c == 'x' ? r : r & 0x3 | 0x8).toString(16);
            });
        }
    }]);

    return GUID;
}();

exports.GUID = GUID;

/***/ }),
/* 28 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
 /* eslint-env node */


// SDP helpers.
var SDPUtils = {};

// Generate an alphanumeric identifier for cname or mids.
// TODO: use UUIDs instead? https://gist.github.com/jed/982883
SDPUtils.generateIdentifier = function() {
  return Math.random().toString(36).substr(2, 10);
};

// The RTCP CNAME used by all peerconnections from the same JS.
SDPUtils.localCName = SDPUtils.generateIdentifier();

// Splits SDP into lines, dealing with both CRLF and LF.
SDPUtils.splitLines = function(blob) {
  return blob.trim().split('\n').map(function(line) {
    return line.trim();
  });
};
// Splits SDP into sessionpart and mediasections. Ensures CRLF.
SDPUtils.splitSections = function(blob) {
  var parts = blob.split('\nm=');
  return parts.map(function(part, index) {
    return (index > 0 ? 'm=' + part : part).trim() + '\r\n';
  });
};

// Returns lines that start with a certain prefix.
SDPUtils.matchPrefix = function(blob, prefix) {
  return SDPUtils.splitLines(blob).filter(function(line) {
    return line.indexOf(prefix) === 0;
  });
};

// Parses an ICE candidate line. Sample input:
// candidate:702786350 2 udp 41819902 8.8.8.8 60769 typ relay raddr 8.8.8.8
// rport 55996"
SDPUtils.parseCandidate = function(line) {
  var parts;
  // Parse both variants.
  if (line.indexOf('a=candidate:') === 0) {
    parts = line.substring(12).split(' ');
  } else {
    parts = line.substring(10).split(' ');
  }

  var candidate = {
    foundation: parts[0],
    component: parseInt(parts[1], 10),
    protocol: parts[2].toLowerCase(),
    priority: parseInt(parts[3], 10),
    ip: parts[4],
    port: parseInt(parts[5], 10),
    // skip parts[6] == 'typ'
    type: parts[7]
  };

  for (var i = 8; i < parts.length; i += 2) {
    switch (parts[i]) {
      case 'raddr':
        candidate.relatedAddress = parts[i + 1];
        break;
      case 'rport':
        candidate.relatedPort = parseInt(parts[i + 1], 10);
        break;
      case 'tcptype':
        candidate.tcpType = parts[i + 1];
        break;
      case 'ufrag':
        candidate.ufrag = parts[i + 1]; // for backward compability.
        candidate.usernameFragment = parts[i + 1];
        break;
      default: // extension handling, in particular ufrag
        candidate[parts[i]] = parts[i + 1];
        break;
    }
  }
  return candidate;
};

// Translates a candidate object into SDP candidate attribute.
SDPUtils.writeCandidate = function(candidate) {
  var sdp = [];
  sdp.push(candidate.foundation);
  sdp.push(candidate.component);
  sdp.push(candidate.protocol.toUpperCase());
  sdp.push(candidate.priority);
  sdp.push(candidate.ip);
  sdp.push(candidate.port);

  var type = candidate.type;
  sdp.push('typ');
  sdp.push(type);
  if (type !== 'host' && candidate.relatedAddress &&
      candidate.relatedPort) {
    sdp.push('raddr');
    sdp.push(candidate.relatedAddress); // was: relAddr
    sdp.push('rport');
    sdp.push(candidate.relatedPort); // was: relPort
  }
  if (candidate.tcpType && candidate.protocol.toLowerCase() === 'tcp') {
    sdp.push('tcptype');
    sdp.push(candidate.tcpType);
  }
  if (candidate.ufrag) {
    sdp.push('ufrag');
    sdp.push(candidate.ufrag);
  }
  return 'candidate:' + sdp.join(' ');
};

// Parses an ice-options line, returns an array of option tags.
// a=ice-options:foo bar
SDPUtils.parseIceOptions = function(line) {
  return line.substr(14).split(' ');
}

// Parses an rtpmap line, returns RTCRtpCoddecParameters. Sample input:
// a=rtpmap:111 opus/48000/2
SDPUtils.parseRtpMap = function(line) {
  var parts = line.substr(9).split(' ');
  var parsed = {
    payloadType: parseInt(parts.shift(), 10) // was: id
  };

  parts = parts[0].split('/');

  parsed.name = parts[0];
  parsed.clockRate = parseInt(parts[1], 10); // was: clockrate
  // was: channels
  parsed.numChannels = parts.length === 3 ? parseInt(parts[2], 10) : 1;
  return parsed;
};

// Generate an a=rtpmap line from RTCRtpCodecCapability or
// RTCRtpCodecParameters.
SDPUtils.writeRtpMap = function(codec) {
  var pt = codec.payloadType;
  if (codec.preferredPayloadType !== undefined) {
    pt = codec.preferredPayloadType;
  }
  return 'a=rtpmap:' + pt + ' ' + codec.name + '/' + codec.clockRate +
      (codec.numChannels !== 1 ? '/' + codec.numChannels : '') + '\r\n';
};

// Parses an a=extmap line (headerextension from RFC 5285). Sample input:
// a=extmap:2 urn:ietf:params:rtp-hdrext:toffset
// a=extmap:2/sendonly urn:ietf:params:rtp-hdrext:toffset
SDPUtils.parseExtmap = function(line) {
  var parts = line.substr(9).split(' ');
  return {
    id: parseInt(parts[0], 10),
    direction: parts[0].indexOf('/') > 0 ? parts[0].split('/')[1] : 'sendrecv',
    uri: parts[1]
  };
};

// Generates a=extmap line from RTCRtpHeaderExtensionParameters or
// RTCRtpHeaderExtension.
SDPUtils.writeExtmap = function(headerExtension) {
  return 'a=extmap:' + (headerExtension.id || headerExtension.preferredId) +
      (headerExtension.direction && headerExtension.direction !== 'sendrecv'
          ? '/' + headerExtension.direction
          : '') +
      ' ' + headerExtension.uri + '\r\n';
};

// Parses an ftmp line, returns dictionary. Sample input:
// a=fmtp:96 vbr=on;cng=on
// Also deals with vbr=on; cng=on
SDPUtils.parseFmtp = function(line) {
  var parsed = {};
  var kv;
  var parts = line.substr(line.indexOf(' ') + 1).split(';');
  for (var j = 0; j < parts.length; j++) {
    kv = parts[j].trim().split('=');
    parsed[kv[0].trim()] = kv[1];
  }
  return parsed;
};

// Generates an a=ftmp line from RTCRtpCodecCapability or RTCRtpCodecParameters.
SDPUtils.writeFmtp = function(codec) {
  var line = '';
  var pt = codec.payloadType;
  if (codec.preferredPayloadType !== undefined) {
    pt = codec.preferredPayloadType;
  }
  if (codec.parameters && Object.keys(codec.parameters).length) {
    var params = [];
    Object.keys(codec.parameters).forEach(function(param) {
      params.push(param + '=' + codec.parameters[param]);
    });
    line += 'a=fmtp:' + pt + ' ' + params.join(';') + '\r\n';
  }
  return line;
};

// Parses an rtcp-fb line, returns RTCPRtcpFeedback object. Sample input:
// a=rtcp-fb:98 nack rpsi
SDPUtils.parseRtcpFb = function(line) {
  var parts = line.substr(line.indexOf(' ') + 1).split(' ');
  return {
    type: parts.shift(),
    parameter: parts.join(' ')
  };
};
// Generate a=rtcp-fb lines from RTCRtpCodecCapability or RTCRtpCodecParameters.
SDPUtils.writeRtcpFb = function(codec) {
  var lines = '';
  var pt = codec.payloadType;
  if (codec.preferredPayloadType !== undefined) {
    pt = codec.preferredPayloadType;
  }
  if (codec.rtcpFeedback && codec.rtcpFeedback.length) {
    // FIXME: special handling for trr-int?
    codec.rtcpFeedback.forEach(function(fb) {
      lines += 'a=rtcp-fb:' + pt + ' ' + fb.type +
      (fb.parameter && fb.parameter.length ? ' ' + fb.parameter : '') +
          '\r\n';
    });
  }
  return lines;
};

// Parses an RFC 5576 ssrc media attribute. Sample input:
// a=ssrc:3735928559 cname:something
SDPUtils.parseSsrcMedia = function(line) {
  var sp = line.indexOf(' ');
  var parts = {
    ssrc: parseInt(line.substr(7, sp - 7), 10)
  };
  var colon = line.indexOf(':', sp);
  if (colon > -1) {
    parts.attribute = line.substr(sp + 1, colon - sp - 1);
    parts.value = line.substr(colon + 1);
  } else {
    parts.attribute = line.substr(sp + 1);
  }
  return parts;
};

// Extracts the MID (RFC 5888) from a media section.
// returns the MID or undefined if no mid line was found.
SDPUtils.getMid = function(mediaSection) {
  var mid = SDPUtils.matchPrefix(mediaSection, 'a=mid:')[0];
  if (mid) {
    return mid.substr(6);
  }
}

SDPUtils.parseFingerprint = function(line) {
  var parts = line.substr(14).split(' ');
  return {
    algorithm: parts[0].toLowerCase(), // algorithm is case-sensitive in Edge.
    value: parts[1]
  };
};

// Extracts DTLS parameters from SDP media section or sessionpart.
// FIXME: for consistency with other functions this should only
//   get the fingerprint line as input. See also getIceParameters.
SDPUtils.getDtlsParameters = function(mediaSection, sessionpart) {
  var lines = SDPUtils.matchPrefix(mediaSection + sessionpart,
      'a=fingerprint:');
  // Note: a=setup line is ignored since we use the 'auto' role.
  // Note2: 'algorithm' is not case sensitive except in Edge.
  return {
    role: 'auto',
    fingerprints: lines.map(SDPUtils.parseFingerprint)
  };
};

// Serializes DTLS parameters to SDP.
SDPUtils.writeDtlsParameters = function(params, setupType) {
  var sdp = 'a=setup:' + setupType + '\r\n';
  params.fingerprints.forEach(function(fp) {
    sdp += 'a=fingerprint:' + fp.algorithm + ' ' + fp.value + '\r\n';
  });
  return sdp;
};
// Parses ICE information from SDP media section or sessionpart.
// FIXME: for consistency with other functions this should only
//   get the ice-ufrag and ice-pwd lines as input.
SDPUtils.getIceParameters = function(mediaSection, sessionpart) {
  var lines = SDPUtils.splitLines(mediaSection);
  // Search in session part, too.
  lines = lines.concat(SDPUtils.splitLines(sessionpart));
  var iceParameters = {
    usernameFragment: lines.filter(function(line) {
      return line.indexOf('a=ice-ufrag:') === 0;
    })[0].substr(12),
    password: lines.filter(function(line) {
      return line.indexOf('a=ice-pwd:') === 0;
    })[0].substr(10)
  };
  return iceParameters;
};

// Serializes ICE parameters to SDP.
SDPUtils.writeIceParameters = function(params) {
  return 'a=ice-ufrag:' + params.usernameFragment + '\r\n' +
      'a=ice-pwd:' + params.password + '\r\n';
};

// Parses the SDP media section and returns RTCRtpParameters.
SDPUtils.parseRtpParameters = function(mediaSection) {
  var description = {
    codecs: [],
    headerExtensions: [],
    fecMechanisms: [],
    rtcp: []
  };
  var lines = SDPUtils.splitLines(mediaSection);
  var mline = lines[0].split(' ');
  for (var i = 3; i < mline.length; i++) { // find all codecs from mline[3..]
    var pt = mline[i];
    var rtpmapline = SDPUtils.matchPrefix(
        mediaSection, 'a=rtpmap:' + pt + ' ')[0];
    if (rtpmapline) {
      var codec = SDPUtils.parseRtpMap(rtpmapline);
      var fmtps = SDPUtils.matchPrefix(
          mediaSection, 'a=fmtp:' + pt + ' ');
      // Only the first a=fmtp:<pt> is considered.
      codec.parameters = fmtps.length ? SDPUtils.parseFmtp(fmtps[0]) : {};
      codec.rtcpFeedback = SDPUtils.matchPrefix(
          mediaSection, 'a=rtcp-fb:' + pt + ' ')
        .map(SDPUtils.parseRtcpFb);
      description.codecs.push(codec);
      // parse FEC mechanisms from rtpmap lines.
      switch (codec.name.toUpperCase()) {
        case 'RED':
        case 'ULPFEC':
          description.fecMechanisms.push(codec.name.toUpperCase());
          break;
        default: // only RED and ULPFEC are recognized as FEC mechanisms.
          break;
      }
    }
  }
  SDPUtils.matchPrefix(mediaSection, 'a=extmap:').forEach(function(line) {
    description.headerExtensions.push(SDPUtils.parseExtmap(line));
  });
  // FIXME: parse rtcp.
  return description;
};

// Generates parts of the SDP media section describing the capabilities /
// parameters.
SDPUtils.writeRtpDescription = function(kind, caps) {
  var sdp = '';

  // Build the mline.
  sdp += 'm=' + kind + ' ';
  sdp += caps.codecs.length > 0 ? '9' : '0'; // reject if no codecs.
  sdp += ' UDP/TLS/RTP/SAVPF ';
  sdp += caps.codecs.map(function(codec) {
    if (codec.preferredPayloadType !== undefined) {
      return codec.preferredPayloadType;
    }
    return codec.payloadType;
  }).join(' ') + '\r\n';

  sdp += 'c=IN IP4 0.0.0.0\r\n';
  sdp += 'a=rtcp:9 IN IP4 0.0.0.0\r\n';

  // Add a=rtpmap lines for each codec. Also fmtp and rtcp-fb.
  caps.codecs.forEach(function(codec) {
    sdp += SDPUtils.writeRtpMap(codec);
    sdp += SDPUtils.writeFmtp(codec);
    sdp += SDPUtils.writeRtcpFb(codec);
  });
  var maxptime = 0;
  caps.codecs.forEach(function(codec) {
    if (codec.maxptime > maxptime) {
      maxptime = codec.maxptime;
    }
  });
  if (maxptime > 0) {
    sdp += 'a=maxptime:' + maxptime + '\r\n';
  }
  sdp += 'a=rtcp-mux\r\n';

  caps.headerExtensions.forEach(function(extension) {
    sdp += SDPUtils.writeExtmap(extension);
  });
  // FIXME: write fecMechanisms.
  return sdp;
};

// Parses the SDP media section and returns an array of
// RTCRtpEncodingParameters.
SDPUtils.parseRtpEncodingParameters = function(mediaSection) {
  var encodingParameters = [];
  var description = SDPUtils.parseRtpParameters(mediaSection);
  var hasRed = description.fecMechanisms.indexOf('RED') !== -1;
  var hasUlpfec = description.fecMechanisms.indexOf('ULPFEC') !== -1;

  // filter a=ssrc:... cname:, ignore PlanB-msid
  var ssrcs = SDPUtils.matchPrefix(mediaSection, 'a=ssrc:')
  .map(function(line) {
    return SDPUtils.parseSsrcMedia(line);
  })
  .filter(function(parts) {
    return parts.attribute === 'cname';
  });
  var primarySsrc = ssrcs.length > 0 && ssrcs[0].ssrc;
  var secondarySsrc;

  var flows = SDPUtils.matchPrefix(mediaSection, 'a=ssrc-group:FID')
  .map(function(line) {
    var parts = line.split(' ');
    parts.shift();
    return parts.map(function(part) {
      return parseInt(part, 10);
    });
  });
  if (flows.length > 0 && flows[0].length > 1 && flows[0][0] === primarySsrc) {
    secondarySsrc = flows[0][1];
  }

  description.codecs.forEach(function(codec) {
    if (codec.name.toUpperCase() === 'RTX' && codec.parameters.apt) {
      var encParam = {
        ssrc: primarySsrc,
        codecPayloadType: parseInt(codec.parameters.apt, 10),
        rtx: {
          ssrc: secondarySsrc
        }
      };
      encodingParameters.push(encParam);
      if (hasRed) {
        encParam = JSON.parse(JSON.stringify(encParam));
        encParam.fec = {
          ssrc: secondarySsrc,
          mechanism: hasUlpfec ? 'red+ulpfec' : 'red'
        };
        encodingParameters.push(encParam);
      }
    }
  });
  if (encodingParameters.length === 0 && primarySsrc) {
    encodingParameters.push({
      ssrc: primarySsrc
    });
  }

  // we support both b=AS and b=TIAS but interpret AS as TIAS.
  var bandwidth = SDPUtils.matchPrefix(mediaSection, 'b=');
  if (bandwidth.length) {
    if (bandwidth[0].indexOf('b=TIAS:') === 0) {
      bandwidth = parseInt(bandwidth[0].substr(7), 10);
    } else if (bandwidth[0].indexOf('b=AS:') === 0) {
      // use formula from JSEP to convert b=AS to TIAS value.
      bandwidth = parseInt(bandwidth[0].substr(5), 10) * 1000 * 0.95
          - (50 * 40 * 8);
    } else {
      bandwidth = undefined;
    }
    encodingParameters.forEach(function(params) {
      params.maxBitrate = bandwidth;
    });
  }
  return encodingParameters;
};

// parses http://draft.ortc.org/#rtcrtcpparameters*
SDPUtils.parseRtcpParameters = function(mediaSection) {
  var rtcpParameters = {};

  var cname;
  // Gets the first SSRC. Note that with RTX there might be multiple
  // SSRCs.
  var remoteSsrc = SDPUtils.matchPrefix(mediaSection, 'a=ssrc:')
      .map(function(line) {
        return SDPUtils.parseSsrcMedia(line);
      })
      .filter(function(obj) {
        return obj.attribute === 'cname';
      })[0];
  if (remoteSsrc) {
    rtcpParameters.cname = remoteSsrc.value;
    rtcpParameters.ssrc = remoteSsrc.ssrc;
  }

  // Edge uses the compound attribute instead of reducedSize
  // compound is !reducedSize
  var rsize = SDPUtils.matchPrefix(mediaSection, 'a=rtcp-rsize');
  rtcpParameters.reducedSize = rsize.length > 0;
  rtcpParameters.compound = rsize.length === 0;

  // parses the rtcp-mux attrіbute.
  // Note that Edge does not support unmuxed RTCP.
  var mux = SDPUtils.matchPrefix(mediaSection, 'a=rtcp-mux');
  rtcpParameters.mux = mux.length > 0;

  return rtcpParameters;
};

// parses either a=msid: or a=ssrc:... msid lines and returns
// the id of the MediaStream and MediaStreamTrack.
SDPUtils.parseMsid = function(mediaSection) {
  var parts;
  var spec = SDPUtils.matchPrefix(mediaSection, 'a=msid:');
  if (spec.length === 1) {
    parts = spec[0].substr(7).split(' ');
    return {stream: parts[0], track: parts[1]};
  }
  var planB = SDPUtils.matchPrefix(mediaSection, 'a=ssrc:')
  .map(function(line) {
    return SDPUtils.parseSsrcMedia(line);
  })
  .filter(function(parts) {
    return parts.attribute === 'msid';
  });
  if (planB.length > 0) {
    parts = planB[0].value.split(' ');
    return {stream: parts[0], track: parts[1]};
  }
};

// Generate a session ID for SDP.
// https://tools.ietf.org/html/draft-ietf-rtcweb-jsep-20#section-5.2.1
// recommends using a cryptographically random +ve 64-bit value
// but right now this should be acceptable and within the right range
SDPUtils.generateSessionId = function() {
  return Math.random().toString().substr(2, 21);
};

// Write boilder plate for start of SDP
// sessId argument is optional - if not supplied it will
// be generated randomly
// sessVersion is optional and defaults to 2
SDPUtils.writeSessionBoilerplate = function(sessId, sessVer) {
  var sessionId;
  var version = sessVer !== undefined ? sessVer : 2;
  if (sessId) {
    sessionId = sessId;
  } else {
    sessionId = SDPUtils.generateSessionId();
  }
  // FIXME: sess-id should be an NTP timestamp.
  return 'v=0\r\n' +
      'o=thisisadapterortc ' + sessionId + ' ' + version + ' IN IP4 127.0.0.1\r\n' +
      's=-\r\n' +
      't=0 0\r\n';
};

SDPUtils.writeMediaSection = function(transceiver, caps, type, stream) {
  var sdp = SDPUtils.writeRtpDescription(transceiver.kind, caps);

  // Map ICE parameters (ufrag, pwd) to SDP.
  sdp += SDPUtils.writeIceParameters(
      transceiver.iceGatherer.getLocalParameters());

  // Map DTLS parameters to SDP.
  sdp += SDPUtils.writeDtlsParameters(
      transceiver.dtlsTransport.getLocalParameters(),
      type === 'offer' ? 'actpass' : 'active');

  sdp += 'a=mid:' + transceiver.mid + '\r\n';

  if (transceiver.direction) {
    sdp += 'a=' + transceiver.direction + '\r\n';
  } else if (transceiver.rtpSender && transceiver.rtpReceiver) {
    sdp += 'a=sendrecv\r\n';
  } else if (transceiver.rtpSender) {
    sdp += 'a=sendonly\r\n';
  } else if (transceiver.rtpReceiver) {
    sdp += 'a=recvonly\r\n';
  } else {
    sdp += 'a=inactive\r\n';
  }

  if (transceiver.rtpSender) {
    // spec.
    var msid = 'msid:' + stream.id + ' ' +
        transceiver.rtpSender.track.id + '\r\n';
    sdp += 'a=' + msid;

    // for Chrome.
    sdp += 'a=ssrc:' + transceiver.sendEncodingParameters[0].ssrc +
        ' ' + msid;
    if (transceiver.sendEncodingParameters[0].rtx) {
      sdp += 'a=ssrc:' + transceiver.sendEncodingParameters[0].rtx.ssrc +
          ' ' + msid;
      sdp += 'a=ssrc-group:FID ' +
          transceiver.sendEncodingParameters[0].ssrc + ' ' +
          transceiver.sendEncodingParameters[0].rtx.ssrc +
          '\r\n';
    }
  }
  // FIXME: this should be written by writeRtpDescription.
  sdp += 'a=ssrc:' + transceiver.sendEncodingParameters[0].ssrc +
      ' cname:' + SDPUtils.localCName + '\r\n';
  if (transceiver.rtpSender && transceiver.sendEncodingParameters[0].rtx) {
    sdp += 'a=ssrc:' + transceiver.sendEncodingParameters[0].rtx.ssrc +
        ' cname:' + SDPUtils.localCName + '\r\n';
  }
  return sdp;
};

// Gets the direction from the mediaSection or the sessionpart.
SDPUtils.getDirection = function(mediaSection, sessionpart) {
  // Look for sendrecv, sendonly, recvonly, inactive, default to sendrecv.
  var lines = SDPUtils.splitLines(mediaSection);
  for (var i = 0; i < lines.length; i++) {
    switch (lines[i]) {
      case 'a=sendrecv':
      case 'a=sendonly':
      case 'a=recvonly':
      case 'a=inactive':
        return lines[i].substr(2);
      default:
        // FIXME: What should happen here?
    }
  }
  if (sessionpart) {
    return SDPUtils.getDirection(sessionpart);
  }
  return 'sendrecv';
};

SDPUtils.getKind = function(mediaSection) {
  var lines = SDPUtils.splitLines(mediaSection);
  var mline = lines[0].split(' ');
  return mline[0].substr(2);
};

SDPUtils.isRejected = function(mediaSection) {
  return mediaSection.split(' ', 2)[1] === '0';
};

SDPUtils.parseMLine = function(mediaSection) {
  var lines = SDPUtils.splitLines(mediaSection);
  var mline = lines[0].split(' ');
  return {
    kind: mline[0].substr(2),
    port: parseInt(mline[1], 10),
    protocol: mline[2],
    fmt: mline.slice(3).join(' ')
  };
};

// Expose public methods.
if (true) {
  module.exports = SDPUtils;
}


/***/ }),
/* 29 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var __decorate = undefined && undefined.__decorate || function (decorators, target, key, desc) {
    var c = arguments.length,
        r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc,
        d;
    if ((typeof Reflect === "undefined" ? "undefined" : _typeof(Reflect)) === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);else for (var i = decorators.length - 1; i >= 0; i--) {
        if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    }return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var MsgSignaling_1 = __webpack_require__(25);
var GUID_1 = __webpack_require__(27);
var Logger_1 = __webpack_require__(0);
var Authenticator_1 = __webpack_require__(10);
var MsgEnums_1 = __webpack_require__(26);
/**
 * Messaging allows exchanging instant messages between 2 or more participants.
 * Messaging supports text and metadata. The conversation doesn't bind or depend on the audio/video calls, but there is a possibility to integrate messaging in audio/video calls.
 *
 * FEATURES:
 * <ul>
 *  <li>
 *      1. messaging is the separate part of WEB SDK but it uses <a href="https://voximplant.com/docs/references/websdk/classes/client.html">VoxImplant.Client.login*</a> methods - in brief, if a user was already logged in he can use messaging functionality.
 *  </li>
 *  <li>
 *      2. messaging doesn't use backend JS scenario at all
 *  </li>
 * </ul>
 *
 * See the minimum example to create messaging and to be able start a conversation:
 * <script src="https://gist.github.com/irbisadm/0d7ca7d27d73d6466e2925e867e613ae.js"></script>
 *
 */
var Messaging;
(function (Messaging) {
    /**
     * Conversation instance. Created by Messenger.createConversation(). Used to send messages, add or remove users, change moderators list etc.
     */
    var Conversation = function () {
        /**
         * @hidden
         */
        function Conversation(participants, distinct, publicJoin, customData, moderators) {
            _classCallCheck(this, Conversation);

            this._distinct = distinct;
            this._publicJoin = publicJoin;
            this._participants = participants;
            this._customData = customData;
            this._moderators = moderators;
        }
        /**
         * Returns sequence of last event that was read by user. Used to display unread messages, events etc.
         * @returns {any}
         */


        _createClass(Conversation, [{
            key: "_getPayload",

            /**
             *
             * @hidden
             */
            value: function _getPayload() {
                if (typeof this._uuid == "undefined") throw Error('You must create UUID with createUUID() function!');
                var str = {
                    uuid: this._uuid,
                    participants: this._prepareParticipants(this._participants)
                };
                if (typeof this._title != "undefined") str['title'] = this._title;else str['title'] = '';
                if (typeof this._moderators != "undefined") str['moderators'] = this._moderators;else str['moderators'] = [];
                if (typeof this._lastRead != "undefined") str['last_readed'] = this._lastRead;
                if (typeof this._distinct != "undefined") str['distinct'] = this._distinct;else str['distinct'] = false;
                if (typeof this._publicJoin != "undefined") str['enable_public_join'] = this._publicJoin;else str['enable_public_join'] = false;
                if (typeof this._customData != "undefined") str['custom_data'] = this._customData;else str['custom_data'] = {};
                if (typeof this._createdAt != "undefined") str['created_at'] = this._createdAt;
                if (typeof this._createdAt != "undefined") str['uber_conversation'] = this._uberConversation;
                return str;
            }
            /**
             *
             * @hidden
             */

        }, {
            key: "_getSimplePayload",
            value: function _getSimplePayload() {
                if (typeof this._uuid == "undefined") throw Error('You must create UUID with createUUID() function!');
                return {
                    uuid: this._uuid,
                    title: typeof this._title != "undefined" ? this._title : '',
                    distinct: typeof this._distinct != "undefined" ? this._distinct : false,
                    enable_public_join: typeof this._publicJoin != "undefined" ? this._publicJoin : false,
                    custom_data: typeof this._customData != "undefined" ? this._customData : {}
                };
            }
            /**
             * Correction participants list for backend
             * @returns {Array}
             * @hidden
             */

        }, {
            key: "_prepareParticipants",
            value: function _prepareParticipants(participants) {
                var ret = [];
                var _iteratorNormalCompletion = true;
                var _didIteratorError = false;
                var _iteratorError = undefined;

                try {
                    for (var _iterator = participants[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                        var participant = _step.value;

                        if (typeof participant.userId != "undefined") {
                            var item = { user_id: ConversationManager.extractUserName(participant.userId) };
                            item['can_write'] = typeof participant.canWrite == "undefined" ? true : participant.canWrite;
                            item['can_manage_participants'] = typeof participant.canManageParticipants == "undefined" ? false : participant.canManageParticipants;
                            ret.push(item);
                        }
                    }
                } catch (err) {
                    _didIteratorError = true;
                    _iteratorError = err;
                } finally {
                    try {
                        if (!_iteratorNormalCompletion && _iterator.return) {
                            _iterator.return();
                        }
                    } finally {
                        if (_didIteratorError) {
                            throw _iteratorError;
                        }
                    }
                }

                return ret;
            }
            /**
             * Generate UUID for new conversation
             *
             * @hidden
             */

        }, {
            key: "_createUUID",
            value: function _createUUID() {
                if (typeof this._uuid != "undefined") throw Error('UUID already created!');
                this._uuid = new GUID_1.GUID().toString();
            }
            /**
             * Create conversation from buss
             * @param busConversation
             * @hidden
             */

        }, {
            key: "toCache",

            /**
             * Serialize conversation so it can be stored into some storage (like IndexedDB) and later restored via <a href='https://voximplant.com/docs/references/websdk/classes/messaging.messenger.html#createconversationfromcache'>Messenger.createConversationFromCache</a>
             * @returns {SerializedConversation}
             */
            value: function toCache() {
                return {
                    uuid: this._uuid,
                    seq: this._lastSeq,
                    lastUpdate: this._lastUpdate,
                    moderators: this._moderators,
                    title: this._title,
                    createdAt: this._createdAt,
                    lastRead: this._lastRead,
                    distinct: this._distinct,
                    publicJoin: this._publicJoin,
                    participants: this._participants,
                    customData: this._customData
                };
            }
            /**
             * Sets current conversation title. Note that setting this property does not send changes to the server. Use the 'update' to send all changes at once or 'setTitle' to update and set the title.
             * @param value
             */

        }, {
            key: "sendMessage",

            //==============msg part============
            value: function sendMessage(message, payload) {
                var msg = new Message(message, payload);
                msg.sendTo(this);
                return msg;
            }
            /**
             * Calling this method will inform backend that user is typing some text. Calls within 10s interval from the last call are discarded.
             * @returns {boolean} 'true' is message was actually sent, 'false' if it was discarded.
             */

        }, {
            key: "typing",
            value: function typing() {
                var _this = this;

                if (this._debounceLock) return false;
                setTimeout(function () {
                    _this._debounceLock = false;
                }, 10000);
                this._debounceLock = true;
                MsgSignaling_1.MsgSignaling.get().sendPayload(MsgEnums_1.MsgAction.typingMessage, { conversation: this._uuid });
                return true;
            }
            /**
             * Mark the event with the specified sequence as 'read'. This affects 'lastRead' and is used to display unread messages and events. Triggers the <a href='https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#read'>Read</a> event for all messenger objects on all connected clients, including this one.
             * @param seq
             */

        }, {
            key: "markAsRead",
            value: function markAsRead(seq) {
                MsgSignaling_1.MsgSignaling.get().sendPayload(MsgEnums_1.MsgAction.isRead, { conversation: this._uuid, seq: seq });
                this._lastRead = seq;
            }
            /**
             * Mark event as handled by current logged-in device. If single user is logged in on multiple devices, this can be used to display delivery status by subscribing to the <a href='https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#delivered'>Delivered</a> event.
             * @param seq
             */

        }, {
            key: "markAsDelivered",
            value: function markAsDelivered(seq) {
                MsgSignaling_1.MsgSignaling.get().sendPayload(MsgEnums_1.MsgAction.isDelivered, { conversation: this._uuid, seq: seq });
            }
            /**
             * Remove current conversation. All participants, including this one, will receive the <a href='https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#removeconversation'>RemoveConversation</a> event.
             */

        }, {
            key: "remove",
            value: function remove() {
                ConversationManager.get().removeConversation(this._uuid);
            }
            /**
             * Send conversation changes to the server: title, public join flag, distinct flag and custom data. Used to send all changes modified via properties. Changes via 'setTitle', 'setPublicJoin' etc are sent instantly.
             */

        }, {
            key: "update",
            value: function update() {
                ConversationManager.get().editConversation(this);
            }
            /**
             * Set the conversation title and send changes to the server.
             */

        }, {
            key: "setTitle",
            value: function setTitle(title) {
                this._title = title;
                ConversationManager.get().editConversation(this);
            }
            /**
             * Set the public join flag and send changes to the server.
             */

        }, {
            key: "setPublicJoin",
            value: function setPublicJoin(publicJoin) {
                this._publicJoin = publicJoin;
                ConversationManager.get().editConversation(this);
            }
            /**
             * Set the distinct flag and send changes to the server.
             */

        }, {
            key: "setDistinct",
            value: function setDistinct(distinct) {
                this._distinct = distinct;
                ConversationManager.get().editConversation(this);
            }
            /**
             * Set the JS object custom data and send changes to the server.
             */

        }, {
            key: "setCustomData",
            value: function setCustomData(customData) {
                this._customData = customData;
                ConversationManager.get().editConversation(this);
            }
            /**
             * Add new participants to the conversation.
             * Duplicated users are ignored.
             * Will fail if any user does not exist.
             * Triggers the <a href='https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#editconversation'>EditConversation</a>
             * event for all messenger objects on all clients, including this one.
             * @param participants
             * @returns {Promise<EditConversation>|Promise}
             */

        }, {
            key: "addParticipants",
            value: function addParticipants(participants) {
                var _this2 = this;

                return new Promise(function (resolve, reject) {
                    if (participants.length == 0) reject();
                    MsgSignaling_1.MsgSignaling.get().sendPayload(MsgEnums_1.MsgAction.addParticipants, { uuid: _this2._uuid, participants: _this2._prepareParticipants(participants) });
                    Messenger.getInstance()._registerPromise(MessengerEvents.EditConversation, resolve, reject);
                });
            }
            /**
             * Change access rights for the existing participants.
             * This function doesn't apply any changes to the participant list.
             * Use <a href="#addparticipant">addParticipant</a> or <a href="#removeparticipant>removeParticipant</a> instead.
             * Triggers the <a href='https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#editconversation'>EditConversation</a>
             * event for all messenger objects on all clients, including this one.
             * @param participants
             * @returns {Promise<EditConversation>|Promise}
             */

        }, {
            key: "editParticipants",
            value: function editParticipants(participants) {
                var _this3 = this;

                return new Promise(function (resolve, reject) {
                    if (participants.length == 0) reject();
                    MsgSignaling_1.MsgSignaling.get().sendPayload(MsgEnums_1.MsgAction.editParticipants, { uuid: _this3._uuid, participants: _this3._prepareParticipants(participants) });
                    Messenger.getInstance()._registerPromise(MessengerEvents.EditConversation, resolve, reject);
                });
            }
            /**
             * Remove participants from the conversation.
             * Duplicated users are ignored.
             * Will fail if any user does not exist.
             * Triggers the <a href='https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#editconversation'>EditConversation</a>
             * event for all messenger objects on all clients, including this one.
             * @param participants
             * @returns {Promise<EditConversation>|Promise}
             */

        }, {
            key: "removeParticipants",
            value: function removeParticipants(participants) {
                var _this4 = this;

                return new Promise(function (resolve, reject) {
                    if (participants.length == 0) reject();
                    MsgSignaling_1.MsgSignaling.get().sendPayload(MsgEnums_1.MsgAction.removeParticipants, { uuid: _this4._uuid, participants: participants.map(function (item) {
                            if (typeof item.userId !== "undefined") return item.userId;
                        }) });
                    Messenger.getInstance()._registerPromise(MessengerEvents.EditConversation, resolve, reject);
                });
            }
            /**
             * Add new moderators to the conversation.
             * Duplicated users are ignored.
             * Will fail if any user does not exist.
             * Triggers the <a href='https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#editconversation'>EditConversation</a>
             * event for all messenger objects on all clients, including this one.
             * @param participants
             * @returns {Promise<EditConversation>|Promise}
             */

        }, {
            key: "addModerators",
            value: function addModerators(moderators) {
                var _this5 = this;

                return new Promise(function (resolve, reject) {
                    if (moderators.length == 0) reject();
                    MsgSignaling_1.MsgSignaling.get().sendPayload(MsgEnums_1.MsgAction.addModerators, { uuid: _this5._uuid, moderators: moderators });
                    Messenger.getInstance()._registerPromise(MessengerEvents.EditConversation, resolve, reject);
                });
            }
            /**
             * Remove moderators from the conversation.
             * Duplicated users are ignored.
             * Will fail if any user does not exist.
             * Triggers the <a href='https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#editconversation'>EditConversation</a>
             * event for all messenger objects on all clients, including this one.
             * @param participants
             * @returns {Promise<EditConversation>|Promise}
             */

        }, {
            key: "removeModerators",
            value: function removeModerators(moderators) {
                var _this6 = this;

                return new Promise(function (resolve, reject) {
                    if (moderators.length == 0) reject();
                    MsgSignaling_1.MsgSignaling.get().sendPayload(MsgEnums_1.MsgAction.removeModerators, { uuid: _this6._uuid, moderators: moderators });
                    Messenger.getInstance()._registerPromise(MessengerEvents.EditConversation, resolve, reject);
                });
            }
            /**
             * Request events in the specified sequence range to be sent from server into this client. Used to get history or get missed events in case of network disconnect.
             * Please note that server will not push any events that was missed due to the client being offline. Client should use this method to request all events based
             * on the last event sequence received from the server and last event sequence saved locally (if any).
             * @param eventsFrom first event in range sequence, inclusive
             * @param eventsTo last event in range sequence, inclusive
             */

        }, {
            key: "retransmitEvents",
            value: function retransmitEvents(eventsFrom, eventsTo) {
                eventsFrom = eventsFrom | 0;
                eventsTo = eventsTo | 0;
                MsgSignaling_1.MsgSignaling.get().sendPayload(MsgEnums_1.MsgAction.retransmitEvents, { uuid: this._uuid, eventsFrom: eventsFrom, eventsTo: eventsTo });
            }
            /**
             * @hidden
             * @param newSeq
             */

        }, {
            key: "updateSeq",
            value: function updateSeq(newSeq) {
                if (newSeq > this._lastSeq) {
                    this._lastSeq = newSeq;
                }
                this._lastUpdate = Date.now() / 1000 | 0;
            }
            /**
             * @hidden
             * @return {string}
             * @private
             */

        }, {
            key: "_traceName",
            value: function _traceName() {
                return 'Conversation';
            }
        }, {
            key: "lastRead",
            get: function get() {
                return this._lastRead;
            }
            /**
             * @hidden
             * @return {boolean}
             */

        }, {
            key: "uberConversation",
            get: function get() {
                return this._uberConversation;
            }
            /**
             * Universally unique identifier of current conversation. Used in methods like 'get', 'remove' etc.
             * @returns {string}
             */

        }, {
            key: "uuid",
            get: function get() {
                return this._uuid;
            }
        }, {
            key: "title",
            set: function set(value) {
                this._title = value;
            }
            /**
             * If two conversations are created with same set of users and moderators and both have 'distinct' flag, second create call will fail with the UUID of conversation already created. Note that changing users or moderators list will clear 'distinct' flag.
             * Note that setting this property does not send changes to the server. Use the 'update' to send all changes at once or 'setDistinct' to update and set the distinct flag.
             * @param value
             */
            ,
            get: function get() {
                return this._title;
            }
        }, {
            key: "distinct",
            set: function set(value) {
                this._distinct = value;
            }
            /**
             * If set to 'true', anyone can join conversation by UUID. Note that setting this property does not send changes to the server. Use the 'update' to send all changes at once or 'setPublicJoin' to update and set the public join flag.
             * @param value
             */
            ,
            get: function get() {
                return this._distinct;
            }
        }, {
            key: "publicJoin",
            set: function set(value) {
                this._publicJoin = value;
            }
            /**
             * JavaScript object with custom data, up to 5kb. Note that setting this property does not send changes to the server. Use the 'update' to send all changes at once or 'setCustomData' to update and set the custom data.
             * @param value
             */
            ,
            get: function get() {
                return this._publicJoin;
            }
            /**
             * Conversation participants list alongside with their rights.
             */

        }, {
            key: "customData",
            set: function set(value) {
                this._customData = value;
            }
            /**
             * Conversation moderator names list.
             */
            ,
            get: function get() {
                return this._customData;
            }
            /**
             * Last event sequence for this conversation. Used with 'lastRead' to display unread messages and events.
             */

        }, {
            key: "moderators",
            get: function get() {
                return this._moderators;
            }
        }, {
            key: "createdAt",
            get: function get() {
                return this._createdAt;
            }
        }, {
            key: "participants",
            get: function get() {
                return this._participants;
            }
        }, {
            key: "lastSeq",
            get: function get() {
                return this._lastSeq;
            }
            /**
             * UNIX timestamp integer that specifies the time of the last event in the conversation. It's same as 'Date.now()' divided by 1000.
             */

        }, {
            key: "lastUpdate",
            get: function get() {
                return this._lastUpdate;
            }
        }], [{
            key: "_createFromBus",
            value: function _createFromBus(busConversation, seq) {
                var conversation = new Conversation([]);
                conversation._lastSeq = seq;
                conversation._uuid = busConversation.uuid;
                conversation._title = busConversation.title;
                conversation._moderators = busConversation.moderators;
                conversation._createdAt = busConversation.created_at;
                conversation._lastRead = busConversation.last_read;
                conversation._distinct = busConversation.distinct;
                conversation._publicJoin = busConversation.public_join;
                conversation._uberConversation = busConversation.uber_conversation;
                if (busConversation.participants) conversation._participants = busConversation.participants.map(function (item) {
                    return {
                        userId: item.user_id,
                        canWrite: item.can_write,
                        canManageParticipants: item.can_manage_participants
                    };
                });
                if (busConversation.custom_data) conversation._customData = busConversation.custom_data;
                conversation._lastUpdate = busConversation.last_update;
                return conversation;
            }
            /**
             * Restore conversation from cache
             * @param cacheConversation
             * @returns {Conversation}
             * @hidden
             */

        }, {
            key: "createFromCache",
            value: function createFromCache(cacheConversation) {
                var conversation = new Conversation([]);
                conversation._uuid = cacheConversation.uuid;
                conversation._lastSeq = cacheConversation.seq;
                conversation._lastUpdate = cacheConversation.lastUpdate;
                conversation._title = cacheConversation.title;
                conversation._moderators = cacheConversation.moderators;
                conversation._createdAt = cacheConversation.createdAt;
                conversation._lastRead = cacheConversation.lastRead;
                conversation._distinct = cacheConversation.distinct;
                conversation._publicJoin = cacheConversation.publicJoin;
                conversation._participants = cacheConversation.participants;
                conversation._customData = cacheConversation.customData;
                conversation._uberConversation = cacheConversation.uberConversation;
                return conversation;
            }
        }]);

        return Conversation;
    }();

    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Conversation.prototype, "_getPayload", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Conversation.prototype, "_getSimplePayload", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Conversation.prototype, "_prepareParticipants", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Conversation.prototype, "_createUUID", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Conversation.prototype, "toCache", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Conversation.prototype, "markAsRead", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Conversation.prototype, "markAsDelivered", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Conversation.prototype, "remove", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Conversation.prototype, "update", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Conversation.prototype, "addParticipants", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Conversation.prototype, "editParticipants", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Conversation.prototype, "removeParticipants", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Conversation.prototype, "addModerators", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Conversation.prototype, "removeModerators", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Conversation.prototype, "retransmitEvents", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Conversation.prototype, "updateSeq", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Conversation, "_createFromBus", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Conversation, "createFromCache", null);
    Messaging.Conversation = Conversation;

    var ConversationManager = function () {
        function ConversationManager() {
            var _this7 = this;

            _classCallCheck(this, ConversationManager);

            if (ConversationManager.instance) throw new Error("Error - use ConversationManager.get()");
            this.signalling = MsgSignaling_1.MsgSignaling.get();
            this.awaitingConversations = {};
            this.signalling.addEventListener(MsgEnums_1.MsgEvent.onEditConversation, function (payload) {
                _this7.resolveEvent(payload, MessengerEvents.EditConversation);
            });
            this.signalling.addEventListener(MsgEnums_1.MsgEvent.onGetConversation, function (payload) {
                _this7.resolveEvent(payload, MessengerEvents.GetConversation);
            });
            this.signalling.addEventListener(MsgEnums_1.MsgEvent.onRemoveConversation, function (payload) {
                _this7.resolveEvent(payload, MessengerEvents.RemoveConversation);
            });
            this.signalling.addEventListener(MsgEnums_1.MsgEvent.onCreateConversation, function (payload) {
                _this7.resolveEvent(payload, MessengerEvents.CreateConversation);
            });
            this.signalling.addEventListener(MsgEnums_1.MsgEvent.onSendMessage, function (payload) {
                _this7.resolveMessageEvent(payload, MessengerEvents.SendMessage);
            });
            this.signalling.addEventListener(MsgEnums_1.MsgEvent.onEditMessage, function (payload) {
                _this7.resolveMessageEvent(payload, MessengerEvents.EditMessage);
            });
            this.signalling.addEventListener(MsgEnums_1.MsgEvent.onRemoveMessage, function (payload) {
                _this7.resolveMessageEvent(payload, MessengerEvents.RemoveMessage);
            });
            this.signalling.addEventListener(MsgEnums_1.MsgEvent.isRead, function (payload) {
                var realPayload = payload.object;
                Messenger.getInstance()._dispatchEvent(MessengerEvents.Read, {
                    conversation: realPayload.conversation,
                    timestamp: new Date(realPayload.timestamp * 1000),
                    userId: payload.user_id,
                    seq: realPayload.seq,
                    onIncomingEvent: payload.on_incoming_event,
                    name: MessengerEvents[MessengerEvents.Read]
                });
            });
            this.signalling.addEventListener(MsgEnums_1.MsgEvent.isDelivered, function (payload) {
                var realPayload = payload.object;
                Messenger.getInstance()._dispatchEvent(MessengerEvents.Delivered, {
                    conversation: realPayload.conversation,
                    timestamp: new Date(realPayload.timestamp * 1000),
                    userId: payload.user_id,
                    seq: realPayload.seq,
                    onIncomingEvent: payload.on_incoming_event,
                    name: MessengerEvents[MessengerEvents.Delivered]
                });
            });
            this.signalling.addEventListener(MsgEnums_1.MsgEvent.onTyping, function (payload) {
                Messenger.getInstance()._dispatchEvent(MessengerEvents.Typing, {
                    name: MessengerEvents[MessengerEvents.Typing],
                    conversation: payload.object.conversation,
                    userId: payload.user_id,
                    onIncomingEvent: payload.on_incoming_event
                });
            });
            this.signalling.addEventListener(MsgEnums_1.MsgEvent.onRetransmitEvents, function (payload) {
                Messenger.getInstance()._dispatchEvent(MessengerEvents.RetransmitEvents, {
                    events: payload.object.map(function (item) {
                        if (item.event) {
                            if (item.event.indexOf('Message') == -1) return {
                                name: item.event,
                                conversation: Conversation._createFromBus(item.payload.object, item.payload.seq),
                                userId: item.payload.user_id,
                                seq: item.payload.seq,
                                onIncomingEvent: item.payload.on_incoming_event
                            };else return {
                                name: item.event,
                                message: Message._createFromBus(item.payload.object, item.payload.seq),
                                userId: item.payload.user_id,
                                seq: item.payload.seq,
                                onIncomingEvent: item.payload.on_incoming_event
                            };
                        }
                    }),
                    userId: payload.user_id,
                    seq: payload.seq,
                    from: payload.from,
                    to: payload.to,
                    onIncomingEvent: payload.on_incoming_event
                });
            });
            this._converasationList = [];
        }
        /**
         * Resolve Event
         * @param payload
         * @param seq
         * @param realEvent
         */


        _createClass(ConversationManager, [{
            key: "resolveEvent",
            value: function resolveEvent(payload, realEvent) {
                if (MessengerEvents[realEvent] === MessengerEvents[MessengerEvents.RemoveConversation]) {
                    var payloadObject = payload.object;
                    Messenger.getInstance()._dispatchEvent(realEvent, {
                        name: MessengerEvents[realEvent],
                        uuid: payloadObject.uuid,
                        userId: payload.user_id,
                        seq: payload.seq,
                        onIncomingEvent: payload.on_incoming_event
                    });
                    return;
                }
                var conversation = Conversation._createFromBus(payload.object, payload.seq);
                this.registerConversation(conversation);
                if (typeof conversation != "undefined") conversation.updateSeq(payload.seq);
                Messenger.getInstance()._dispatchEvent(realEvent, {
                    name: MessengerEvents[realEvent],
                    conversation: conversation,
                    userId: payload.user_id,
                    seq: payload.seq,
                    onIncomingEvent: payload.on_incoming_event
                });
                //resolve awaiting conversation events, such new message
                if (realEvent === MessengerEvents.GetConversation && typeof this.awaitingConversations[conversation.uuid] !== "undefined") {
                    this.awaitingConversations[conversation.uuid](conversation);
                    delete this.awaitingConversations[conversation.uuid];
                }
            }
            /**
             * Resolve message Event
             * @param payload
             * @param seq
             * @param realEvent
             */

        }, {
            key: "resolveMessageEvent",
            value: function resolveMessageEvent(payload, realEvent) {
                var message = Message._createFromBus(payload.object, payload.seq);
                if (typeof this._converasationList[message.conversation] != "undefined") this._converasationList[message.conversation].updateSeq(payload.seq);
                Messenger.getInstance()._dispatchEvent(realEvent, {
                    name: MessengerEvents[realEvent],
                    message: message,
                    userId: payload.user_id,
                    seq: payload.seq,
                    onIncomingEvent: payload.on_incoming_event
                });
            }
        }, {
            key: "createConversation",

            /**
             * Create new conversation
             * @param participants
             * @param title
             * @param distinct
             * @param enablePublicJoin
             * @param customData
             * @returns {Promise<Conversation>|Promise}
             */
            value: function createConversation(participants, title, distinct, enablePublicJoin, customData, moderators) {
                var newConversation = new Conversation(participants, distinct, enablePublicJoin, customData, moderators);
                newConversation.title = title;
                newConversation._createUUID();
                this.signalling.sendPayload(MsgEnums_1.MsgAction.createConversation, newConversation._getPayload());
            }
            /**
             * Add conversation to conversation list and database
             * @param conversation
             */

        }, {
            key: "registerConversation",
            value: function registerConversation(conversation) {
                this._converasationList.filter(function (item) {
                    return item.uuid !== conversation.uuid;
                });
                this._converasationList.push(conversation);
            }
            /**
             * Remove conversation
             * @param uuid
             * @returns {Promise<Conversation>|Promise}
             */

        }, {
            key: "removeConversation",
            value: function removeConversation(uuid) {
                this.signalling.sendPayload(MsgEnums_1.MsgAction.removeConversation, { uuid: uuid });
            }
            /**
             * Edit conversation
             * @param conversation
             * @returns {Promise<Conversation>|Promise}
             */

        }, {
            key: "editConversation",
            value: function editConversation(conversation) {
                this.signalling.sendPayload(MsgEnums_1.MsgAction.editConversation, conversation._getSimplePayload());
            }
            /**
             * Return conversation from memory. If not exist, or "force" set to true - get conversation from backend
             * @param uuid
             * @returns {Promise<Conversation>|Promise}
             */

        }, {
            key: "getConversation",
            value: function getConversation(uuid) {
                this.signalling.sendPayload(MsgEnums_1.MsgAction.getConversation, { uuid: uuid });
            }
        }, {
            key: "getConversationByUUID",
            value: function getConversationByUUID(uuid) {
                var _this8 = this;

                return new Promise(function (resolve, reject) {
                    var _iteratorNormalCompletion2 = true;
                    var _didIteratorError2 = false;
                    var _iteratorError2 = undefined;

                    try {
                        for (var _iterator2 = _this8._converasationList[Symbol.iterator](), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
                            var conversation = _step2.value;

                            if (conversation.uuid === uuid) {
                                Messenger.getInstance()._dispatchEvent(MessengerEvents.GetConversation, { conversation: conversation });
                                resolve(conversation);
                                return;
                            }
                        }
                    } catch (err) {
                        _didIteratorError2 = true;
                        _iteratorError2 = err;
                    } finally {
                        try {
                            if (!_iteratorNormalCompletion2 && _iterator2.return) {
                                _iterator2.return();
                            }
                        } finally {
                            if (_didIteratorError2) {
                                throw _iteratorError2;
                            }
                        }
                    }

                    _this8.awaitingConversations[uuid] = resolve;
                });
            }
            /**
             * Deserialize conversation from disc cache
             * @hidden
             * @param value
             * @returns {Conversation}
             */

        }, {
            key: "getConversations",
            value: function getConversations(conversations) {
                this.signalling.sendPayload(MsgEnums_1.MsgAction.getConversations, { uuids: conversations });
            }
            /**
             * @hidden
             * @return {string}
             * @private
             */

        }, {
            key: "_traceName",
            value: function _traceName() {
                return 'ConversationManager';
            }
        }], [{
            key: "get",
            value: function get() {
                ConversationManager.instance = ConversationManager.instance || new ConversationManager();
                return ConversationManager.instance;
            }
            /**
             * Remove custom domain ending
             * @param username
             * @returns {string}
             */

        }, {
            key: "extractUserName",
            value: function extractUserName(username) {
                var userParts = username.split('@');
                userParts[1] = userParts[1].split('.').splice(0, 2).join('.');
                return userParts.join('@');
            }
        }, {
            key: "deserialize",
            value: function deserialize(value) {
                return Conversation.createFromCache(value);
            }
            /**
             * Serialize conversation for disc storage
             * @param conversation
             * @returns {SerializedConversation}
             */

        }, {
            key: "serialize",
            value: function serialize(conversation) {
                return conversation.toCache();
            }
        }]);

        return ConversationManager;
    }();

    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], ConversationManager.prototype, "resolveEvent", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], ConversationManager.prototype, "resolveMessageEvent", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], ConversationManager.prototype, "createConversation", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], ConversationManager.prototype, "registerConversation", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], ConversationManager.prototype, "removeConversation", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], ConversationManager.prototype, "editConversation", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], ConversationManager.prototype, "getConversation", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], ConversationManager.prototype, "getConversationByUUID", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], ConversationManager.prototype, "getConversations", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], ConversationManager, "get", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], ConversationManager, "extractUserName", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], ConversationManager, "deserialize", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], ConversationManager, "serialize", null);
    /**
     * Describes single message. Received via the 'onSendMessage' or 'onEditMessage' events and used to serialize or edit the message.
     */

    var Message = function () {
        function Message(message, payload) {
            _classCallCheck(this, Message);

            this._text = message;
            this._payload = payload;
            this._uuid = new GUID_1.GUID().toString();
        }
        /**
         * @hidden
         * @param conversation
         */


        _createClass(Message, [{
            key: "sendTo",
            value: function sendTo(conversation) {
                this._conversation = conversation.uuid;
                MsgSignaling_1.MsgSignaling.get().sendPayload(MsgEnums_1.MsgAction.sendMessage, this.getPayload());
            }
            /**
             * @hidden
             * @returns {{uuid: string, text: string, conversation: string}}
             */

        }, {
            key: "getPayload",
            value: function getPayload() {
                var str = {
                    uuid: this._uuid,
                    text: this._text,
                    conversation: this._conversation
                };
                if (typeof this._payload !== "undefined") str['payload'] = this._payload;else str['payload'] = [];
                return str;
            }
            /**
             * Universally unique identifier of message. Can be used on client side for housekeeping.
             * @returns {string}
             */

        }, {
            key: "update",

            /**
             * Sends text and payload changes to the server.
             */
            value: function update() {
                MsgSignaling_1.MsgSignaling.get().sendPayload(MsgEnums_1.MsgAction.editMessage, this.getPayload());
            }
            /**
             * Remove the message.
             * Triggers the <a href='https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#removemessage'>RemoveMessage</a>
             * event for all messenger objects on all clients, including this one.
             */

        }, {
            key: "remove",
            value: function remove() {
                MsgSignaling_1.MsgSignaling.get().sendPayload(MsgEnums_1.MsgAction.removeMessage, { uuid: this._uuid, conversation: this.conversation });
            }
            /**
             * Message sequence number.
             */

        }, {
            key: "toCache",

            /**
             * Serialize message so it can be stored into some storage (like IndexedDB) and later restored via <a href='https://voximplant.com/docs/references/websdk/classes/messaging.messenger.html#createmessagefromcache'>Messenger.createMessageFromCache</a>
             */
            value: function toCache() {
                return {
                    seq: this._seq,
                    uuid: this._uuid,
                    text: this._text,
                    payload: this._payload,
                    conversation: this._conversation,
                    sender: this._sender
                };
            }
            /**
             * @hidden
             * @return {string}
             * @private
             */

        }, {
            key: "_traceName",
            value: function _traceName() {
                return 'Message';
            }
        }, {
            key: "uuid",
            get: function get() {
                return this._uuid;
            }
            /**
             * UUID of the conversation this message belongs to.
             */

        }, {
            key: "conversation",
            get: function get() {
                return this._conversation;
            }
            /**
             * Message text.
             */

        }, {
            key: "text",
            get: function get() {
                return this._text;
            },
            set: function set(value) {
                this._text = value;
            }
            /**
             * Array of 'Payload' objects associated with the message.
             */

        }, {
            key: "payload",
            get: function get() {
                return this._payload;
            },
            set: function set(value) {
                this._payload = value;
            }
        }, {
            key: "seq",
            get: function get() {
                return this._seq;
            }
            //FIXME: remove!

        }, {
            key: "sender",
            get: function get() {
                return this._sender;
            }
            /**
             * Create message from bus
             * @param busMessage
             * @param seq
             * @hidden
             */

        }], [{
            key: "_createFromBus",
            value: function _createFromBus(busMessage, seq) {
                var message = new Message(busMessage.text, busMessage.payload);
                message._uuid = busMessage.uuid;
                message._conversation = busMessage.conversation;
                message._sender = busMessage.sender;
                message._seq = seq;
                return message;
            }
            /**
             * @hidden
             * @param cacheMessage
             * @returns {Message}
             */

        }, {
            key: "createFromCache",
            value: function createFromCache(cacheMessage) {
                var message = new Message(cacheMessage.text, cacheMessage.payload);
                message._uuid = cacheMessage.uuid;
                message._conversation = cacheMessage.conversation;
                message._sender = cacheMessage.sender;
                message._seq = cacheMessage.seq;
                return message;
            }
        }]);

        return Message;
    }();

    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Message.prototype, "sendTo", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Message.prototype, "getPayload", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Message.prototype, "update", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Message.prototype, "remove", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Message.prototype, "toCache", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Message, "_createFromBus", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Message, "createFromCache", null);
    Messaging.Message = Message;
    /**
     * Messenger class used to control messaging functions. Can't be instantiated directly (singleton), please use <a href="../globals.html#getmessenger">VoxImplant.getMessenger</a> to get the class instance.
     */

    var Messenger = function () {
        /**
         * @hidden
         */
        function Messenger() {
            var _this9 = this;

            _classCallCheck(this, Messenger);

            if (Messenger.instance) {
                throw new Error("Error - use Client.getIM()");
            }
            this.eventListeners = {};
            this.signalling = MsgSignaling_1.MsgSignaling.get();
            this.cm = ConversationManager.get();
            this.signalling.addEventListener(MsgEnums_1.MsgEvent.onError, function (payload) {
                _this9._dispatchEvent(MessengerEvents.Error, payload);
            });
            ConversationManager.get();
            this.signalling.addEventListener(MsgEnums_1.MsgEvent.onEditUser, function (payload) {
                var eventPayload = payload.object;
                var checkedPayload = {
                    user: {
                        customData: eventPayload.custom_data,
                        privateCustomData: eventPayload.private_custom_data,
                        userId: eventPayload.user_id
                    },
                    userId: payload.user_id,
                    seq: payload.seq,
                    onIncomingEvent: payload.on_incoming_event
                };
                _this9._dispatchEvent(MessengerEvents.EditUser, checkedPayload);
            });
            this.signalling.addEventListener(MsgEnums_1.MsgEvent.onGetUser, function (payload) {
                var eventPayload = payload.object;
                var checkedPayload = {
                    user: {
                        conversationsList: eventPayload.conversations_list,
                        leaveConversationList: eventPayload.leave_conversation_list,
                        customData: eventPayload.custom_data,
                        privateCustomData: eventPayload.private_custom_data,
                        userId: eventPayload.user_id
                    },
                    userId: payload.user_id,
                    seq: payload.seq,
                    onIncomingEvent: payload.on_incoming_event
                };
                _this9._dispatchEvent(MessengerEvents.GetUser, checkedPayload);
            });
            this.signalling.addEventListener(MsgEnums_1.MsgEvent.onsubscribe, function (payload) {
                _this9._dispatchEvent(MessengerEvents.Subscribe, { users: payload.users });
            });
            this.signalling.addEventListener(MsgEnums_1.MsgEvent.onUnSubscribe, function (payload) {
                _this9._dispatchEvent(MessengerEvents.Unsubscribe, { users: payload.users });
            });
            this.signalling.addEventListener(MsgEnums_1.MsgEvent.onSetStatus, function (payload) {
                _this9._dispatchEvent(MessengerEvents.SetStatus, {
                    user: {
                        userId: payload.object.user_id,
                        online: payload.object.online,
                        timestamp: payload.object.timestamp
                    },
                    userId: payload.user_id,
                    seq: payload.seq,
                    onIncomingEvent: payload.on_incoming_event
                });
            });
            this.awaitPromiseList = [];
        }
        /**
         * @hidden
         */


        _createClass(Messenger, [{
            key: "createConversation",

            /**
             * Create a new conversation.
             * Triggers the <a href='https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#createconversation'>CreateConversation</a> event on all connected clients that are mentioned in the 'participants' array.
             * <div class="doc_dispatch_block tsd-descriptions">
             *     <h4 class="tsd-parameters-title">Dispatch:</h4>
             *     <ul class="tsd-parameters">
             *          <li><div class="tsd-comment tsd-typography"><p>The <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#createconversation">CreateConversation</a> event with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.conversationevent.html">ConversationEvent</a> parameter</p></div></li>
             *          <li><div class="tsd-comment tsd-typography"><p>The <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#error">Error</a> event with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.errorevent.html">ErrorEvent</a> parameter </p></div></li>
             *     </ul>
             * </div>
             * @param participants Array of participants alongside with access rights params
             * @param moderators Array of moderators
             * @param distinct If two conversations are created with same set of users and moderators and both have 'distinct' flag, second creation of conversation (with the same participants) will fail with the UUID of conversation already created. Note that changing users or moderators list will clear 'distinct' flag.
             * @param enablePublicJoin If set to 'true', anyone can join conversation by uuid
             * @param customData JavaScript object with custom data, up to 5kb. Note that setting this property does not send changes to the server. Use the 'update' to send all changes at once or 'setCustomData' to update and set the custom data.
             * @param title conversation title
             */
            value: function createConversation(participants, title, distinct, enablePublicJoin, customData, moderators) {
                this.cm.createConversation(participants, title, distinct, enablePublicJoin, customData, moderators);
            }
            /**
             * Get conversation by it's UUID
             * <div class="doc_dispatch_block tsd-descriptions">
             *     <h4 class="tsd-parameters-title">Dispatch:</h4>
             *     <ul class="tsd-parameters">
             *          <li><div class="tsd-comment tsd-typography"><p>The <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#getconversation">GetConversation</a> event with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.conversationevent.html">ConversationEvent</a> parameter</p></div></li>
             *          <li><div class="tsd-comment tsd-typography"><p>The <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#error">Error</a> event with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.errorevent.html">ErrorEvent</a> parameter </p></div></li>
             *     </ul>
             * </div>
             * @param uuid
             */

        }, {
            key: "getConversation",
            value: function getConversation(uuid) {
                this.cm.getConversation(uuid);
            }
            /**
             * Get multiple conversations by array of UUIDs. Maximum 30 conversation. Note that calling this method will result in <b>multiple</b> 'getConversation' events.
             * <div class="doc_dispatch_block tsd-descriptions">
             *     <h4 class="tsd-parameters-title">Dispatch:</h4>
             *     <ul class="tsd-parameters">
             *          <li><div class="tsd-comment tsd-typography"><p>Multiple <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#getconversation">GetConversation</a> events with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.conversationevent.html">ConversationEvent</a> parameter</p></div></li>
             *          <li><div class="tsd-comment tsd-typography"><p>The <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#error">Error</a> event with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.errorevent.html">ErrorEvent</a> parameter </p></div></li>
             *     </ul>
             * </div>
             * @param conversations Array of UUIDs
             * @returns {Array<Conversation>}
             */

        }, {
            key: "getConversations",
            value: function getConversations(conversations) {
                if (conversations.length > 30) {
                    Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.MESSAGING, "Rate limit", Logger_1.LogLevel.ERROR, "you can get maximum 30 conversation in one getConversations");
                    return;
                }
                return this.cm.getConversations(conversations);
            }
            /**
             * @hidden
             */

        }, {
            key: "getRawConversations",
            value: function getRawConversations(conversations) {
                return this.cm.getConversations(conversations);
            }
            /**
             * Remove the conversation specified by the UUID
             * <div class="doc_dispatch_block tsd-descriptions">
             *     <h4 class="tsd-parameters-title">Dispatch:</h4>
             *     <ul class="tsd-parameters">
             *          <li><div class="tsd-comment tsd-typography"><p>The <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#removeconversation">RemoveConversation</a> event with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.conversationevent.html">ConversationEvent</a> parameter</p></div></li>
             *          <li><div class="tsd-comment tsd-typography"><p>The <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#error">Error</a> event with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.errorevent.html">ErrorEvent</a> parameter </p></div></li>
             *     </ul>
             * </div>
             * @param uuid Universally Unique Identifier of the conversation
             */

        }, {
            key: "removeConversation",
            value: function removeConversation(uuid) {
                this.cm.removeConversation(uuid);
            }
            /**
             * Join current user to the conversation specified by the UUID
             * <div class="doc_dispatch_block tsd-descriptions">
             *     <h4 class="tsd-parameters-title">Dispatch:</h4>
             *     <ul class="tsd-parameters">
             *          <li><div class="tsd-comment tsd-typography"><p>The <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#editconversation">EditConversation</a> event with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.conversationevent.html">ConversationEvent</a> parameter</p></div></li>
             *          <li><div class="tsd-comment tsd-typography"><p>The <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#error">Error</a> event with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.errorevent.html">ErrorEvent</a> parameter </p></div></li>
             *     </ul>
             * </div>
             * @param uuid Universally Unique Identifier of the conversation
             */

        }, {
            key: "joinConversation",
            value: function joinConversation(uuid) {
                this.signalling.sendPayload(MsgEnums_1.MsgAction.joinConversation, { uuid: uuid });
            }
            /**
             * Leave current user from the conversation specified by the UUID
             * <div class="doc_dispatch_block tsd-descriptions">
             *     <h4 class="tsd-parameters-title">Dispatch:</h4>
             *     <ul class="tsd-parameters">
             *          <li><div class="tsd-comment tsd-typography"><p>The <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#editconversation">EditConversation</a> event with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.conversationevent.html">ConversationEvent</a> parameter</p></div></li>
             *          <li><div class="tsd-comment tsd-typography"><p>The <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#error">Error</a> event with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.errorevent.html">ErrorEvent</a> parameter </p></div></li>
             *     </ul>
             * </div>
             * @param uuid  Universally Unique Identifier of the conversation
             */

        }, {
            key: "leaveConversation",
            value: function leaveConversation(uuid) {
                this.signalling.sendPayload(MsgEnums_1.MsgAction.leaveConversation, { uuid: uuid });
            }
            /**
             * Get user information for the user specified by the full Voximplant user identifier, ex 'username@appname.accname'
             * <div class="doc_dispatch_block tsd-descriptions">
             *     <h4 class="tsd-parameters-title">Dispatch:</h4>
             *     <ul class="tsd-parameters">
             *          <li><div class="tsd-comment tsd-typography"><p>The <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#getuser">GetUser</a> event with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.userevent.html">UserEvent</a> parameter</p></div></li>
             *          <li><div class="tsd-comment tsd-typography"><p>The <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#error">Error</a> event with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.errorevent.html">ErrorEvent</a> parameter </p></div></li>
             *     </ul>
             * </div>
             * @param user_id User identifier
             */

        }, {
            key: "getUser",
            value: function getUser(user_id) {
                this.signalling.sendPayload(MsgEnums_1.MsgAction.getUser, { user_id: user_id });
            }
            /**
             * Get the full Voximplant user identifier, ex 'username@appname.accname', for the current user
             * @returns {string} current user short identifier
             */

        }, {
            key: "getMe",
            value: function getMe() {
                return ConversationManager.extractUserName(Authenticator_1.Authenticator.get().username());
            }
            /**
             * Edit current user information.
             * <div class="doc_dispatch_block tsd-descriptions">
             *     <h4 class="tsd-parameters-title">Dispatch:</h4>
             *     <ul class="tsd-parameters">
             *          <li><div class="tsd-comment tsd-typography"><p>The <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#edituser">EditUser</a> event with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.userevent.html">UserEvent</a> parameter</p></div></li>
             *          <li><div class="tsd-comment tsd-typography"><p>The <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#error">Error</a> event with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.errorevent.html">ErrorEvent</a> parameter </p></div></li>
             *     </ul>
             * </div>
             * @param custom_data Public custom data available for all users
             * @param private_custom_data Private custom data available only to the user themselves.
             */

        }, {
            key: "editUser",
            value: function editUser(customData, privateCustomData) {
                var user = { user_id: ConversationManager.extractUserName(Authenticator_1.Authenticator.get().username()) };
                if (customData) user['custom_data'] = customData;
                if (privateCustomData) user['private_custom_data'] = privateCustomData;
                this.signalling.sendPayload(MsgEnums_1.MsgAction.editUser, user);
            }
            /**
             * Get user information for the users specified by the array of the full Voximplant user identifiers, ex 'username@appname.accname'
             * <div class="doc_dispatch_block tsd-descriptions">
             *     <h4 class="tsd-parameters-title">Dispatch:</h4>
             *     <ul class="tsd-parameters">
             *          <li><div class="tsd-comment tsd-typography"><p>Multiple <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#getuser">GetUser</a> events with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.userevent.html">UserEvent</a> parameter</p></div></li>
             *          <li><div class="tsd-comment tsd-typography"><p>The <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#error">Error</a> event with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.errorevent.html">ErrorEvent</a> parameter </p></div></li>
             *     </ul>
             * </div>
             * @param users List of user identifiers
             */

        }, {
            key: "getUsers",
            value: function getUsers(users) {
                this.signalling.sendPayload(MsgEnums_1.MsgAction.getUsers, { users: users });
            }
            /**
             * Register handler for the specified event
             * @hidden
             * @deprecated
             * @param event Event identifier
             * @param handler JavaScript function that will be called when the specified event is triggered. Please note that function is called without 'this' binding.
             */

        }, {
            key: "addEventListener",
            value: function addEventListener(event, handler) {
                if (typeof this.eventListeners[event] === 'undefined') this.eventListeners[event] = [];
                this.eventListeners[event].push(handler);
            }
            /**
             * Remove handler for the specified event
             * @hidden
             * @deprecated
             * @param event Event identifier
             * @param handler Reference to the JavaScript function to remove from event listeners. If not specified, removes all event listeners from the specified event.
             */

        }, {
            key: "removeEventListener",
            value: function removeEventListener(event, handler) {
                if (typeof this.eventListeners[event] === 'undefined') return;
                if (typeof handler === "function") {
                    for (var i = 0; i < this.eventListeners[event].length; i++) {
                        if (this.eventListeners[event][i] === handler) {
                            this.eventListeners[event].splice(i, 1);
                            break;
                        }
                    }
                } else {
                    this.eventListeners[event] = [];
                }
            }
            /**
             * @hidden
             * @param event
             * @param payload
             */

        }, {
            key: "_dispatchEvent",
            value: function _dispatchEvent(event, payload) {
                payload.name = MessengerEvents[event];
                if (typeof this.eventListeners[event] !== 'undefined') this.eventListeners[event].forEach(function (item) {
                    if (typeof item === "function") item(payload);
                });
                if (typeof this.awaitPromiseList[event] !== 'undefined' && this.awaitPromiseList[event].length != 0) {
                    var nowPromise = this.awaitPromiseList[event].splice(0, 1);
                    nowPromise.resolve(payload);
                    window.clearTimeout(nowPromise.expire);
                }
            }
            /**
             * Syntax shortcut for the 'addEventListener'
             * @param event
             * @param handler
             */

        }, {
            key: "on",
            value: function on(event, handler) {
                this.addEventListener(event, handler);
            }
            /**
             * Syntax shortcut for the 'removeEventListener'
             * @param event
             * @param handler
             */

        }, {
            key: "off",
            value: function off(event, handler) {
                this.removeEventListener(event, handler);
            }
            /**
             * Add new promice for awaiting.
             * @param event
             * @param resolve
             * @param reject
             * @hidden
             */

        }, {
            key: "_registerPromise",
            value: function _registerPromise(event, resolve, reject) {
                if (typeof this.awaitPromiseList[event] === "undefined") this.awaitPromiseList[event] = [];
                this.awaitPromiseList[event].push({ resolve: resolve, reject: reject, expire: setTimeout(function () {
                        reject();
                    }, 20000) });
            }
            /**
             * Restore conversation from cache that is previously created by the 'toCache' method.
             * @param cacheConversation JavaScript object for the serialized conversation
             * @returns {Conversation}
             */

        }, {
            key: "createConversationFromCache",
            value: function createConversationFromCache(cacheConversation) {
                if (typeof cacheConversation === "undefined") return null;
                return Conversation.createFromCache(cacheConversation);
            }
            /**
             * Restore message from cache that is previously created by the 'toCache' method.
             * @param cacheMessage JavaScript object for the serialized conversation
             * @returns {Message}
             */

        }, {
            key: "createMessageFromCache",
            value: function createMessageFromCache(cacheMessage) {
                if (typeof cacheMessage === "undefined") return null;
                return Message.createFromCache(cacheMessage);
            }
            /**
             * Subscribe for user information change and presence status change. On change, the 'onSuscribe' event will be triggered.
             * <div class="doc_dispatch_block tsd-descriptions">
             *     <h4 class="tsd-parameters-title">Dispatch:</h4>
             *     <ul class="tsd-parameters">
             *          <li><div class="tsd-comment tsd-typography"><p>Multiple <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#subscribe">Subscribe</a> events with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.subscriptionevent.html">SubscriptionEvent</a> parameter</p></div></li>
             *          <li><div class="tsd-comment tsd-typography"><p>The <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#error">Error</a> event with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.errorevent.html">ErrorEvent</a> parameter </p></div></li>
             *     </ul>
             * </div>
             * @param users List of full Voximplant user identifiers, ex 'username@appname.accname'
             */

        }, {
            key: "subscribe",
            value: function subscribe(users) {
                this.signalling.sendPayload(MsgEnums_1.MsgAction.subscribe, { users: users });
            }
            /**
             * Unsubscribe for user information change and presence status change.
             * <div class="doc_dispatch_block tsd-descriptions">
             *     <h4 class="tsd-parameters-title">Dispatch:</h4>
             *     <ul class="tsd-parameters">
             *          <li><div class="tsd-comment tsd-typography"><p>Multiple <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#unsubscribe">Unsubscribe</a> events with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.subscriptionevent.html">SubscriptionEvent</a> parameter</p></div></li>
             *          <li><div class="tsd-comment tsd-typography"><p>The <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#error">Error</a> event with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.errorevent.html">ErrorEvent</a> parameter </p></div></li>
             *     </ul>
             * </div>
             * @param users List of full Voximplant user identifiers, ex 'username@appname.accname'
             */

        }, {
            key: "unsubscribe",
            value: function unsubscribe(users) {
                this.signalling.sendPayload(MsgEnums_1.MsgAction.unsubscribe, { users: users });
            }
            /**
             * @hidden
             * @deprecated
             * @param status
             */

        }, {
            key: "setPresence",
            value: function setPresence(status) {
                this.setStatus(status);
            }
            /**
             * Set user presence status.
             * Triggers the <a href='https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#setstatus'>SetStatus</a> event for all messenger objects on all <b>connected</b> clients which are subscribed for notifications about this user. Including this one if conditions are met.
             * <div class="doc_dispatch_block tsd-descriptions">
             *     <h4 class="tsd-parameters-title">Dispatch:</h4>
             *     <ul class="tsd-parameters">
             *          <li><div class="tsd-comment tsd-typography"><p>Multiple <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#setstatus">SetStatus</a> events with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.statusevent.html">StatusEvent</a> parameter</p></div></li>
             *          <li><div class="tsd-comment tsd-typography"><p>The <a href="https://voximplant.com/docs/references/websdk/enums/messaging.messengerevents.html#error">Error</a> event with the <a href="https://voximplant.com/docs/references/websdk/interfaces/messaging.messengereventscallbacks.errorevent.html">ErrorEvent</a> parameter </p></div></li>
             *     </ul>
             * </div>
             * @param status true if user is available for messaging.
             */

        }, {
            key: "setStatus",
            value: function setStatus(status) {
                this.signalling.sendPayload(MsgEnums_1.MsgAction.setStatus, { online: status });
            }
            /**
             * @hidden
             * @return {string}
             * @private
             */

        }, {
            key: "_traceName",
            value: function _traceName() {
                return 'Messenger';
            }
        }], [{
            key: "getInstance",
            value: function getInstance() {
                Messenger.instance = Messenger.instance || new Messenger();
                return Messenger.instance;
            }
        }]);

        return Messenger;
    }();

    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger.prototype, "createConversation", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger.prototype, "getConversation", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger.prototype, "getConversations", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger.prototype, "getRawConversations", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger.prototype, "removeConversation", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger.prototype, "joinConversation", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger.prototype, "leaveConversation", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger.prototype, "getUser", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger.prototype, "getMe", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger.prototype, "editUser", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger.prototype, "getUsers", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger.prototype, "addEventListener", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger.prototype, "removeEventListener", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger.prototype, "_dispatchEvent", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger.prototype, "on", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger.prototype, "off", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger.prototype, "_registerPromise", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger.prototype, "createConversationFromCache", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger.prototype, "createMessageFromCache", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger.prototype, "subscribe", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger.prototype, "unsubscribe", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger.prototype, "setPresence", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger.prototype, "setStatus", null);
    __decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.MESSAGING)], Messenger, "getInstance", null);
    Messaging.Messenger = Messenger;
    var MessengerEvents = void 0;
    (function (MessengerEvents) {
        /**
         * New conversation created.
         * <p>You receive this event when anybody created a new conversation with the current user in participant array. Also this event dispatch on conversation creator.</p>
         */
        MessengerEvents["CreateConversation"] = "CreateConversation";
        /**
         * Conversation properties were modified.
         */
        MessengerEvents["EditConversation"] = "EditConversation";
        /**
         * The conversation was removed.
         */
        MessengerEvents["RemoveConversation"] = "RemoveConversation";
        /**
         * Conversation description is received. Triggered in response to the 'getConversation'.
         */
        MessengerEvents["GetConversation"] = "GetConversation";
        /**
         * New message is received. Triggered in response to the 'sendMessage' called by any user.
         */
        MessengerEvents["SendMessage"] = "SendMessage";
        /**
         * Message was edited.
         */
        MessengerEvents["EditMessage"] = "EditMessage";
        /**
         * Message was removed.
         */
        MessengerEvents["RemoveMessage"] = "RemoveMessage";
        /**
         * Information that some user is typing something is received. Triggered in response to the 'typing' called by any user.
         */
        MessengerEvents["Typing"] = "Typing";
        /**
         * Dispatch when <a href="https://voximplant.com/docs/references/websdk/classes/messaging.messenger.html#edituser">Messenger.editUser</a> successful done into cloud. Triggered only for users specified in the 'subscribe' method call.
         */
        MessengerEvents["EditUser"] = "EditUser";
        /**
         * Return user, requested in <a href="https://voximplant.com/docs/references/websdk/classes/messaging.messenger.html#getuser">Messenger.getUser</a> function
         */
        MessengerEvents["GetUser"] = "GetUser";
        MessengerEvents["Error"] = "Error";
        /**
         * Event is triggered after <a href='https://voximplant.com/docs/references/websdk/classes/messaging.conversation.html#retransmitevents'>retransmitEvents</a> method is called on some conversation for this SDK instance.
         */
        MessengerEvents["RetransmitEvents"] = "RetransmitEvents";
        /**
         *  Event is triggered after another device with same logged in user called the <a href="https://voximplant.com/docs/references/websdk/classes/messaging.conversation.html#markasread">Conversation.markAsRead</a> method.
         */
        MessengerEvents["Read"] = "Read";
        /**
         * Event is triggered after another device with same logged in user called the <a href="https://voximplant.com/docs/references/websdk/classes/messaging.conversation.html#markasdelivered">Conversation.markAsDelivered</a> method.
         */
        MessengerEvents["Delivered"] = "Delivered";
        /**
         * Event is triggered after the <a href='https://voximplant.com/docs/references/websdk/classes/messaging.messenger.html#subscribe'>subscribe</a> method is called.
         */
        MessengerEvents["Subscribe"] = "Subscribe";
        /**
         * Event is triggered after the <a href='https://voximplant.com/docs/references/websdk/classes/messaging.messenger.html#unsubscribe'>unsubscribe</a> method is called.
         */
        MessengerEvents["Unsubscribe"] = "Unsubscribe";
        /**
         * Event is triggered after the user presence state has changed.
         */
        MessengerEvents["SetStatus"] = "SetStatus";
    })(MessengerEvents = Messaging.MessengerEvents || (Messaging.MessengerEvents = {}));
    /**
     * Available methods to manipulate the messaging flow. Note if the action triggers any of <a href="https://irbisadm.ru/websdk/interfaces/messaging.messengereventscallbacks.conversationevent.html#messengeraction">events</a>, the action's name will be set as a value of
     */
    var MessengerAction = void 0;
    (function (MessengerAction) {
        MessengerAction[MessengerAction["createConversation"] = "createConversation"] = "createConversation";
        MessengerAction[MessengerAction["editConversation"] = "editConversation"] = "editConversation";
        MessengerAction[MessengerAction["removeConversation"] = "removeConversation"] = "removeConversation";
        MessengerAction[MessengerAction["joinConversation"] = "joinConversation"] = "joinConversation";
        MessengerAction[MessengerAction["leaveConversation"] = "leaveConversation"] = "leaveConversation";
        MessengerAction[MessengerAction["getConversation"] = "getConversation"] = "getConversation";
        MessengerAction[MessengerAction["getConversations"] = "getConversations"] = "getConversations";
        MessengerAction[MessengerAction["sendMessage"] = "sendMessage"] = "sendMessage";
        MessengerAction[MessengerAction["editMessage"] = "editMessage"] = "editMessage";
        MessengerAction[MessengerAction["removeMessage"] = "removeMessage"] = "removeMessage";
        MessengerAction[MessengerAction["typingMessage"] = "typingMessage"] = "typingMessage";
        MessengerAction[MessengerAction["editUser"] = "editUser"] = "editUser";
        MessengerAction[MessengerAction["getUser"] = "getUser"] = "getUser";
        MessengerAction[MessengerAction["getUsers"] = "getUsers"] = "getUsers";
        MessengerAction[MessengerAction["retransmitEvents"] = "retransmitEvents"] = "retransmitEvents";
        MessengerAction[MessengerAction["isRead"] = "isRead"] = "isRead";
        MessengerAction[MessengerAction["isDelivered"] = "isDelivered"] = "isDelivered";
        MessengerAction[MessengerAction["addParticipants"] = "addParticipants"] = "addParticipants";
        MessengerAction[MessengerAction["editParticipants"] = "editParticipants"] = "editParticipants";
        MessengerAction[MessengerAction["removeParticipants"] = "removeParticipants"] = "removeParticipants";
        MessengerAction[MessengerAction["addModerators"] = "addModerators"] = "addModerators";
        MessengerAction[MessengerAction["removeModerators"] = "removeModerators"] = "removeModerators";
        MessengerAction[MessengerAction["subscribe"] = "subscribe"] = "subscribe";
        MessengerAction[MessengerAction["unsubscribe"] = "unsubscribe"] = "unsubscribe";
        MessengerAction[MessengerAction["setStatus"] = "setStatus"] = "setStatus";
    })(MessengerAction = Messaging.MessengerAction || (Messaging.MessengerAction = {}));
    /**
     *
     */
    var MessengerError = void 0;
    (function (MessengerError) {
        /**
         * Wrong transport message structure
         */
        MessengerError[MessengerError["Error_1"] = "1"] = "Error_1";
        /**
         * Unknown event name
         */
        MessengerError[MessengerError["Error_2"] = "2"] = "Error_2";
        /**
         * User not auth
         */
        MessengerError[MessengerError["Error_3"] = "3"] = "Error_3";
        /**
         * Wrong message structure
         */
        MessengerError[MessengerError["Error_4"] = "4"] = "Error_4";
        /**
         * Conversation not found or user not in participant list
         */
        MessengerError[MessengerError["Error_5"] = "5"] = "Error_5";
        /**
         * Conversation not found or user can't moderate conversation
         */
        MessengerError[MessengerError["Error_6"] = "6"] = "Error_6";
        /**
         * Conversation already exists
         */
        MessengerError[MessengerError["Error_7"] = "7"] = "Error_7";
        /**
         * Conversation does not exist
         */
        MessengerError[MessengerError["Error_8"] = "8"] = "Error_8";
        /**
         * Message already exists
         */
        MessengerError[MessengerError["Error_9"] = "9"] = "Error_9";
        /**
         * Message does not exist
         */
        MessengerError[MessengerError["Error_10"] = "10"] = "Error_10";
        /**
         * Message was deleted
         */
        MessengerError[MessengerError["Error_11"] = "11"] = "Error_11";
        /**
         * ACL error
         */
        MessengerError[MessengerError["Error_12"] = "12"] = "Error_12";
        /**
         * User already in participant list
         */
        MessengerError[MessengerError["Error_13"] = "13"] = "Error_13";
        /**
         * No rights to edit user
         */
        MessengerError[MessengerError["Error_14"] = "14"] = "Error_14";
        /**
         * Public join is not available in this conversation
         */
        MessengerError[MessengerError["Error_15"] = "15"] = "Error_15";
        /**
         * Conversation was deleted
         */
        MessengerError[MessengerError["Error_16"] = "16"] = "Error_16";
        /**
         * Conversation is distinct
         */
        MessengerError[MessengerError["Error_17"] = "17"] = "Error_17";
        /**
         * User validation Error
         */
        MessengerError[MessengerError["Error_18"] = "18"] = "Error_18";
        /**
         * Lists mismatch
         */
        MessengerError[MessengerError["Error_19"] = "19"] = "Error_19";
        /**
         * Range larger then allowed by service
         */
        MessengerError[MessengerError["Error_21"] = "21"] = "Error_21";
        /**
         * Number of requested objects is larger then allowed by service
         */
        MessengerError[MessengerError["Error_22"] = "22"] = "Error_22";
        /**
         * Message size so large
         */
        MessengerError[MessengerError["Error_23"] = "23"] = "Error_23";
        /**
         * Seq is too big
         */
        MessengerError[MessengerError["Error_24"] = "24"] = "Error_24";
        /**
         * IM service not available
         */
        MessengerError[MessengerError["Error_30"] = "30"] = "Error_30";
        /**
         * Internal error
         */
        MessengerError[MessengerError["Error_500"] = "500"] = "Error_500";
        /**
         * Oops! Something went wrong
         */
        MessengerError[MessengerError["Error_777"] = "777"] = "Error_777";
    })(MessengerError = Messaging.MessengerError || (Messaging.MessengerError = {}));
})(Messaging = exports.Messaging || (exports.Messaging = {}));

/***/ }),
/* 30 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


function __export(m) {
    for (var p in m) {
        if (!exports.hasOwnProperty(p)) exports[p] = m[p];
    }
}
Object.defineProperty(exports, "__esModule", { value: true });
var Client_1 = __webpack_require__(3);
// import '../node_modules/webrtc-adapter/out/adapter_no_edge.js';
__webpack_require__(46);
var Authenticator_1 = __webpack_require__(10);
var index_1 = __webpack_require__(29);
var Events_1 = __webpack_require__(18);
exports.Events = Events_1.Events;
var CallEvents_1 = __webpack_require__(9);
exports.CallEvents = CallEvents_1.CallEvents;
var EndPoint_1 = __webpack_require__(58);
exports.EndPoint = EndPoint_1.EndPoint;
__export(__webpack_require__(29));
var Structures_1 = __webpack_require__(59);
exports.OperatorACDStatuses = Structures_1.OperatorACDStatuses;
var Logger_1 = __webpack_require__(0);
exports.LogCategory = Logger_1.LogCategory;
exports.LogLevel = Logger_1.LogLevel;
__export(__webpack_require__(13));
/**
 * Get VoxImplant.Client instance to use platform functions
 * @example <script src="https://gist.github.com/irbisadm/97b2009d736d6daf27486f342e1d2445.js"></script>
 */
function getInstance() {
    return Client_1.Client.getInstance();
}
exports.getInstance = getInstance;
/**
 * VoxImplant Web SDK lib version
 */
exports.version = Client_1.Client.getInstance().version;
/**
 * Get instance of messaging subsystem
 * @returns {Messenger}
 *
 */
function getMessenger() {
    if (!Authenticator_1.Authenticator.get().authorized()) throw new Error("NOT_AUTHORIZED");
    return index_1.Messaging.Messenger.getInstance();
}
exports.getMessenger = getMessenger;

/***/ }),
/* 31 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

Object.defineProperty(exports, "__esModule", { value: true });
var md5_1 = __webpack_require__(32);
var MediaCache_1 = __webpack_require__(22);
var SignalingDTMFSender_1 = __webpack_require__(17);
var Logger_1 = __webpack_require__(0);
/**
 * Firefox specific implementation
 * @hidden
 */

var FF = function () {
    function FF() {
        _classCallCheck(this, FF);
    }

    _createClass(FF, [{
        key: "_traceName",

        /**
         * @hidden
         * @return {string}
         * @private
         */
        value: function _traceName() {
            return 'FF';
        }
    }], [{
        key: "attachStream",
        value: function attachStream(stream, element) {
            if (typeof element.srcObject === "undefined") {
                element["mozSrcObject"] = stream;
            } else {
                element.srcObject = stream;
            }
            element.load();
            element.play();
        }
    }, {
        key: "detachStream",
        value: function detachStream(element) {
            if (typeof element.srcObject === "undefined") {
                element["mozSrcObject"] = null;
            } else {
                element.srcObject = null;
            }
            element.load();
            element.src = "";
        }
    }, {
        key: "screenSharingSupported",
        value: function screenSharingSupported() {
            return new Promise(function (resolve, reject) {
                if (window.location.protocol != "https:") {
                    resolve(false);
                    return;
                }
                resolve(true);
            });
        }
    }, {
        key: "getScreenMedia",
        value: function getScreenMedia() {
            var constraints = { "audio": false, "video": { mediaSource: 'window' || 'screen' } };
            Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.USERMEDIA, '[constraints]', Logger_1.LogLevel.TRACE, JSON.stringify(constraints));
            return navigator.mediaDevices.getUserMedia(constraints);
        }
    }, {
        key: "getRTCStats",
        value: function getRTCStats(pc) {
            return new Promise(function (resolve, reject) {
                pc.getStats(null).then(function (e) {
                    var resultArray = [];
                    e.forEach(function (result) {
                        if (result.type == "inboundrtp" || result.type == "outboundrtp") resultArray.push(result);
                    });
                    resolve(resultArray);
                }).catch(reject);
            });
        }
    }, {
        key: "getUserMedia",
        value: function getUserMedia(constraints) {
            return new Promise(function (globResolve, globReject) {
                var ms = new MediaStream();
                var cache = MediaCache_1.MediaCache.get();
                var promiseList = [];
                new Promise(function (resolve, reject) {
                    var tracks = [];
                    if (typeof constraints.audio !== "undefined" && constraints.audio !== false) {
                        var deviceId = md5_1.Md5.hashStr(JSON.stringify(constraints.audio));
                        cache.getTrack(deviceId, 'audio').then(function (track) {
                            if (typeof track !== "undefined" && track.readyState !== "ended") {
                                tracks.push(track);
                                resolve(tracks);
                            } else {
                                var subconstraint = JSON.parse(JSON.stringify(constraints));
                                subconstraint.video = false;
                                Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.USERMEDIA, '[constraints]', Logger_1.LogLevel.TRACE, JSON.stringify(subconstraint));
                                navigator.mediaDevices.getUserMedia(subconstraint).then(function (mediastream) {
                                    var _iteratorNormalCompletion = true;
                                    var _didIteratorError = false;
                                    var _iteratorError = undefined;

                                    try {
                                        for (var _iterator = mediastream.getTracks()[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                                            var _track = _step.value;

                                            cache.setTrack(deviceId, 'audio', _track);
                                            tracks.push(_track);
                                        }
                                    } catch (err) {
                                        _didIteratorError = true;
                                        _iteratorError = err;
                                    } finally {
                                        try {
                                            if (!_iteratorNormalCompletion && _iterator.return) {
                                                _iterator.return();
                                            }
                                        } finally {
                                            if (_didIteratorError) {
                                                throw _iteratorError;
                                            }
                                        }
                                    }

                                    resolve(tracks);
                                }).catch(function (e) {
                                    resolve([]);
                                });
                            }
                        });
                    } else {
                        resolve([]);
                    }
                }).then(function (tracks) {
                    return new Promise(function (secResolve, secReject) {
                        if (typeof constraints.video !== "undefined" && constraints.video !== false) {
                            var deviceId = md5_1.Md5.hashStr(JSON.stringify(constraints.video));
                            promiseList.push(new Promise(function (resolve, reject) {
                                cache.getTrack(deviceId, 'video').then(function (track) {
                                    if (typeof track !== "undefined" && track.readyState !== "ended") {
                                        tracks.push(track);
                                        secResolve(tracks);
                                    } else {
                                        var subconstraint = JSON.parse(JSON.stringify(constraints));
                                        subconstraint.audio = false;
                                        Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.USERMEDIA, '[constraints]', Logger_1.LogLevel.TRACE, JSON.stringify(subconstraint));
                                        navigator.mediaDevices.getUserMedia(subconstraint).then(function (mediastream) {
                                            var _iteratorNormalCompletion2 = true;
                                            var _didIteratorError2 = false;
                                            var _iteratorError2 = undefined;

                                            try {
                                                for (var _iterator2 = mediastream.getTracks()[Symbol.iterator](), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
                                                    var _track2 = _step2.value;

                                                    cache.setTrack(deviceId, 'video', _track2);
                                                    tracks.push(_track2);
                                                    secResolve(tracks);
                                                }
                                            } catch (err) {
                                                _didIteratorError2 = true;
                                                _iteratorError2 = err;
                                            } finally {
                                                try {
                                                    if (!_iteratorNormalCompletion2 && _iterator2.return) {
                                                        _iterator2.return();
                                                    }
                                                } finally {
                                                    if (_didIteratorError2) {
                                                        throw _iteratorError2;
                                                    }
                                                }
                                            }
                                        }).catch(function (e) {
                                            secResolve(tracks);
                                        });
                                    }
                                });
                            }));
                        } else {
                            secResolve(tracks);
                        }
                    });
                }).then(function (result) {
                    var _iteratorNormalCompletion3 = true;
                    var _didIteratorError3 = false;
                    var _iteratorError3 = undefined;

                    try {
                        for (var _iterator3 = result[Symbol.iterator](), _step3; !(_iteratorNormalCompletion3 = (_step3 = _iterator3.next()).done); _iteratorNormalCompletion3 = true) {
                            var item = _step3.value;

                            if (typeof item !== "undefined") ms.addTrack(item);
                        }
                    } catch (err) {
                        _didIteratorError3 = true;
                        _iteratorError3 = err;
                    } finally {
                        try {
                            if (!_iteratorNormalCompletion3 && _iterator3.return) {
                                _iterator3.return();
                            }
                        } finally {
                            if (_didIteratorError3) {
                                throw _iteratorError3;
                            }
                        }
                    }

                    if (result.length > 0) globResolve(ms);else globReject();
                }).catch(function (e) {
                    globReject(e);
                });
            });
        }
    }, {
        key: "getDTMFSender",
        value: function getDTMFSender(pc, callId) {
            var pattern = /Firefox\/([0-9\.]+)(?:\s|$)/;
            var ua = navigator.userAgent;
            if (pattern.test(ua)) {
                var browser = pattern.exec(ua);
                var version = browser[1].split('.');
                if (+version[0] >= 53) {
                    var dtmfSenders = pc.getSenders().map(function (sender) {
                        if (sender.track.kind === "audio" && !!sender.dtmf) return sender.dtmf;
                    });
                    if (dtmfSenders.length > 0) return dtmfSenders[0];
                }
            }
            return new SignalingDTMFSender_1.SignalingDTMFSender(callId);
        }
    }]);

    return FF;
}();

exports.FF = FF;

/***/ }),
/* 32 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/*

TypeScript Md5
==============

Based on work by
* Joseph Myers: http://www.myersdaily.org/joseph/javascript/md5-text.html
* André Cruz: https://github.com/satazor/SparkMD5
* Raymond Hill: https://github.com/gorhill/yamd5.js

Effectively a TypeScrypt re-write of Raymond Hill JS Library

The MIT License (MIT)

Copyright (C) 2014 Raymond Hill

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.



            DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
                    Version 2, December 2004

 Copyright (C) 2015 André Cruz <amdfcruz@gmail.com>

 Everyone is permitted to copy and distribute verbatim or modified
 copies of this license document, and changing it is allowed as long
 as the name is changed.

            DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
   TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION

  0. You just DO WHAT THE FUCK YOU WANT TO.


*/
Object.defineProperty(exports, "__esModule", { value: true });
var Md5 = (function () {
    function Md5() {
        this._state = new Int32Array(4);
        this._buffer = new ArrayBuffer(68);
        this._buffer8 = new Uint8Array(this._buffer, 0, 68);
        this._buffer32 = new Uint32Array(this._buffer, 0, 17);
        this.start();
    }
    // One time hashing functions
    Md5.hashStr = function (str, raw) {
        if (raw === void 0) { raw = false; }
        return this.onePassHasher
            .start()
            .appendStr(str)
            .end(raw);
    };
    Md5.hashAsciiStr = function (str, raw) {
        if (raw === void 0) { raw = false; }
        return this.onePassHasher
            .start()
            .appendAsciiStr(str)
            .end(raw);
    };
    Md5._hex = function (x) {
        var hc = Md5.hexChars;
        var ho = Md5.hexOut;
        var n;
        var offset;
        var j;
        var i;
        for (i = 0; i < 4; i += 1) {
            offset = i * 8;
            n = x[i];
            for (j = 0; j < 8; j += 2) {
                ho[offset + 1 + j] = hc.charAt(n & 0x0F);
                n >>>= 4;
                ho[offset + 0 + j] = hc.charAt(n & 0x0F);
                n >>>= 4;
            }
        }
        return ho.join('');
    };
    Md5._md5cycle = function (x, k) {
        var a = x[0];
        var b = x[1];
        var c = x[2];
        var d = x[3];
        // ff()
        a += (b & c | ~b & d) + k[0] - 680876936 | 0;
        a = (a << 7 | a >>> 25) + b | 0;
        d += (a & b | ~a & c) + k[1] - 389564586 | 0;
        d = (d << 12 | d >>> 20) + a | 0;
        c += (d & a | ~d & b) + k[2] + 606105819 | 0;
        c = (c << 17 | c >>> 15) + d | 0;
        b += (c & d | ~c & a) + k[3] - 1044525330 | 0;
        b = (b << 22 | b >>> 10) + c | 0;
        a += (b & c | ~b & d) + k[4] - 176418897 | 0;
        a = (a << 7 | a >>> 25) + b | 0;
        d += (a & b | ~a & c) + k[5] + 1200080426 | 0;
        d = (d << 12 | d >>> 20) + a | 0;
        c += (d & a | ~d & b) + k[6] - 1473231341 | 0;
        c = (c << 17 | c >>> 15) + d | 0;
        b += (c & d | ~c & a) + k[7] - 45705983 | 0;
        b = (b << 22 | b >>> 10) + c | 0;
        a += (b & c | ~b & d) + k[8] + 1770035416 | 0;
        a = (a << 7 | a >>> 25) + b | 0;
        d += (a & b | ~a & c) + k[9] - 1958414417 | 0;
        d = (d << 12 | d >>> 20) + a | 0;
        c += (d & a | ~d & b) + k[10] - 42063 | 0;
        c = (c << 17 | c >>> 15) + d | 0;
        b += (c & d | ~c & a) + k[11] - 1990404162 | 0;
        b = (b << 22 | b >>> 10) + c | 0;
        a += (b & c | ~b & d) + k[12] + 1804603682 | 0;
        a = (a << 7 | a >>> 25) + b | 0;
        d += (a & b | ~a & c) + k[13] - 40341101 | 0;
        d = (d << 12 | d >>> 20) + a | 0;
        c += (d & a | ~d & b) + k[14] - 1502002290 | 0;
        c = (c << 17 | c >>> 15) + d | 0;
        b += (c & d | ~c & a) + k[15] + 1236535329 | 0;
        b = (b << 22 | b >>> 10) + c | 0;
        // gg()
        a += (b & d | c & ~d) + k[1] - 165796510 | 0;
        a = (a << 5 | a >>> 27) + b | 0;
        d += (a & c | b & ~c) + k[6] - 1069501632 | 0;
        d = (d << 9 | d >>> 23) + a | 0;
        c += (d & b | a & ~b) + k[11] + 643717713 | 0;
        c = (c << 14 | c >>> 18) + d | 0;
        b += (c & a | d & ~a) + k[0] - 373897302 | 0;
        b = (b << 20 | b >>> 12) + c | 0;
        a += (b & d | c & ~d) + k[5] - 701558691 | 0;
        a = (a << 5 | a >>> 27) + b | 0;
        d += (a & c | b & ~c) + k[10] + 38016083 | 0;
        d = (d << 9 | d >>> 23) + a | 0;
        c += (d & b | a & ~b) + k[15] - 660478335 | 0;
        c = (c << 14 | c >>> 18) + d | 0;
        b += (c & a | d & ~a) + k[4] - 405537848 | 0;
        b = (b << 20 | b >>> 12) + c | 0;
        a += (b & d | c & ~d) + k[9] + 568446438 | 0;
        a = (a << 5 | a >>> 27) + b | 0;
        d += (a & c | b & ~c) + k[14] - 1019803690 | 0;
        d = (d << 9 | d >>> 23) + a | 0;
        c += (d & b | a & ~b) + k[3] - 187363961 | 0;
        c = (c << 14 | c >>> 18) + d | 0;
        b += (c & a | d & ~a) + k[8] + 1163531501 | 0;
        b = (b << 20 | b >>> 12) + c | 0;
        a += (b & d | c & ~d) + k[13] - 1444681467 | 0;
        a = (a << 5 | a >>> 27) + b | 0;
        d += (a & c | b & ~c) + k[2] - 51403784 | 0;
        d = (d << 9 | d >>> 23) + a | 0;
        c += (d & b | a & ~b) + k[7] + 1735328473 | 0;
        c = (c << 14 | c >>> 18) + d | 0;
        b += (c & a | d & ~a) + k[12] - 1926607734 | 0;
        b = (b << 20 | b >>> 12) + c | 0;
        // hh()
        a += (b ^ c ^ d) + k[5] - 378558 | 0;
        a = (a << 4 | a >>> 28) + b | 0;
        d += (a ^ b ^ c) + k[8] - 2022574463 | 0;
        d = (d << 11 | d >>> 21) + a | 0;
        c += (d ^ a ^ b) + k[11] + 1839030562 | 0;
        c = (c << 16 | c >>> 16) + d | 0;
        b += (c ^ d ^ a) + k[14] - 35309556 | 0;
        b = (b << 23 | b >>> 9) + c | 0;
        a += (b ^ c ^ d) + k[1] - 1530992060 | 0;
        a = (a << 4 | a >>> 28) + b | 0;
        d += (a ^ b ^ c) + k[4] + 1272893353 | 0;
        d = (d << 11 | d >>> 21) + a | 0;
        c += (d ^ a ^ b) + k[7] - 155497632 | 0;
        c = (c << 16 | c >>> 16) + d | 0;
        b += (c ^ d ^ a) + k[10] - 1094730640 | 0;
        b = (b << 23 | b >>> 9) + c | 0;
        a += (b ^ c ^ d) + k[13] + 681279174 | 0;
        a = (a << 4 | a >>> 28) + b | 0;
        d += (a ^ b ^ c) + k[0] - 358537222 | 0;
        d = (d << 11 | d >>> 21) + a | 0;
        c += (d ^ a ^ b) + k[3] - 722521979 | 0;
        c = (c << 16 | c >>> 16) + d | 0;
        b += (c ^ d ^ a) + k[6] + 76029189 | 0;
        b = (b << 23 | b >>> 9) + c | 0;
        a += (b ^ c ^ d) + k[9] - 640364487 | 0;
        a = (a << 4 | a >>> 28) + b | 0;
        d += (a ^ b ^ c) + k[12] - 421815835 | 0;
        d = (d << 11 | d >>> 21) + a | 0;
        c += (d ^ a ^ b) + k[15] + 530742520 | 0;
        c = (c << 16 | c >>> 16) + d | 0;
        b += (c ^ d ^ a) + k[2] - 995338651 | 0;
        b = (b << 23 | b >>> 9) + c | 0;
        // ii()
        a += (c ^ (b | ~d)) + k[0] - 198630844 | 0;
        a = (a << 6 | a >>> 26) + b | 0;
        d += (b ^ (a | ~c)) + k[7] + 1126891415 | 0;
        d = (d << 10 | d >>> 22) + a | 0;
        c += (a ^ (d | ~b)) + k[14] - 1416354905 | 0;
        c = (c << 15 | c >>> 17) + d | 0;
        b += (d ^ (c | ~a)) + k[5] - 57434055 | 0;
        b = (b << 21 | b >>> 11) + c | 0;
        a += (c ^ (b | ~d)) + k[12] + 1700485571 | 0;
        a = (a << 6 | a >>> 26) + b | 0;
        d += (b ^ (a | ~c)) + k[3] - 1894986606 | 0;
        d = (d << 10 | d >>> 22) + a | 0;
        c += (a ^ (d | ~b)) + k[10] - 1051523 | 0;
        c = (c << 15 | c >>> 17) + d | 0;
        b += (d ^ (c | ~a)) + k[1] - 2054922799 | 0;
        b = (b << 21 | b >>> 11) + c | 0;
        a += (c ^ (b | ~d)) + k[8] + 1873313359 | 0;
        a = (a << 6 | a >>> 26) + b | 0;
        d += (b ^ (a | ~c)) + k[15] - 30611744 | 0;
        d = (d << 10 | d >>> 22) + a | 0;
        c += (a ^ (d | ~b)) + k[6] - 1560198380 | 0;
        c = (c << 15 | c >>> 17) + d | 0;
        b += (d ^ (c | ~a)) + k[13] + 1309151649 | 0;
        b = (b << 21 | b >>> 11) + c | 0;
        a += (c ^ (b | ~d)) + k[4] - 145523070 | 0;
        a = (a << 6 | a >>> 26) + b | 0;
        d += (b ^ (a | ~c)) + k[11] - 1120210379 | 0;
        d = (d << 10 | d >>> 22) + a | 0;
        c += (a ^ (d | ~b)) + k[2] + 718787259 | 0;
        c = (c << 15 | c >>> 17) + d | 0;
        b += (d ^ (c | ~a)) + k[9] - 343485551 | 0;
        b = (b << 21 | b >>> 11) + c | 0;
        x[0] = a + x[0] | 0;
        x[1] = b + x[1] | 0;
        x[2] = c + x[2] | 0;
        x[3] = d + x[3] | 0;
    };
    Md5.prototype.start = function () {
        this._dataLength = 0;
        this._bufferLength = 0;
        this._state.set(Md5.stateIdentity);
        return this;
    };
    // Char to code point to to array conversion:
    // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/charCodeAt
    // #Example.3A_Fixing_charCodeAt_to_handle_non-Basic-Multilingual-Plane_characters_if_their_presence_earlier_in_the_string_is_unknown
    Md5.prototype.appendStr = function (str) {
        var buf8 = this._buffer8;
        var buf32 = this._buffer32;
        var bufLen = this._bufferLength;
        var code;
        var i;
        for (i = 0; i < str.length; i += 1) {
            code = str.charCodeAt(i);
            if (code < 128) {
                buf8[bufLen++] = code;
            }
            else if (code < 0x800) {
                buf8[bufLen++] = (code >>> 6) + 0xC0;
                buf8[bufLen++] = code & 0x3F | 0x80;
            }
            else if (code < 0xD800 || code > 0xDBFF) {
                buf8[bufLen++] = (code >>> 12) + 0xE0;
                buf8[bufLen++] = (code >>> 6 & 0x3F) | 0x80;
                buf8[bufLen++] = (code & 0x3F) | 0x80;
            }
            else {
                code = ((code - 0xD800) * 0x400) + (str.charCodeAt(++i) - 0xDC00) + 0x10000;
                if (code > 0x10FFFF) {
                    throw new Error('Unicode standard supports code points up to U+10FFFF');
                }
                buf8[bufLen++] = (code >>> 18) + 0xF0;
                buf8[bufLen++] = (code >>> 12 & 0x3F) | 0x80;
                buf8[bufLen++] = (code >>> 6 & 0x3F) | 0x80;
                buf8[bufLen++] = (code & 0x3F) | 0x80;
            }
            if (bufLen >= 64) {
                this._dataLength += 64;
                Md5._md5cycle(this._state, buf32);
                bufLen -= 64;
                buf32[0] = buf32[16];
            }
        }
        this._bufferLength = bufLen;
        return this;
    };
    Md5.prototype.appendAsciiStr = function (str) {
        var buf8 = this._buffer8;
        var buf32 = this._buffer32;
        var bufLen = this._bufferLength;
        var i;
        var j = 0;
        for (;;) {
            i = Math.min(str.length - j, 64 - bufLen);
            while (i--) {
                buf8[bufLen++] = str.charCodeAt(j++);
            }
            if (bufLen < 64) {
                break;
            }
            this._dataLength += 64;
            Md5._md5cycle(this._state, buf32);
            bufLen = 0;
        }
        this._bufferLength = bufLen;
        return this;
    };
    Md5.prototype.appendByteArray = function (input) {
        var buf8 = this._buffer8;
        var buf32 = this._buffer32;
        var bufLen = this._bufferLength;
        var i;
        var j = 0;
        for (;;) {
            i = Math.min(input.length - j, 64 - bufLen);
            while (i--) {
                buf8[bufLen++] = input[j++];
            }
            if (bufLen < 64) {
                break;
            }
            this._dataLength += 64;
            Md5._md5cycle(this._state, buf32);
            bufLen = 0;
        }
        this._bufferLength = bufLen;
        return this;
    };
    Md5.prototype.getState = function () {
        var self = this;
        var s = self._state;
        return {
            buffer: String.fromCharCode.apply(null, self._buffer8),
            buflen: self._bufferLength,
            length: self._dataLength,
            state: [s[0], s[1], s[2], s[3]]
        };
    };
    Md5.prototype.setState = function (state) {
        var buf = state.buffer;
        var x = state.state;
        var s = this._state;
        var i;
        this._dataLength = state.length;
        this._bufferLength = state.buflen;
        s[0] = x[0];
        s[1] = x[1];
        s[2] = x[2];
        s[3] = x[3];
        for (i = 0; i < buf.length; i += 1) {
            this._buffer8[i] = buf.charCodeAt(i);
        }
    };
    Md5.prototype.end = function (raw) {
        if (raw === void 0) { raw = false; }
        var bufLen = this._bufferLength;
        var buf8 = this._buffer8;
        var buf32 = this._buffer32;
        var i = (bufLen >> 2) + 1;
        var dataBitsLen;
        this._dataLength += bufLen;
        buf8[bufLen] = 0x80;
        buf8[bufLen + 1] = buf8[bufLen + 2] = buf8[bufLen + 3] = 0;
        buf32.set(Md5.buffer32Identity.subarray(i), i);
        if (bufLen > 55) {
            Md5._md5cycle(this._state, buf32);
            buf32.set(Md5.buffer32Identity);
        }
        // Do the final computation based on the tail and length
        // Beware that the final length may not fit in 32 bits so we take care of that
        dataBitsLen = this._dataLength * 8;
        if (dataBitsLen <= 0xFFFFFFFF) {
            buf32[14] = dataBitsLen;
        }
        else {
            var matches = dataBitsLen.toString(16).match(/(.*?)(.{0,8})$/);
            if (matches === null) {
                return;
            }
            var lo = parseInt(matches[2], 16);
            var hi = parseInt(matches[1], 16) || 0;
            buf32[14] = lo;
            buf32[15] = hi;
        }
        Md5._md5cycle(this._state, buf32);
        return raw ? this._state : Md5._hex(this._state);
    };
    // Private Static Variables
    Md5.stateIdentity = new Int32Array([1732584193, -271733879, -1732584194, 271733878]);
    Md5.buffer32Identity = new Int32Array([0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]);
    Md5.hexChars = '0123456789abcdef';
    Md5.hexOut = [];
    // Permanent instance is to use for one-call hashing
    Md5.onePassHasher = new Md5();
    return Md5;
}());
exports.Md5 = Md5;
if (Md5.hashStr('hello') !== '5d41402abc4b2a76b9719d911017c592') {
    console.error('Md5 self test failed.');
}
//# sourceMappingURL=md5.js.map

/***/ }),
/* 33 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

Object.defineProperty(exports, "__esModule", { value: true });
/**
 * Configuration of capture devices
 * @hidden
 */

var MediaCaptureConfig = function () {
    function MediaCaptureConfig() {
        _classCallCheck(this, MediaCaptureConfig);
    }

    _createClass(MediaCaptureConfig, [{
        key: "clone",
        value: function clone() {
            var r = new MediaCaptureConfig();
            r.videoInputId = this.videoInputId;
            r.audioInputId = this.audioInputId;
            r.audioEnabled = this.audioEnabled;
            r.videoEnabled = this.videoEnabled;
            r.videoSettings = this.videoSettings;
            return r;
        }
    }, {
        key: "setVideoSettings",
        value: function setVideoSettings(settings) {
            this.videoSettings = settings;
            if (typeof settings.deviceId != "undefined") this.videoInputId = settings.deviceId;
        }
        /**
         * @hidden
         * @return {string}
         * @private
         */

    }, {
        key: "_traceName",
        value: function _traceName() {
            return 'MediaCaptureConfig';
        }
    }]);

    return MediaCaptureConfig;
}();

exports.MediaCaptureConfig = MediaCaptureConfig;

/***/ }),
/* 34 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

Object.defineProperty(exports, "__esModule", { value: true });
var BrowserSpecific_1 = __webpack_require__(5);
var Logger_1 = __webpack_require__(0);
/**
 * @hidden
 */

var ReusableStructure = function () {
    function ReusableStructure(kind) {
        _classCallCheck(this, ReusableStructure);

        this.kind = kind;
        this.inUse = false;
        this.track = null;
        this.callId = null;
    }
    // public attach(track:MediaStreamTrack,direction:"local"|"remote"){


    _createClass(ReusableStructure, [{
        key: "attach",
        value: function attach(track, direction) {
            BrowserSpecific_1.default.attachMedia(new MediaStream([track]), this.element);
            this.direction = direction;
        }
    }, {
        key: "free",
        value: function free() {
            BrowserSpecific_1.default.detachMedia(this.element);
            this.inUse = false;
            this.track = null;
            this.callId = null;
        }
    }]);

    return ReusableStructure;
}();

exports.ReusableStructure = ReusableStructure;
/**
 * Singleton that provides audio/video rendering
 * Reuses audio/video elements
 * @hidden
 */

var ReusableRenderer = function () {
    _createClass(ReusableRenderer, null, [{
        key: "get",
        value: function get() {
            if (!this.inst) this.inst = new ReusableRenderer();
            return this.inst;
        }
    }]);

    function ReusableRenderer() {
        _classCallCheck(this, ReusableRenderer);

        this.elementList = new Array();
        this.log = Logger_1.LogManager.get().createLogger(Logger_1.LogCategory.RTC, "Renderer");
    }
    // private getFreeElements(kind:"all"|"video"|"audio"):Array<ReusableStructure>{


    _createClass(ReusableRenderer, [{
        key: "getFreeElements",
        value: function getFreeElements(kind) {
            return this.elementList.filter(function (el) {
                if (el.inUse && (kind == "all" || kind == el.kind)) return true;
            });
        }
        // public catchRender(track:MediaStreamTrack,callId:number,direction:"local"|"remote"):HTMLMediaElement{

    }, {
        key: "catchRender",
        value: function catchRender(track, callId, direction) {
            var freeTracks = this.getFreeElements(track.kind);
            var activeTrack = void 0;
            if (freeTracks.length) {
                activeTrack = freeTracks[0];
            } else {
                activeTrack = new ReusableStructure(track.kind);
                this.elementList.push(activeTrack);
            }
            activeTrack.inUse = true;
            activeTrack.track = track;
            activeTrack.callId = callId;
            activeTrack.attach(track, direction);
            return activeTrack.element;
        }
        // public catchRenders(stream:MediaStream,callId:number,direction:"local"|"remote"):Array<HTMLMediaElement>{

    }, {
        key: "catchRenders",
        value: function catchRenders(stream, callId, direction) {
            var tracks = stream.getTracks();
            var renderers = [];
            for (var i = 0; i < tracks.length; i++) {
                renderers.push(this.catchRender(tracks[i], callId, direction));
            }
            return renderers;
        }
        // public freeRendersByCallId(callId:string,direction:"local"|"remote"|"all"="all"){

    }, {
        key: "freeRendersByCallId",
        value: function freeRendersByCallId(callId) {
            var direction = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "all";

            this.elementList.map(function (el) {
                if (el.callId == callId && (direction == "all" || direction == el.direction)) el.free();
            });
        }
    }, {
        key: "freeRenderByTrack",
        value: function freeRenderByTrack(track) {
            this.elementList.map(function (el) {
                if (el.track.id == track.id) el.free();
            });
        }
        // public getRendererByCallId(callId:string,direction:"local"|"remote"|"all"="all"){

    }, {
        key: "getRendererByCallId",
        value: function getRendererByCallId(callId) {
            var direction = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "all";

            this.elementList.filter(function (el) {
                if (el.callId == callId && (direction == "all" || direction == el.direction)) return true;
            });
        }
        /**
         * @hidden
         * @return {string}
         * @private
         */

    }, {
        key: "_traceName",
        value: function _traceName() {
            return 'ReusableStructure';
        }
    }]);

    return ReusableRenderer;
}();

exports.ReusableRenderer = ReusableRenderer;

/***/ }),
/* 35 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _get = function get(object, property, receiver) { if (object === null) object = Function.prototype; var desc = Object.getOwnPropertyDescriptor(object, property); if (desc === undefined) { var parent = Object.getPrototypeOf(object); if (parent === null) { return undefined; } else { return get(parent, property, receiver); } } else if ("value" in desc) { return desc.value; } else { var getter = desc.get; if (getter === undefined) { return undefined; } return getter.call(receiver); } };

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var __decorate = undefined && undefined.__decorate || function (decorators, target, key, desc) {
    var c = arguments.length,
        r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc,
        d;
    if ((typeof Reflect === "undefined" ? "undefined" : _typeof(Reflect)) === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);else for (var i = decorators.length - 1; i >= 0; i--) {
        if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    }return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var Call_1 = __webpack_require__(14);
var VoxSignaling_1 = __webpack_require__(1);
var CallManager_1 = __webpack_require__(6);
var Logger_1 = __webpack_require__(0);
var RemoteFunction_1 = __webpack_require__(2);
/**
 * @hidden
 */

var CallExServer = function (_Call_1$Call) {
    _inherits(CallExServer, _Call_1$Call);

    function CallExServer(id, dn, incoming, settings) {
        _classCallCheck(this, CallExServer);

        var _this = _possibleConstructorReturn(this, (CallExServer.__proto__ || Object.getPrototypeOf(CallExServer)).call(this, id, dn, incoming, settings));

        _this.settings.mode = Call_1.CallMode.SERVER;
        return _this;
    }

    _createClass(CallExServer, [{
        key: "answer",
        value: function answer(customData, extraHeaders) {
            _get(CallExServer.prototype.__proto__ || Object.getPrototypeOf(CallExServer.prototype), "answer", this).call(this, customData, extraHeaders);
            var extra = { tracks: this.peerConnection.getTrackKind() };
            VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.acceptCall, this.settings.id, CallManager_1.CallManager.cleanHeaders(extraHeaders), extra);
        }
        /**
         * @hidden
         * @return {string}
         * @private
         */

    }, {
        key: "_traceName",
        value: function _traceName() {
            return 'CallExServer';
        }
    }]);

    return CallExServer;
}(Call_1.Call);

__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALLEXSERVER)], CallExServer.prototype, "answer", null);
exports.CallExServer = CallExServer;

/***/ }),
/* 36 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _get = function get(object, property, receiver) { if (object === null) object = Function.prototype; var desc = Object.getOwnPropertyDescriptor(object, property); if (desc === undefined) { var parent = Object.getPrototypeOf(object); if (parent === null) { return undefined; } else { return get(parent, property, receiver); } } else if ("value" in desc) { return desc.value; } else { var getter = desc.get; if (getter === undefined) { return undefined; } return getter.call(receiver); } };

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var __decorate = undefined && undefined.__decorate || function (decorators, target, key, desc) {
    var c = arguments.length,
        r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc,
        d;
    if ((typeof Reflect === "undefined" ? "undefined" : _typeof(Reflect)) === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);else for (var i = decorators.length - 1; i >= 0; i--) {
        if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    }return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var Call_1 = __webpack_require__(14);
var VoxSignaling_1 = __webpack_require__(1);
var Logger_1 = __webpack_require__(0);
var UserMediaManager_1 = __webpack_require__(4);
var RemoteFunction_1 = __webpack_require__(2);
var Constants_1 = __webpack_require__(11);
/**
 * @hidden
 */

var CallExP2P = function (_Call_1$Call) {
    _inherits(CallExP2P, _Call_1$Call);

    function CallExP2P() {
        _classCallCheck(this, CallExP2P);

        return _possibleConstructorReturn(this, (CallExP2P.__proto__ || Object.getPrototypeOf(CallExP2P)).apply(this, arguments));
    }

    _createClass(CallExP2P, [{
        key: "answer",
        value: function answer(customData, extraHeaders, useVideo) {
            var _this2 = this;

            _get(CallExP2P.prototype.__proto__ || Object.getPrototypeOf(CallExP2P.prototype), "answer", this).call(this, customData, extraHeaders);
            if (typeof customData != 'undefined') {
                if (typeof extraHeaders == 'undefined') extraHeaders = {};
                extraHeaders[Constants_1.Constants.CALL_DATA_HEADER] = customData;
            }
            var uMedia = UserMediaManager_1.UserMediaManager.get();
            if (typeof useVideo == "undefined") uMedia.attachTo(this._peerConnection);else if (!useVideo.sendVideo) {
                uMedia.attachToSound(this._peerConnection);
            } else {
                uMedia.attachTo(this._peerConnection);
            }
            this._peerConnection.getLocalAnswer().then(function (activeLocalSD) {
                VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.acceptCall, _this2.id(), extraHeaders, activeLocalSD.sdp);
            });
        }
        /**
         * @hidden
         * @return {string}
         * @private
         */

    }, {
        key: "_traceName",
        value: function _traceName() {
            return 'CallExP2P';
        }
    }]);

    return CallExP2P;
}(Call_1.Call);

__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALLEXP2P)], CallExP2P.prototype, "answer", null);
exports.CallExP2P = CallExP2P;

/***/ }),
/* 37 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _get = function get(object, property, receiver) { if (object === null) object = Function.prototype; var desc = Object.getOwnPropertyDescriptor(object, property); if (desc === undefined) { var parent = Object.getPrototypeOf(object); if (parent === null) { return undefined; } else { return get(parent, property, receiver); } } else if ("value" in desc) { return desc.value; } else { var getter = desc.get; if (getter === undefined) { return undefined; } return getter.call(receiver); } };

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var __decorate = undefined && undefined.__decorate || function (decorators, target, key, desc) {
    var c = arguments.length,
        r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc,
        d;
    if ((typeof Reflect === "undefined" ? "undefined" : _typeof(Reflect)) === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);else for (var i = decorators.length - 1; i >= 0; i--) {
        if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    }return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var Call_1 = __webpack_require__(14);
var VoxSignaling_1 = __webpack_require__(1);
var Logger_1 = __webpack_require__(0);
var UserMediaManager_1 = __webpack_require__(4);
var RemoteFunction_1 = __webpack_require__(2);
var Constants_1 = __webpack_require__(11);
var Client_1 = __webpack_require__(3);
var index_1 = __webpack_require__(13);
var CallEvents_1 = __webpack_require__(9);
/**
 * @hidden
 */

var CallExMedia = function (_Call_1$Call) {
    _inherits(CallExMedia, _Call_1$Call);

    function CallExMedia() {
        _classCallCheck(this, CallExMedia);

        return _possibleConstructorReturn(this, (CallExMedia.__proto__ || Object.getPrototypeOf(CallExMedia)).apply(this, arguments));
    }

    _createClass(CallExMedia, [{
        key: "answer",
        value: function answer(customData, extraHeaders, useVideo) {
            var _this2 = this;

            _get(CallExMedia.prototype.__proto__ || Object.getPrototypeOf(CallExMedia.prototype), "answer", this).call(this, customData, extraHeaders);
            if (typeof customData != 'undefined') {
                if (typeof extraHeaders == 'undefined' || (typeof extraHeaders === "undefined" ? "undefined" : _typeof(extraHeaders)) !== "object") extraHeaders = {};
                extraHeaders[Constants_1.Constants.CALL_DATA_HEADER] = customData;
            }
            var appConfig = Client_1.Client.getInstance().config();
            if (appConfig.experiments && appConfig.experiments.hardware) {
                return new Promise(function (resolve, reject) {
                    if ((typeof useVideo === "undefined" ? "undefined" : _typeof(useVideo)) === "object") _this2.settings.videoDirections = useVideo;
                    index_1.default.StreamManager.get().getCallStream(_this2).then(function (stream) {
                        _this2._peerConnection.bindLocalMedia(stream);
                        _this2._peerConnection.getLocalAnswer().then(function (activeLocalSD) {
                            var extra = { tracks: _this2.peerConnection.getTrackKind() };
                            VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.acceptCall, _this2.id(), extraHeaders, activeLocalSD.sdp, extra);
                            resolve();
                        });
                    }).catch(function (e) {
                        return reject(e);
                    });
                });
            } else {
                var uMedia = UserMediaManager_1.UserMediaManager.get();
                if (typeof useVideo == "undefined") uMedia.attachTo(this._peerConnection);else if (!useVideo.sendVideo) {
                    this._peerConnection.setVideoFlags(useVideo);
                    this.settings.videoDirections = useVideo;
                    uMedia.attachToSound(this._peerConnection);
                } else {
                    this._peerConnection.setVideoFlags(useVideo);
                    this.settings.videoDirections = useVideo;
                    uMedia.attachTo(this._peerConnection);
                }
                this._peerConnection.getLocalAnswer().then(function (activeLocalSD) {
                    var extra = { tracks: _this2.peerConnection.getTrackKind() };
                    VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.acceptCall, _this2.id(), extraHeaders, activeLocalSD.sdp, extra);
                });
            }
        }
        /**
         * New version of setActive function - attach/detach by changes in SDP
         * @param newState
         */

    }, {
        key: "setActive",
        value: function setActive(newState) {
            var _this3 = this;

            if (newState === this.settings.active) {
                return new Promise(function (a, resolve) {
                    resolve({ name: CallEvents_1.CallEvents['Updated'], result: false, call: _this3 });
                });
            }
            this.settings.active = newState;
            return this.peerConnection.hold(!newState);
        }
        /**
         * @hidden
         * @return {string}
         * @private
         */

    }, {
        key: "_traceName",
        value: function _traceName() {
            return 'CallExMedia';
        }
    }]);

    return CallExMedia;
}(Call_1.Call);

__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], CallExMedia.prototype, "answer", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.CALL)], CallExMedia.prototype, "setActive", null);
exports.CallExMedia = CallExMedia;

/***/ }),
/* 38 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var __decorate = undefined && undefined.__decorate || function (decorators, target, key, desc) {
    var c = arguments.length,
        r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc,
        d;
    if ((typeof Reflect === "undefined" ? "undefined" : _typeof(Reflect)) === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);else for (var i = decorators.length - 1; i >= 0; i--) {
        if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    }return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var Logger_1 = __webpack_require__(0);
/**
 * @hidden
 */

var CodecSorterHelpers = function () {
    function CodecSorterHelpers() {
        _classCallCheck(this, CodecSorterHelpers);
    }

    _createClass(CodecSorterHelpers, [{
        key: "_traceName",

        /**
         * @hidden
         * @return {string}
         * @private
         */
        value: function _traceName() {
            return 'CodecSorterHelpers';
        }
    }], [{
        key: "H264Sorter",
        value: function H264Sorter(codecList, incoming) {
            if (!incoming) return new Promise(function (resolve) {
                for (var i = 0; i < codecList.sections.length; i++) {
                    if (codecList.sections[i].kind.toLowerCase() == "video") {
                        codecList.sections[i].codec.sort(function (a, b) {
                            if (a.toLowerCase().indexOf("h264") != -1 && a.toLowerCase().indexOf("uc") == -1) return -1;
                            if (b.toLowerCase().indexOf("h264") != -1 && b.toLowerCase().indexOf("uc") == -1) return 1;
                            return 0;
                        });
                    }
                }
                resolve(codecList);
            });else return new Promise(function (resolve) {
                for (var i = 0; i < codecList.sections.length; i++) {
                    if (codecList.sections[i].kind.toLowerCase() == "video") {
                        var codecCandidate = codecList.sections[i].codec.filter(function (item) {
                            return item.toLowerCase().indexOf("h264") != -1 && item.toLowerCase().indexOf("uc") == -1;
                        });
                        if (codecCandidate.length) codecList.sections[i].codec = codecCandidate;
                    }
                }
                resolve(codecList);
            });
        }
    }, {
        key: "VP8Sorter",
        value: function VP8Sorter(codecList, incoming) {
            return new Promise(function (resolve, reject) {
                for (var i = 0; i < codecList.sections.length; i++) {
                    if (codecList.sections[i].kind.toLowerCase() == "video") {
                        codecList.sections[i].codec.sort(function (a, b) {
                            if (a.toLowerCase().indexOf("vp8") != -1) return -1;
                            if (b.toLowerCase().indexOf("vp8") != -1) return 1;
                            return 0;
                        });
                    }
                }
                resolve(codecList);
            });
        }
    }]);

    return CodecSorterHelpers;
}();

__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], CodecSorterHelpers, "H264Sorter", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], CodecSorterHelpers, "VP8Sorter", null);
exports.CodecSorterHelpers = CodecSorterHelpers;

/***/ }),
/* 39 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

Object.defineProperty(exports, "__esModule", { value: true });
var SignalingDTMFSender_1 = __webpack_require__(17);
var Client_1 = __webpack_require__(3);
var Logger_1 = __webpack_require__(0);
/**
 * @hidden
 */

var Webkit = function () {
    function Webkit() {
        _classCallCheck(this, Webkit);
    }

    _createClass(Webkit, [{
        key: "_traceName",

        /**
         * @hidden
         * @return {string}
         * @private
         */
        value: function _traceName() {
            return 'Webkit';
        }
    }], [{
        key: "attachStream",
        value: function attachStream(stream, element) {
            try {
                if (typeof element.srcObject !== "undefined") element.srcObject = stream;else element.src = URL.createObjectURL(stream);
                element.load();
                if (element instanceof HTMLVideoElement) element.play().catch(function (e) {});else {
                    element.play();
                    var sinkId = Client_1.Client.getInstance()._defaultSinkId;
                    if (sinkId != null) element.setSinkId(sinkId);
                }
            } catch (e) {
                Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.USERMEDIA, 'Webkit: ', Logger_1.LogLevel.WARNING, JSON.stringify(e));
            }
        }
    }, {
        key: "detachStream",
        value: function detachStream(element) {
            if (element instanceof HTMLVideoElement) {
                var promise = element.pause();
                if (typeof promise !== "undefined") promise.catch(function (e) {});
            } else element.pause();
            element.src = "";
        }
    }, {
        key: "getDTMFSender",
        value: function getDTMFSender(pc, callId) {
            if (!!pc.createDTMFSender) {
                var audioTracks = [];
                pc.getLocalStreams().forEach(function (stream) {
                    stream.getAudioTracks().forEach(function (track) {
                        audioTracks.push(track);
                    });
                });
                if (audioTracks.length) {
                    return pc.createDTMFSender(audioTracks[0]);
                }
            } else return new SignalingDTMFSender_1.SignalingDTMFSender(callId);
        }
    }, {
        key: "getUserMedia",
        value: function getUserMedia(constraint) {
            return navigator.mediaDevices.getUserMedia(constraint);
        }
    }, {
        key: "screenSharingSupported",
        value: function screenSharingSupported() {
            return new Promise(function (resolve, reject) {
                window.postMessage('VoximplantWebsdkCheckExtension', '*');
                var listener = function listener(event) {
                    if (event.origin === window.location.origin || event.data === 'VoximplantWebsdkExtensionLoaded') {
                        window.removeEventListener('message', listener);
                        clearTimeout(failTimer);
                        resolve(true);
                    }
                };
                var failTimer = setTimeout(function () {
                    window.removeEventListener('message', listener);
                    resolve(false);
                }, 800);
                window.addEventListener('message', listener);
            });
        }
    }, {
        key: "getScreenMedia",
        value: function getScreenMedia() {
            return new Promise(function (resolve, reject) {
                window.postMessage('voximplantWebsdkGetSourceId', '*');
                var listener = function listener(event) {
                    if (!event.data || event.origin !== window.location.origin) return;
                    if (!event.data.result) return;
                    if (event.data.result === 'err') return reject(new Error(event.data.reason));
                    if (event.data.result === 'ok' && typeof event.data.sourceId !== "undefined") {
                        window.removeEventListener('message', listener);
                        var mediaParams = {
                            audio: false,
                            video: {
                                mandatory: {
                                    chromeMediaSource: 'desktop',
                                    maxWidth: screen.width > 1920 ? screen.width : 1920,
                                    maxHeight: screen.height > 1080 ? screen.height : 1080,
                                    chromeMediaSourceId: event.data.sourceId
                                },
                                optional: [{ googTemporalLayeredScreencast: true }]
                            }
                        };
                        Logger_1.LogManager.get().writeMessage(Logger_1.LogCategory.USERMEDIA, '[constraints]', Logger_1.LogLevel.TRACE, JSON.stringify(mediaParams));
                        navigator.mediaDevices.getUserMedia(mediaParams).then(resolve, reject);
                    }
                };
                window.addEventListener('message', listener);
            });
        }
    }, {
        key: "getRTCStats",
        value: function getRTCStats(pc) {
            return new Promise(function (resolve, reject) {
                var resultArray = [];
                pc.getStats(null).then(function (e) {
                    e.forEach(function (result) {
                        if (result.type == "outbound-rtp" || result.type == "inbound-rtp") {
                            resultArray.push(result);
                        }
                    });
                    resolve(resultArray);
                    return;
                }).catch(reject);
            });
        }
    }]);

    return Webkit;
}();

exports.Webkit = Webkit;

/***/ }),
/* 40 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var __decorate = undefined && undefined.__decorate || function (decorators, target, key, desc) {
    var c = arguments.length,
        r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc,
        d;
    if ((typeof Reflect === "undefined" ? "undefined" : _typeof(Reflect)) === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);else for (var i = decorators.length - 1; i >= 0; i--) {
        if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    }return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var PeerConnection_1 = __webpack_require__(19);
var BrowserSpecific_1 = __webpack_require__(5);
var UserMediaManager_1 = __webpack_require__(4);
var Renderer_1 = __webpack_require__(20);
var PCFactory_1 = __webpack_require__(8);
var Logger_1 = __webpack_require__(0);
var VoxSignaling_1 = __webpack_require__(1);
var CallManager_1 = __webpack_require__(6);
var RemoteFunction_1 = __webpack_require__(2);
var CodecSorter_1 = __webpack_require__(41);
var CallEvents_1 = __webpack_require__(9);
var CallstatsIo_1 = __webpack_require__(16);
var Constants_1 = __webpack_require__(11);
var SDPMuggle_1 = __webpack_require__(24);
var Client_1 = __webpack_require__(3);
var ReInviteQ_1 = __webpack_require__(23);
var index_1 = __webpack_require__(13);
var RTCSdpType;
(function (RTCSdpType) {
    RTCSdpType[RTCSdpType["offer"] = "offer"] = "offer";
    RTCSdpType[RTCSdpType["answer"] = "answer"] = "answer";
    RTCSdpType[RTCSdpType["pranswer"] = "pranswer"] = "pranswer";
    RTCSdpType[RTCSdpType["rollback"] = "rollback"] = "rollback";
})(RTCSdpType || (RTCSdpType = {}));
var RTCIceRole;
(function (RTCIceRole) {
    RTCIceRole[RTCIceRole["controlling"] = "controlling"] = "controlling";
    RTCIceRole[RTCIceRole["controlled"] = "controlled"] = "controlled";
})(RTCIceRole || (RTCIceRole = {}));
//WebRTC implementation of PeerConnection
/**
 * @hidden
 */

var WebRTCPC = function (_PeerConnection_1$Pee) {
    _inherits(WebRTCPC, _PeerConnection_1$Pee);

    function WebRTCPC(id, mode, videoEnabled) {
        _classCallCheck(this, WebRTCPC);

        var _this = _possibleConstructorReturn(this, (WebRTCPC.__proto__ || Object.getPrototypeOf(WebRTCPC)).call(this, id, mode, videoEnabled));

        _this.iceTimer = null;
        _this.needTransportRestart = true;
        /**
         * Max time to ICE
         * @type {number}
         */
        _this.ICE_TIMEOUT = 20000;
        /**
         * max renegotiation time
         * @type {number}
         */
        _this.RENEGOTIATION_TIMEOUT = 15000;
        _this._canReInvite = function () {
            return _this.impl.iceConnectionState === 'connected' || _this.impl.iceConnectionState === 'completed';
        };
        var cfg = PCFactory_1.PCFactory.get().iceConfig;
        // this.impl = new RTCPeerConnection(cfg, {'optional': [{'DtlsSrtpKeyAgreement': 'true'}]});
        // this.impl = new RTCPeerConnection(null, {'optional': [{'DtlsSrtpKeyAgreement': 'true'}]});
        var xconf = cfg;
        // if(xconf===null)
        //    xconf = <RTCConfiguration>{gatherPolicy: "all", iceServers: []};
        if (typeof xconf === "undefined" || xconf === null) {
            xconf = { gatherPolicy: "all", iceServers: [] };
        }
        xconf.bundlePolicy = 'max-compat';
        _this.impl = new RTCPeerConnection(xconf);
        //this.impl = new RTCPeerConnection(<RTCConfiguration>{gatherPolicy: "all", iceServers: [{ "urls": ["turn:192.168.15.40:3478?transport=udp"], "username": "redmond", "credential": "redmond123" }]}, {'optional': [{'DtlsSrtpKeyAgreement': 'true'}]})
        // FF 44 implementation
        if (typeof _this.impl.ontrack !== "undefined") {
            _this.impl.ontrack = function (e) {
                return _this.onAddTrack(e);
            };
        } else if (typeof _this.impl.onaddtrack !== "undefined") {
            _this.impl.onaddtrack = function (e) {
                return _this.onAddTrack(e);
            };
        } else {
            _this.impl.onaddstream = function (e) {
                return _this.onAddStream(e);
            };
        }
        _this.impl.onicecandidate = function (ev) {
            _this.onICECandidate(ev["candidate"]);
        };
        _this.impl.oniceconnectionstatechange = function (e) {
            if (_this.impl.iceConnectionState === "completed" || _this.impl.iceConnectionState === "connected") {
                _this.iceTimer && clearTimeout(_this.iceTimer);
                _this.iceTimer = null;
                if (_this.reInviteQ) _this.reInviteQ.runNext();
            }
        };
        _this.rtpSenders = [];
        _this.renegotiationInProgress = false;
        _this.impl.onnegotiationneeded = function (e) {
            return _this.onRenegotiation();
        };
        _this.impl.onsignalingstatechange = function (e) {
            return _this.onSignalingStateChange();
        };
        _this.impl.oniceconnectionstatechange = function (e) {
            return _this.onConnectionChange();
        };
        _this.iceRole = RTCIceRole.controlling;
        _this._remoteStreams = [];
        _this.banReinviteAnswer = false;
        _this._call = CallManager_1.CallManager.get().calls[_this.id];
        //Check if call not active, set HOLD
        if (typeof _this._call != "undefined") _this.onHold = !_this._call.active();else _this.onHold = false;
        _this.rtcCollectingCycle = setInterval(function () {
            _this.getPCStats();
        }, CallManager_1.CallManager.get().rtcStatsCollectionInterval);
        // Callstats.io integration
        if (typeof _this._call !== "undefined") {
            var CSIOID = _this._call.headers()[Constants_1.Constants.CALLSTATSIOID_HEADER];
            if (typeof CSIOID === "undefined") CSIOID = _this._call.id();
            CallstatsIo_1.CallstatsIo.get().addNewFabric(_this.impl, _this._call.number(), videoEnabled ? CallstatsIo_1.CallstatsIoFabricUsage.multiplex : CallstatsIo_1.CallstatsIoFabricUsage.audio, CSIOID);
        }
        _this.needTransportRestart = false;
        if (id !== "_default" && CallManager_1.CallManager.get().calls[id]) _this.reInviteQ = new ReInviteQ_1.ReInviteQ(CallManager_1.CallManager.get().calls[id], _this._canReInvite);
        return _this;
    }

    _createClass(WebRTCPC, [{
        key: "onSignalingStateChange",
        value: function onSignalingStateChange() {
            this.log.info("Signal state changed to " + this.impl.signalingState + " for PC:" + this.id);
            if (this.impl.signalingState === "stable") {
                //TODO: there was screen sharing
            }
        }
    }, {
        key: "getPCStats",
        value: function getPCStats() {
            var _this2 = this;

            BrowserSpecific_1.default.getRTCStats(this.impl).then(function (statistic) {
                if (typeof _this2._call !== "undefined") _this2._call.dispatchEvent({ name: 'RTCStatsReceived', stats: statistic });
            });
        }
    }, {
        key: "onConnectionChange",
        value: function onConnectionChange() {
            if (this.impl.iceConnectionState === "completed") {
                if (typeof this._call !== "undefined") {
                    this._call.dispatchEvent({ name: 'ICECompleted', call: this._call });
                }
            }
            if (this.impl.iceConnectionState === "completed" || this.impl.iceConnectionState === "connected") {
                this.iceTimer && clearTimeout(this.iceTimer);
                this.iceTimer = null;
                if (this.reInviteQ) this.reInviteQ.runNext();
            }
        }
        /**
         * Testing variant for renegotiation function
         *
         */

    }, {
        key: "onRenegotiation",
        value: function onRenegotiation() {
            var _this3 = this;

            if (typeof this.impl === "undefined") return;
            if (this.impl.connectionState === "disconnected" || this.impl.connectionState === "failed") {
                this.log.info("Renegotiation requested on closed PeerConnection");
                return;
            }
            if (this.impl.localDescription === null) {
                this.log.info("Renegotiation needed, but no local SD, skipping");
                return;
            }
            if (this.impl.iceConnectionState !== "connected" && this.impl.iceConnectionState !== "completed") {
                this.log.info("Renegotiation requested while ice state is " + this.impl.iceConnectionState + ". Postponing");
                setTimeout(this.onRenegotiation, 100);
                return;
            }
            if (this.renegotiationInProgress) {
                this.log.info("Renegotiation in progress. Queueing");
                return;
            } else {
                this.log.info("Renegotiation started");
            }
            if (this.renegotiationInProgress === false) {
                this.renegotiationInProgress = true;
                var offerOption = this.getReceiveOptions();
                this.updateHoldState();
                this.impl.createOffer(offerOption).then(function (sdp) {
                    return _this3.codecRearrange(sdp);
                }).then(function (sdp) {
                    var tempsdp = { type: sdp.type, sdp: sdp.sdp };
                    tempsdp = PCFactory_1.PCFactory.get().addBandwidthParams(tempsdp);
                    tempsdp = SDPMuggle_1.SDPMuggle.removeTelephoneEvents(tempsdp);
                    tempsdp = SDPMuggle_1.SDPMuggle.removeDoubleOpus(tempsdp);
                    tempsdp = SDPMuggle_1.SDPMuggle.fixVideoRecieve(tempsdp, _this3.videoEnabled.receiveVideo);
                    return tempsdp;
                }).then(function (sdp) {
                    _this3.srcLocalSDP = sdp.sdp;
                    _this3.pendingOffer = sdp;
                    return sdp;
                }).then(function () {
                    var extra = { tracks: _this3._getTrackKind() };
                    VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.reInvite, _this3._call.id(), {}, _this3.pendingOffer.sdp, extra);
                }).catch(function (e) {
                    _this3.log.error("Error when renegatiation start " + e.message);
                });
            } else {
                this.log.error("Another renegatiation in progress");
            }
        }
    }, {
        key: "getReceiveOptions",
        value: function getReceiveOptions() {
            return {
                "offerToReceiveAudio": !this.onHold ? 1 : 0,
                "offerToReceiveVideo": this.videoEnabled.receiveVideo && !this.onHold ? 1 : 0
            };
        }
    }, {
        key: "updateHoldState",
        value: function updateHoldState() {
            var _this4 = this;

            this.impl.getLocalStreams().forEach(function (stream) {
                stream.getTracks().forEach(function (track) {
                    track.enabled = !_this4.onHold;
                });
            });
            this.impl.getRemoteStreams().forEach(function (stream) {
                stream.getTracks().forEach(function (track) {
                    track.enabled = !_this4.onHold;
                });
            });
        }
        /**
         * Callback to add new local candidates to send
         * @param cand
         */

    }, {
        key: "onICECandidate",
        value: function onICECandidate(cand) {
            if (cand && cand !== null) {
                this.sendLocalCandidateToPeer("a=" + cand.candidate, cand.sdpMLineIndex);
            } else {
                this.log.info("End of candidates");
            }
        }
        /**
         * Callback to add new Track
         * @param e
         */

    }, {
        key: "onAddTrack",
        value: function onAddTrack(e) {
            var type = e.track.kind;
            var newStream = new MediaStream([e.track]);
            var renderer = this.renderStream(newStream, type);
            if (type === "video" && typeof this.videoRendered === "undefined") this.videoRendered = renderer;else if (typeof this.audioRendered === "undefined") this.audioRendered = renderer;
            this.updateHoldState();
            this._remoteStreams.push(newStream);
            this.renderers.push(renderer);
            this.mapRenderer(newStream, renderer);
        }
    }, {
        key: "onAddStream",
        value: function onAddStream(e) {
            var type = UserMediaManager_1.UserMediaManager.getVideoTracks(e.stream).length ? "video" : "audio";
            var renderer = this.renderStream(e.stream, type);
            if (type === "video" && typeof this.videoRendered === "undefined") this.videoRendered = renderer;else if (typeof this.audioRendered === "undefined") this.audioRendered = renderer;
            this.updateHoldState();
            this._remoteStreams.push(e.stream);
            this.renderers.push(renderer);
            this.mapRenderer(e.stream, renderer);
        }
    }, {
        key: "mapRenderer",
        value: function mapRenderer(stream, renderer) {
            var _this5 = this;

            stream.getTracks().forEach(function (track) {
                track.onended = function () {
                    _this5.checkStreamActive(stream, renderer);
                };
            });
        }
    }, {
        key: "checkStreamActive",
        value: function checkStreamActive(stream, renderer) {
            if (!stream.getTracks().some(function (item) {
                return item.readyState === 'live';
            })) {
                if (this._call) this._call.dispatchEvent({
                    name: CallEvents_1.CallEvents.MediaElementRemoved,
                    call: this._call,
                    elementId: renderer.id,
                    type: stream.getVideoTracks().length ? 'video' : 'audio'
                });
                Renderer_1.Renderer.get().releaseElement(renderer);
            }
        }
    }, {
        key: "renderStream",
        value: function renderStream(stream, type) {
            var renderToLocal = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

            var element = document.body;
            var tracks = stream.getTracks();
            var containerId = Client_1.Client.getInstance().remoteVideoContainerId;
            if (renderToLocal) {
                containerId = Client_1.Client.getInstance().localVideoContainerId;
            }
            if ((type === "video" || type === "sharing") && typeof containerId !== "undefined") element = document.getElementById(containerId);
            var renderer = Renderer_1.Renderer.get().getElement(tracks[0].id, type === "video" || type === "sharing", element);
            if (stream.oninactive) stream.oninactive(function () {
                BrowserSpecific_1.default.detachMedia(renderer);
            });
            if (typeof this.sinkId !== "undefined") {
                renderer.setSinkId(this.sinkId);
            }
            BrowserSpecific_1.default.attachMedia(stream, renderer);
            if (typeof this._call !== "undefined" && !renderToLocal) {
                this._call.dispatchEvent({
                    name: CallEvents_1.CallEvents.MediaElementCreated,
                    call: this._call,
                    stream: stream,
                    element: renderer,
                    type: type
                });
            } else if (renderToLocal) {
                this._call.dispatchEvent({
                    name: CallEvents_1.CallEvents.LocalVideoStreamAdded,
                    stream: stream,
                    element: renderer,
                    type: type
                });
            }
            return renderer;
        }
    }, {
        key: "checkMediaAttached",
        value: function checkMediaAttached(type) {
            return this._remoteStreams.length > 0 && this._remoteStreams.reduce(function (last, item) {
                if (type === "audio") return last && item.getAudioTracks().length > 0;else if (type === "video") return last && item.getVideoTracks().length > 0;else return last;
            }, true);
        }
    }, {
        key: "wireRemoteStream",
        value: function wireRemoteStream(force) {
            //TODO: Not implemented
            return new Promise(function (resolve, reject) {
                reject(new Error('Not implemented'));
            });
        }
    }, {
        key: "_processRemoteAnswer",
        value: function _processRemoteAnswer(headers, sdp) {
            var _this6 = this;

            this.iceTimer = setTimeout(function () {
                _this6._call.notifyICETimeout();
            }, this.ICE_TIMEOUT);
            this.pendingEvent = [headers, sdp];
            if (this.impl.remoteDescription !== null) if (this.impl.remoteDescription.sdp != "") return;
            var d = { sdp: sdp, type: RTCSdpType.answer };
            this.srcRemoteSDP = sdp;
            d = SDPMuggle_1.SDPMuggle.removeTIAS(d);
            return this.impl.setRemoteDescription(d);
        }
    }, {
        key: "_getLocalOffer",
        value: function _getLocalOffer() {
            var _this7 = this;

            this.iceRole = RTCIceRole.controlling;
            return new Promise(function (resolve, reject) {
                var rtcOfferOptions = _this7.getReceiveOptions();
                _this7.impl.createOffer(rtcOfferOptions).then(function (sdp) {
                    var tempsdp = { type: sdp.type, sdp: sdp.sdp };
                    tempsdp = PCFactory_1.PCFactory.get().addBandwidthParams(tempsdp);
                    return _this7.codecRearrange(tempsdp);
                }).then(function (sdp) {
                    _this7.srcLocalSDP = sdp.sdp;
                    return _this7.impl.setLocalDescription(sdp);
                }).then(function () {
                    resolve(_this7.impl.localDescription);
                }).catch(function (e) {
                    reject(e);
                });
            });
        }
    }, {
        key: "_getLocalAnswer",
        value: function _getLocalAnswer() {
            var _this8 = this;

            this.iceRole = RTCIceRole.controlled;
            return new Promise(function (resolve, reject) {
                var rtcAnswerOptions = { mandatory: _this8.getReceiveOptions() };
                _this8.impl.createAnswer(rtcAnswerOptions).then(function (sdp) {
                    var tempsdp = { type: sdp.type, sdp: sdp.sdp };
                    tempsdp = PCFactory_1.PCFactory.get().addBandwidthParams(tempsdp);
                    tempsdp = SDPMuggle_1.SDPMuggle.fixVideoRecieve(tempsdp, _this8.videoEnabled.receiveVideo);
                    return _this8.codecRearrange(tempsdp);
                }).then(function (sdp) {
                    _this8.srcLocalSDP = sdp.sdp;
                    return _this8.impl.setLocalDescription(sdp);
                }).then(function () {
                    resolve({ type: RTCSdpType.answer, sdp: _this8.impl.localDescription.sdp });
                }).catch(function (e) {
                    reject(e);
                });
            });
        }
    }, {
        key: "_setRemoteDescription",
        value: function _setRemoteDescription(sdp) {
            var d = new RTCSessionDescription({ sdp: sdp, type: RTCSdpType.offer });
            d = SDPMuggle_1.SDPMuggle.removeTIAS(d);
            this.srcRemoteSDP = sdp;
            return this.impl.setRemoteDescription(d);
        }
    }, {
        key: "_processRemoteOffer",
        value: function _processRemoteOffer(sdp) {
            var _this9 = this;

            this.iceRole = RTCIceRole.controlled;
            return new Promise(function (resolve, reject) {
                var d = new RTCSessionDescription({ sdp: sdp, type: RTCSdpType.offer });
                _this9.srcRemoteSDP = sdp;
                d = SDPMuggle_1.SDPMuggle.removeTIAS(d);
                _this9.impl.setRemoteDescription(d).then(function () {
                    var rtcAnswerOptions = { mandatory: _this9.getReceiveOptions() };
                    return _this9.impl.createAnswer(rtcAnswerOptions);
                }).then(function (sdp) {
                    return _this9.codecRearrange(sdp);
                }).then(function (sdp) {
                    _this9.srcLocalSDP = sdp.sdp;
                    return _this9.impl.setLocalDescription(sdp);
                }).then(function () {
                    resolve(_this9.impl.localDescription.sdp);
                }).catch(function (e) {
                    reject(e);
                });
            });
        }
        /**
         * Close curent PeerConnection
         *
         * @private
         */

    }, {
        key: "_close",
        value: function _close() {
            var _this10 = this;

            clearInterval(this.rtcCollectingCycle);
            this.impl.onnegotiationneeded = null;
            var appConfig = Client_1.Client.getInstance().config();
            if (this.impl.removeTrack) this.rtpSenders.forEach(function (sender) {
                _this10.impl.removeTrack(sender);
            });else this.impl.getLocalStreams().forEach(function (stream) {
                if (!appConfig.experiments || !appConfig.experiments.hardware) {
                    stream.getTracks().forEach(function (track) {
                        track.stop();
                        stream.removeTrack(track);
                    });
                }
                _this10.impl.removeStream(stream);
            });
            if (appConfig.experiments && appConfig.experiments.hardware && typeof this._call !== "undefined") {
                index_1.default.StreamManager.get().remCallStream(this._call);
            }
            this.impl.close();
            if (typeof this._call !== "undefined") CallstatsIo_1.CallstatsIo.get().sendFabricEvent(this.impl, CallstatsIo_1.CallstatsIoFabricEvent.fabricTerminated, this._call.id());
            this._localStream = null;
            this._remoteStreams = null;
            // this._unbindLocalMegia();
            // this.renderers.forEach((item)=>{
            //     Renderer.get().releaseElement(item);
            // })
        }
        /**
         * Add remote candidate from peer
         *
         * @param candidate
         * @param mLineIndex
         * @returns {Promise<void>}
         * @private
         */

    }, {
        key: "_addRemoteCandidate",
        value: function _addRemoteCandidate(candidate, mLineIndex) {
            var _this11 = this;

            return new Promise(function (resolve, reject) {
                try {
                    _this11.impl.addIceCandidate(new RTCIceCandidate({
                        candidate: candidate.substring(2),
                        sdpMLineIndex: mLineIndex
                    })).then(function () {
                        resolve();
                    }).catch(function () {
                        resolve();
                    });
                } catch (e) {
                    resolve();
                }
            });
        }
        /**
         * Bind local media to this PeerConnection
         * and create native DTMFSender, if can
         *
         * @private
         */

    }, {
        key: "_bindLocalMedia",
        value: function _bindLocalMedia() {
            var _this12 = this;

            if (typeof this._localStream !== "undefined") {
                if (BrowserSpecific_1.default.getWSVendor() === "firefox") {
                    this._localStream.getTracks().forEach(function (track) {
                        _this12.rtpSenders.push(_this12.impl.addTrack(track, _this12._localStream));
                    });
                } else {
                    this.impl.addStream(this._localStream);
                }
                if (!!this._call) {
                    var newSender = BrowserSpecific_1.default.getDTMFSender(this.impl, this._call.id());
                    if (newSender) this.dtmfSender = newSender;
                }
                this.updateHoldState();
            }
        }
    }, {
        key: "_addMediaStream",
        value: function _addMediaStream(stream) {
            var _this13 = this;

            var showLocalView = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

            if (typeof this.shareScreenMedia === "undefined") this.shareScreenMedia = [];
            if (typeof this.shareScreenMediaTracks === "undefined") this.shareScreenMediaTracks = [];
            this.shareScreenMedia.push(stream);
            stream.getTracks().forEach(function (track) {
                _this13.shareScreenMediaTracks.push(track.id);
            });
            if (BrowserSpecific_1.default.getWSVendor() === "firefox") {
                stream.getTracks().forEach(function (track) {
                    var newStream = new MediaStream([track]);
                    _this13.rtpSenders.push(_this13.impl.addTrack(track, newStream));
                });
            } else {
                this.impl.addStream(stream);
            }
            if (showLocalView) {
                var renderer = this.renderStream(stream, "sharing", true);
                this.renderers.push(renderer);
            }
        }
        /**
         * Unbind local media from this PeerConnection
         *
         * @private
         */

    }, {
        key: "_unbindLocalMegia",
        value: function _unbindLocalMegia() {
            var _this14 = this;

            this.needTransportRestart = true;
            if (BrowserSpecific_1.default.getWSVendor() === "firefox") {
                this.rtpSenders.forEach(function (sender) {
                    if (_this14.shareScreenMediaTracks) {
                        if (_this14.shareScreenMediaTracks.indexOf(sender.track.id) === -1) {
                            _this14.impl.removeTrack(sender);
                        }
                    } else {
                        _this14.impl.removeTrack(sender);
                    }
                });
            } else {
                if (this._localStream) this.impl.removeStream(this._localStream);
            }
            if (this._localStream) this._localStream.getTracks().forEach(function (track) {
                track.stop();
                _this14._localStream.removeTrack(track);
            });
            this._localStream = null;
        }
        /**
         * Action for ReInvite message from server
         * if incoming sdp empty: start renegotiation - else create answer
         *
         * @author Igor Sheko
         * @param headers
         * @param sdp
         * @returns {Promise<void>|Promise}
         * @private
         */

    }, {
        key: "_handleReinvite",
        value: function _handleReinvite(headers, sdp) {
            var _this15 = this;

            return new Promise(function (resolve, reject) {
                if (_this15.banReinviteAnswer) {
                    reject(new Error());
                }
                if (_this15.renegotiationInProgress === false) {
                    _this15.renegotiationInProgress = true;
                    var d = { sdp: sdp, type: RTCSdpType.offer };
                    _this15.srcRemoteSDP = sdp;
                    d = SDPMuggle_1.SDPMuggle.removeTIAS(d);
                    _this15.impl.setRemoteDescription(d).then(function () {
                        var rtcAnswerOptions = { mandatory: _this15.getReceiveOptions() };
                        _this15.impl.createAnswer(rtcAnswerOptions).then(function (localSDP) {
                            var tempsdp = { type: localSDP.type, sdp: localSDP.sdp };
                            tempsdp = SDPMuggle_1.SDPMuggle.removeDoubleOpus(tempsdp);
                            tempsdp = SDPMuggle_1.SDPMuggle.fixVideoRecieve(tempsdp, _this15.videoEnabled.receiveVideo);
                            _this15.srcLocalSDP = tempsdp.sdp;
                            try {
                                _this15.impl.setLocalDescription(tempsdp).then(function () {
                                    var extra = { tracks: _this15._getTrackKind() };
                                    VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.acceptReInvite, _this15._call.id(), headers, _this15.impl.localDescription.sdp, extra);
                                    _this15.renegotiationInProgress = false;
                                    _this15._call.dispatchEvent({ name: CallEvents_1.CallEvents.Updated, result: true, call: _this15._call });
                                    _this15.updateHoldState();
                                    resolve();
                                });
                            } catch (e) {
                                _this15.renegotiationInProgress = false;
                                reject(e);
                            }
                        });
                    });
                } else if (_this15.renegotiationInProgress === true) {
                    //get remoteAnswer
                    var _d = { sdp: sdp, type: RTCSdpType.answer };
                    _this15.renegotiationInProgress = false;
                    _this15.srcRemoteSDP = sdp;
                    _d = SDPMuggle_1.SDPMuggle.removeTIAS(_d);
                    _this15.impl.setLocalDescription(_this15.pendingOffer).then(function () {
                        try {
                            _this15.impl.setRemoteDescription(_d).then(function () {
                                _this15._call.dispatchEvent({ name: CallEvents_1.CallEvents.Updated, result: true, call: _this15._call });
                                _this15.updateHoldState();
                                resolve();
                            });
                        } catch (e) {
                            _this15._call.dispatchEvent({ name: CallEvents_1.CallEvents.Updated, result: false, call: _this15._call });
                            _this15.renegotiationInProgress = false;
                            _this15.log.error(JSON.stringify(e));
                            reject(e);
                        }
                        clearTimeout(_this15.renegotiationTimer);
                    });
                } else {
                    reject(new Error("Universe was broken!"));
                }
            });
        }
        /**
         * Promise to rearrange codec by user
         *
         * @author Igor Sheko
         * @param sdp
         * @returns {Promise<RTCSessionDescription>|Promise}
         */

    }, {
        key: "codecRearrange",
        value: function codecRearrange(sdp) {
            var _this16 = this;

            return new Promise(function (resolve, reject) {
                var call = CallManager_1.CallManager.get().calls[_this16.id];
                if (typeof call !== "undefined") {
                    var codecSorter = new CodecSorter_1.CodecSorter(sdp.sdp);
                    var userCodecList = codecSorter.getUserCodecList();
                    if (typeof call.rearangeCodecs !== "undefined") {
                        call.rearangeCodecs(userCodecList, call.settings.incoming).then(function (newCodecList) {
                            codecSorter.setUserCodecList(newCodecList);
                            resolve({ type: sdp.type, sdp: codecSorter.getSDP() });
                        }, function (e) {
                            _this16.log.error(JSON.stringify(e));
                            reject(e);
                        });
                    } else {
                        _this16.log.info("No sdp transformer registered");
                        codecSorter.setUserCodecList(userCodecList);
                        resolve({ type: sdp.type, sdp: codecSorter.getSDP() });
                    }
                } else {
                    resolve(sdp);
                }
            });
        }
        /**
         * Sed DTMF via WebRTC if can
         *
         * @author Igor Sheko
         * @param key
         * @param duration
         * @param gap
         * @private
         */

    }, {
        key: "_sendDTMF",
        value: function _sendDTMF(key, duration, gap) {
            if (typeof this.dtmfSender !== "undefined") {
                this.dtmfSender.insertDTMF(key, duration, gap);
            }
        }
        /**
         * Hold call by remove local stream and start renegotiation process
         * Hold call by add local stream and start renegotiation process
         * @param newState
         * @returns {undefined}
         * @private
         */

    }, {
        key: "_hold",
        value: function _hold(newState) {
            CallstatsIo_1.CallstatsIo.get().sendFabricEvent(this.impl, newState ? CallstatsIo_1.CallstatsIoFabricEvent.fabricHold : CallstatsIo_1.CallstatsIoFabricEvent.fabricResume, this._call.id());
            this.onHold = newState;
            this.onRenegotiation();
        }
    }, {
        key: "_getDirections",
        value: function _getDirections() {
            var directions = {};
            directions['local'] = SDPMuggle_1.SDPMuggle.detectDirections(this.impl.localDescription.sdp);
            directions['remote'] = SDPMuggle_1.SDPMuggle.detectDirections(this.impl.remoteDescription.sdp);
            return directions;
        }
    }, {
        key: "_getStreamActivity",
        value: function _getStreamActivity() {
            var status = {};
            status['local'] = this.getMediaActivity(this.impl.getLocalStreams());
            status['remote'] = this.getMediaActivity(this.impl.getRemoteStreams());
            return status;
        }
    }, {
        key: "getMediaActivity",
        value: function getMediaActivity(streams) {
            return streams.map(function (item) {
                return item.getTracks().map(function (x_item) {
                    return {
                        id: x_item.id,
                        kind: x_item.kind,
                        mutted: x_item.muted,
                        active: x_item.enabled,
                        label: x_item.label
                    };
                });
            });
        }
    }, {
        key: "_hdnFRSPrep",
        value: function _hdnFRSPrep() {
            this.banReinviteAnswer = true;
        }
    }, {
        key: "_hdnFRS",
        value: function _hdnFRS() {
            this.renegotiationInProgress = false;
            this.onRenegotiation();
        }
    }, {
        key: "_getTrackKind",
        value: function _getTrackKind() {
            var _this17 = this;

            var tracks = {};
            if (_typeof(this.impl.getSenders)) {
                this.impl.getSenders().forEach(function (sender) {
                    if (sender.track) {
                        if (sender.track.kind !== "video") tracks[sender.track.id] = sender.track.kind;else if (_this17.shareScreenMedia && _this17.shareScreenMedia.some(function (sStream) {
                            return sStream.getTracks().some(function (sTrack) {
                                return sTrack.id === sender.track.id;
                            });
                        })) tracks[sender.track.id] = "sharing";else tracks[sender.track.id] = "video";
                    }
                });
            } else {
                this.impl.getLocalStreams().forEach(function (stream) {
                    stream.getTracks().forEach(function (track) {
                        if (track.kind !== "video") tracks[track.id] = track.kind;else if (_this17.shareScreenMedia && _this17.shareScreenMedia.some(function (sStream) {
                            return sStream.getTracks().some(function (sTrack) {
                                return sTrack.id === track.id;
                            });
                        })) tracks[track.id] = "sharing";else tracks[track.id] = "video";
                    });
                });
            }
            return tracks;
        }
    }, {
        key: "_stopSharing",
        value: function _stopSharing() {
            var _this18 = this;

            if (typeof this.shareScreenMedia === "undefined") return;
            if (BrowserSpecific_1.default.getWSVendor() === "firefox") {
                this.impl.getSenders().forEach(function (sender) {
                    if (_this18.shareScreenMediaTracks.indexOf(sender.track.id) !== -1) _this18.impl.removeTrack(sender);
                });
            } else {
                this.shareScreenMedia.forEach(function (stream) {
                    _this18.impl.removeStream(stream);
                });
            }
            this.shareScreenMedia.forEach(function (stream) {
                stream.getTracks().forEach(function (track) {
                    track.stop();
                    stream.removeTrack(track);
                    _this18.renderers.forEach(function (renderer) {
                        if (renderer.id === track.id) {
                            _this18._call.dispatchEvent({
                                name: CallEvents_1.CallEvents.MediaElementRemoved,
                                call: _this18._call,
                                elementId: renderer.id,
                                type: 'video'
                            });
                            Renderer_1.Renderer.get().releaseElement(renderer);
                        }
                    });
                });
            });
            this.shareScreenMediaTracks = [];
            this.shareScreenMedia = [];
        }
        /**
         * @hidden
         * @return {string}
         * @private
         */

    }, {
        key: "_traceName",
        value: function _traceName() {
            return 'WebRTCPC';
        }
    }, {
        key: "hasLocalAudio",
        value: function hasLocalAudio() {
            return this.impl.getLocalStreams().some(function (stream) {
                if (stream.getAudioTracks().length) return true;else return false;
            });
        }
    }, {
        key: "hasLocalVideo",
        value: function hasLocalVideo() {
            var _this19 = this;

            return this.impl.getLocalStreams().some(function (stream) {
                return stream.getVideoTracks().some(function (track) {
                    if (!_this19.shareScreenMedia || !_this19.shareScreenMedia.some(function (sStream) {
                        return sStream.getTracks().some(function (sTrack) {
                            return sTrack.id === track.id;
                        });
                    })) {
                        return true;
                    } else {
                        return false;
                    }
                });
            });
        }
    }, {
        key: "enableVideo",
        value: function enableVideo(flag) {
            var _this20 = this;

            this.impl.getLocalStreams().forEach(function (stream) {
                stream.getVideoTracks().forEach(function (track) {
                    if (!_this20.shareScreenMedia || !_this20.shareScreenMedia.some(function (sStream) {
                        return sStream.getTracks().some(function (sTrack) {
                            return sTrack.id === track.id;
                        });
                    })) {
                        track.enabled = flag;
                    }
                });
            });
        }
    }]);

    return WebRTCPC;
}(PeerConnection_1.PeerConnection);

__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "onSignalingStateChange", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "getPCStats", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "onConnectionChange", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "onRenegotiation", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "getReceiveOptions", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "updateHoldState", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "onICECandidate", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "onAddTrack", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "onAddStream", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "mapRenderer", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "checkStreamActive", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "renderStream", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "checkMediaAttached", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "wireRemoteStream", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "_processRemoteAnswer", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "_getLocalOffer", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "_getLocalAnswer", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "_setRemoteDescription", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "_processRemoteOffer", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "_close", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "_addRemoteCandidate", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "_bindLocalMedia", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "_unbindLocalMegia", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "_handleReinvite", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "codecRearrange", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "_sendDTMF", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "_hold", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "_getStreamActivity", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "getMediaActivity", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "_hdnFRSPrep", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "_hdnFRS", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "_getTrackKind", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "_stopSharing", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "hasLocalAudio", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "hasLocalVideo", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], WebRTCPC.prototype, "enableVideo", null);
exports.WebRTCPC = WebRTCPC;

/***/ }),
/* 41 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var __decorate = undefined && undefined.__decorate || function (decorators, target, key, desc) {
    var c = arguments.length,
        r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc,
        d;
    if ((typeof Reflect === "undefined" ? "undefined" : _typeof(Reflect)) === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);else for (var i = decorators.length - 1; i >= 0; i--) {
        if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    }return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var Logger_1 = __webpack_require__(0);
/**
 * @hidden
 */

var CodecSorter = function () {
    function CodecSorter(sdp) {
        _classCallCheck(this, CodecSorter);

        this.originalSDP = sdp;
    }

    _createClass(CodecSorter, [{
        key: "getCodecList",

        /**
         * Parcing source sdp to inner codec list
         *
         * @autor Igor Sheko
         * @returns {CodecSorterCodecList}
         */
        value: function getCodecList() {
            this.originalCodecList = {
                prefix: '',
                sections: [],
                sufix: ''
            };
            var validLine = RegExp.prototype.test.bind(/^([a-z])=(.*)/);
            var sections = CodecSorter.splitSections(this.originalSDP);
            this.originalCodecList.prefix = sections[0];
            for (var i = 1; i < sections.length; i++) {
                var mediaCodec = {
                    kind: 'audio',
                    firstLine: '',
                    prefix: '',
                    sufix: '',
                    codec: []
                };
                var preparced = sections[i].split('\na=rtpmap');
                preparced = preparced.map(function (part, index) {
                    return (index > 0 ? 'a=rtpmap' + part : part).trim() + '\r\n';
                });
                mediaCodec.prefix = preparced.shift();
                var tempsufix = preparced.pop();
                tempsufix = tempsufix.split(/(\r\n|\r|\n)/).filter(validLine);
                var needparse = true;
                preparced.push('');
                while (needparse) {
                    needparse = false;
                    if (tempsufix.length !== 0) {
                        var el = tempsufix.shift();
                        if (el.indexOf('a=rtpmap') === 0 || el.indexOf('a=rtcp-fb') === 0 || el.indexOf('a=fmtp') === 0 || el.indexOf('a=x-caps') === 0 || el.indexOf('a=maxptime') === 0) {
                            preparced[preparced.length - 1] += el + '\r\n';
                            needparse = true;
                        } else tempsufix.unshift(el);
                    }
                }
                for (var j = 0; j < preparced.length; j++) {
                    mediaCodec.codec.push(preparced[j].split(/(\r\n|\r|\n)/).filter(validLine));
                }
                var parsedPrefix = mediaCodec.prefix.split(/(\r\n|\r|\n)/).filter(validLine);
                mediaCodec.firstLine = parsedPrefix.shift();
                var firstLineSplited = mediaCodec.firstLine.split(' ');
                firstLineSplited.splice(-1 * mediaCodec.codec.length, mediaCodec.codec.length);
                mediaCodec.kind = firstLineSplited[0].substring(2);
                mediaCodec.prefix = parsedPrefix.join('\r\n') + '\r\n';
                mediaCodec.firstLine = firstLineSplited.join(' ');
                if (tempsufix.length > 0) mediaCodec.sufix = tempsufix.join('\r\n') + '\r\n';
                this.originalCodecList.sections.push(mediaCodec);
            }
            return this.originalCodecList;
        }
        /**
         * Return user readable list of sections with list of codec inside
         *
         * @autor Igor Sheko
         * @returns {CodecSorterUserCodecList}
         */

    }, {
        key: "getUserCodecList",
        value: function getUserCodecList() {
            if (typeof this.originalCodecList === "undefined") this.getCodecList();
            var userChL = {
                sections: []
            };
            userChL.sections = this.originalCodecList.sections.filter(function (value) {
                return value.kind === "video" || value.kind === "audio";
            }).map(function (currentValue, index, array) {
                var list = {
                    kind: currentValue.kind,
                    codec: currentValue.codec.map(function (item) {
                        return CodecSorter.codecToUserCodec(item);
                    })
                };
                var resultArr = [];
                var _iteratorNormalCompletion = true;
                var _didIteratorError = false;
                var _iteratorError = undefined;

                try {
                    for (var _iterator = list.codec[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                        var item = _step.value;

                        if (resultArr.indexOf(item) === -1) resultArr.push(item);
                    }
                } catch (err) {
                    _didIteratorError = true;
                    _iteratorError = err;
                } finally {
                    try {
                        if (!_iteratorNormalCompletion && _iterator.return) {
                            _iterator.return();
                        }
                    } finally {
                        if (_didIteratorError) {
                            throw _iteratorError;
                        }
                    }
                }

                list.codec = resultArr;
                return list;
            });
            return userChL;
        }
    }, {
        key: "setUserCodecList",
        value: function setUserCodecList(userCL) {
            if (typeof this.originalCodecList === "undefined") this.getCodecList();
            for (var i = 0; i < userCL.sections.length; i++) {
                if (userCL.sections[i].kind === this.originalCodecList.sections[i].kind) {
                    this.originalCodecList.sections[i].codec = CodecSorter.resortSection(userCL.sections[i].codec, this.originalCodecList.sections[i].codec);
                }
            }
        }
    }, {
        key: "getSDP",
        value: function getSDP() {
            var resultSDP = this.originalCodecList.prefix;
            for (var i = 0; i < this.originalCodecList.sections.length; i++) {
                var codecPart = '';
                var codecOrder = [];
                for (var j = 0; j < this.originalCodecList.sections[i].codec.length; j++) {
                    codecOrder.push(this.originalCodecList.sections[i].codec[j][0].split(" ")[0].substring(9));
                    codecPart += this.originalCodecList.sections[i].codec[j].join('\r\n') + '\r\n';
                }
                resultSDP += this.originalCodecList.sections[i].firstLine + " " + codecOrder.join(" ") + '\r\n';
                resultSDP += this.originalCodecList.sections[i].prefix;
                resultSDP += codecPart;
                resultSDP += this.originalCodecList.sections[i].sufix;
            }
            return resultSDP;
        }
    }, {
        key: "_traceName",

        /**
         * @hidden
         * @return {string}
         * @private
         */
        value: function _traceName() {
            return 'CodecSorter';
        }
    }], [{
        key: "splitSections",
        value: function splitSections(blob) {
            var parts = blob.split('\nm=');
            return parts.map(function (part, index) {
                return (index > 0 ? 'm=' + part : part).trim() + '\r\n';
            });
        }
    }, {
        key: "codecToUserCodec",
        value: function codecToUserCodec(item) {
            var splited = item[0].split(' ');
            splited.shift();
            return splited.join(' ');
        }
    }, {
        key: "resortSection",
        value: function resortSection(userCodec, originalCodec) {
            var newCodecs = [];
            for (var i = 0; i < userCodec.length; i++) {
                for (var j = 0; j < originalCodec.length; j++) {
                    if (userCodec[i] === CodecSorter.codecToUserCodec(originalCodec[j])) {
                        newCodecs.push(originalCodec[j]);
                    }
                }
            }
            return newCodecs;
        }
    }, {
        key: "downOpusBandwidth",
        value: function downOpusBandwidth(sdp) {
            return new Promise(function (resolve, reject) {
                var validLine = RegExp.prototype.test.bind(/^([a-z])=(.*)/);
                var sdpLines = sdp.sdp.split(/(\r\n|\r|\n)/).filter(validLine);
                var changed = false;
                for (var i = 0; i < sdpLines.length; i++) {
                    if (sdpLines[i].indexOf('a=fmtp:114') !== -1) {
                        sdpLines[i] = "a=fmtp:114 minptime=10; useinbandfec=1; sprop-maxcapturerate=8000";
                        changed = true;
                    }
                    if (sdpLines[i].indexOf('a=fmtp:111') !== -1) {
                        sdpLines[i] = "a=fmtp:111 minptime=10; useinbandfec=1; sprop-maxcapturerate=8000";
                        changed = true;
                    }
                }
                if (!changed) {
                    reject(sdp);
                }
                sdp.sdp = sdpLines.join('\r\n') + '\r\n';
                resolve(sdp);
            });
        }
    }]);

    return CodecSorter;
}();

__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], CodecSorter.prototype, "getCodecList", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], CodecSorter.prototype, "getUserCodecList", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], CodecSorter.prototype, "setUserCodecList", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], CodecSorter.prototype, "getSDP", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], CodecSorter, "splitSections", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], CodecSorter, "codecToUserCodec", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], CodecSorter, "resortSection", null);
__decorate([Logger_1.LogManager.d_trace(Logger_1.LogCategory.RTC)], CodecSorter, "downOpusBandwidth", null);
exports.CodecSorter = CodecSorter;

/***/ }),
/* 42 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

Object.defineProperty(exports, "__esModule", { value: true });
/**
 * Edge specific implementation
 * @hidden
 */

var Edge = function () {
    function Edge() {
        _classCallCheck(this, Edge);
    }

    _createClass(Edge, [{
        key: "_traceName",

        /**
         * @hidden
         * @return {string}
         * @private
         */
        value: function _traceName() {
            return 'Edge';
        }
    }], [{
        key: "attachStream",
        value: function attachStream(stream, element) {
            element.srcObject = stream;
            element.play();
        }
    }, {
        key: "detachStream",
        value: function detachStream(element) {
            element.pause();
            element.src = "";
        }
    }, {
        key: "getScreenMedia",
        value: function getScreenMedia() {
            return new Promise(function (resolve, reject) {
                reject(new Error('Screen sharing not allowed for you platform'));
            });
        }
    }, {
        key: "getRTCStats",
        value: function getRTCStats(pc) {
            return new Promise(function (resolve, reject) {
                reject(new Error('RTCStats sharing not allowed for you platform'));
            });
        }
    }]);

    return Edge;
}();

exports.Edge = Edge;

/***/ }),
/* 43 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

Object.defineProperty(exports, "__esModule", { value: true });
var SignalingDTMFSender_1 = __webpack_require__(17);
/**
 * @hidden
 */

var Safari = function () {
    function Safari() {
        _classCallCheck(this, Safari);
    }

    _createClass(Safari, [{
        key: "_traceName",

        /**
         * @hidden
         * @return {string}
         * @private
         */
        value: function _traceName() {
            return 'Safari';
        }
    }], [{
        key: "attachStream",
        value: function attachStream(stream, element) {
            element.srcObject = stream;
        }
    }, {
        key: "detachStream",
        value: function detachStream(element) {
            if (element instanceof HTMLVideoElement) {
                var promice = element.pause();
                if (typeof promice != "undefined") promice.catch(function (e) {});
            } else element.pause();
            element.src = "";
        }
    }, {
        key: "getDTMFSender",
        value: function getDTMFSender(pc, callId) {
            if (!!pc.createDTMFSender) return pc.createDTMFSender(pc.getLocalStreams()[0].getAudioTracks()[0]);else return new SignalingDTMFSender_1.SignalingDTMFSender(callId);
        }
    }, {
        key: "getScreenMedia",
        value: function getScreenMedia() {
            return new Promise(function (resolve, reject) {
                window.postMessage('get-sourceId', '*');
                window.addEventListener('message', function (event) {
                    if (event.origin == window.location.origin) {
                        if (event.data == 'PermissionDeniedError') {
                            reject(new Error('PermissionDeniedError'));
                        }
                        if (typeof event.data != 'string' && typeof event.data.sourceId != "undefined") {
                            var mediaParams = {
                                audio: false,
                                video: {
                                    mandatory: {
                                        chromeMediaSource: 'desktop',
                                        maxWidth: screen.width > 1920 ? screen.width : 1920,
                                        maxHeight: screen.height > 1080 ? screen.height : 1080,
                                        chromeMediaSourceId: event.data.sourceId
                                        // minAspectRatio: 1.77
                                    },
                                    optional: [{
                                        googTemporalLayeredScreencast: true
                                    }]
                                }
                            };
                            navigator.mediaDevices.getUserMedia(mediaParams).then(function (stream) {
                                resolve(stream);
                            }).catch(function (e) {
                                reject(e);
                            });
                        }
                    }
                });
            });
        }
    }, {
        key: "getRTCStats",
        value: function getRTCStats(pc) {
            return new Promise(function (resolve, reject) {
                var resultArray = [];
                pc.getStats(null).then(function (e) {
                    e.forEach(function (result) {
                        if (result.type == "ssrc") {
                            var item = {};
                            item.id = result.id;
                            item.type = result.type;
                            item.timestamp = result.timestamp;
                            resultArray.push(item);
                        }
                    });
                    resolve(resultArray);
                }).catch(reject);
            });
        }
    }]);

    return Safari;
}();

exports.Safari = Safari;

/***/ }),
/* 44 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

Object.defineProperty(exports, "__esModule", { value: true });
var Events_1 = __webpack_require__(18);
var CallManager_1 = __webpack_require__(6);
var VoxSignaling_1 = __webpack_require__(1);
var RemoteFunction_1 = __webpack_require__(2);
var UserMediaManager_1 = __webpack_require__(4);
var RemoteEvent_1 = __webpack_require__(12);
var Authenticator_1 = __webpack_require__(10);
var CallEvents_1 = __webpack_require__(9);
/**
 * @hidden
 */

var ZingayaAPI = function () {
  /**
   * @hidden
   * @param client
   */
  /**
   * @hidden
   * @param client
   */
  function ZingayaAPI(client) {
    var _this = this;

    _classCallCheck(this, ZingayaAPI);

    this.client = client;
    /**
     * @hidden
     */
    this.currentCall = null;
    /**
     * @hidden
     */
    this.onConnectionFailed = null;
    /**
     * @hidden
     */
    this.onConnectionEstablished = null;
    /**
     * @hidden
     */
    this.onCheckComplete = null;
    /**
     * @hidden
     */
    this.onCallFailed = null;
    /**
     * @hidden
     */
    this.onCallConnected = null;
    /**
     * @hidden
     */
    this.onCallEnded = null;
    /**
     * @hidden
     */
    this.onCallRinging = null;
    /**
     * @hidden
     */
    this.onCallMediaStarted = null;
    /**
     * @hidden
     */
    this.onVoicemail = null;
    /**
     * @hidden
     */
    this.onNetStatsReceived = null;
    //console.log(`[ZA] constructor`);
    CallManager_1.CallManager.get().protocolVersion == "2";
    client.on(Events_1.Events.ConnectionFailed, function (event) {
      return _this.runLegacyCallback(_this.onConnectionFailed, event);
    });
    client.on(Events_1.Events.ConnectionEstablished, function (event) {
      return _this.runLegacyCallback(_this.onConnectionEstablished, event);
    });
    VoxSignaling_1.VoxSignaling.get().setRPCHandler(RemoteEvent_1.RemoteEvent.handlePreFlightCheckResult, function (a, b, c) {
      return _this.onCheckComplete(a, b, c);
    });
    VoxSignaling_1.VoxSignaling.get().setRPCHandler(RemoteEvent_1.RemoteEvent.handleVoicemail, function (event) {
      return _this.runLegacyCallback(_this.onVoicemail, event);
    });
  }
  /**
   * @hidden
   * @param serverAddress
   * @param referrer
   * @param extra
   * @param appName
   */


  _createClass(ZingayaAPI, [{
    key: "connectTo",
    value: function connectTo(serverAddress, referrer, extra, appName) {
      //console.log(`[ZA] connectTo(${serverAddress},${referrer},${extra},${appName}`);
      var signaling = VoxSignaling_1.VoxSignaling.get();
      Authenticator_1.Authenticator.get().ziAuthorized(true);
      signaling.lagacyConnectTo(serverAddress, referrer, extra, appName);
    }
    /**
     * @hidden
     */

  }, {
    key: "connect",
    value: function connect() {
      //console.log(`[ZA] connect`);
    }
  }, {
    key: "requestMedia",

    /**
     * @hidden
     * @param video
     * @param onMediaAccessGranted
     * @param onMediaAccessRejected
     * @param stopStream
     */
    value: function requestMedia(video, onMediaAccessGranted, onMediaAccessRejected, stopStream) {
      //console.log(`[ZA] requestMedia`);
      var mediaManager = UserMediaManager_1.UserMediaManager.get();
      mediaManager.enableAudio(true);
      mediaManager.queryMedia().then(function (stream) {
        UserMediaManager_1.UserMediaManager.get().updateLocalVideo(stream);
        if (typeof onMediaAccessGranted == "function") onMediaAccessGranted(stream);
      }).catch(function (err) {
        if (typeof onMediaAccessRejected == "function") onMediaAccessRejected(err);
      });
    }
  }, {
    key: "hangupCall",

    /**
     * @hidden
     * @param callId
     * @param headers
     */
    value: function hangupCall(callId, headers) {
      //console.log(`[ZA] hangupCall(${callId},${JSON.stringify(headers)})`);
      CallManager_1.CallManager.get().calls[callId].hangup(headers);
      VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.disconnectCall, callId, {});
    }
  }, {
    key: "callTo",

    /**
     * @hidden
     * @param destination
     * @param useVideo
     * @param headers
     * @param extraParams
     */
    value: function callTo(destination, useVideo, headers, extraParams) {
      //console.log(`[ZA] callTo(${destination},${useVideo},${JSON.stringify(headers)},${JSON.stringify(extraParams)})`);
      this.currentCall = this.client.call({
        number: destination,
        video: useVideo,
        extraHeaders: headers,
        extraParams: extraParams
      });
      this.bindCurrentCall();
      return this.currentCall.id();
    }
  }, {
    key: "voicemailPromptFinished",

    /**
     * @hidden
     * @param callId
     */
    value: function voicemailPromptFinished(callId) {
      //console.log(`[ZA] voicemailPromptFinished(${callId})`);
      VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.zPromptFinished, callId);
    }
  }, {
    key: "makeid",

    /**
     * @hidden
     * @param len
     */
    value: function makeid(len) {
      //console.log(`[ZA] makeid(${len})`);
      var text = "";
      var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
      for (var i = 0; i < len; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
      }return text;
    }
  }, {
    key: "muteMicrophone",

    /**
     * @hidden
     * @param doMute
     */
    value: function muteMicrophone(doMute) {
      //console.log(`[ZA] muteMicrophone(${doMute})`);
      var cm = CallManager_1.CallManager.get();
      for (var call in cm.calls) {
        if (cm.calls.hasOwnProperty(call)) {
          if (doMute) cm.calls[call].muteMicrophone();else cm.calls[call].unmuteMicrophone();
        }
      }
    }
  }, {
    key: "sendDigit",

    /**
     * @hidden
     * @param callId
     * @param digit
     */
    value: function sendDigit(callId, digit) {
      //console.log(`[ZA] sendDigit(${callId},${digit})`);
      VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.sendDTMF, callId, digit);
    }
  }, {
    key: "startPreFlightCheck",

    /**
     * @hidden
     * @param mic
     * @param net
     */
    value: function startPreFlightCheck(mic, net) {
      //console.log(`[ZA] startPreFlightCheck(${mic},${net})`);
      VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.zStartPreFlightCheck, !!mic, !!net);
    }
  }, {
    key: "runLegacyCallback",

    /**
     * @hidden
     * @param callback
     * @param event
     */
    value: function runLegacyCallback(callback, event) {
      //console.log(`[ZA] runLegacyCallback(${event.name})`);
      if (typeof callback !== "undefined" && callback !== null) {
        callback(event);
      }
    }
    /**
     * @hidden
     */

  }, {
    key: "bindCurrentCall",
    value: function bindCurrentCall() {
      var _this2 = this;

      window['currentCall'] = this.currentCall;
      this.currentCall.on(CallEvents_1.CallEvents.Failed, function (event) {
        _this2.runLegacyCallback(_this2.onCallFailed, event);
        _this2.unbindCurrentCall();
      });
      this.currentCall.on(CallEvents_1.CallEvents.Connected, function (event) {
        _this2.runLegacyCallback(_this2.onCallConnected, event);
        _this2.runLegacyCallback(_this2.onCallMediaStarted, event);
        var cm = CallManager_1.CallManager.get();
        setTimeout(function () {
          var renderer = document.getElementById(window['currentCall'].peerConnection.impl.getRemoteStreams()[0].getTracks()[0].id);
          renderer.srcObject = window['currentCall'].peerConnection.impl.getRemoteStreams()[0];
          renderer.load();
          renderer.play();
        }, 1000);
      });
      this.currentCall.on(CallEvents_1.CallEvents.Disconnected, function (event) {
        _this2.runLegacyCallback(_this2.onCallEnded, event);
        _this2.unbindCurrentCall();
      });
      this.client.on(Events_1.Events.NetStatsReceived, function (event) {
        return _this2.onNetStatsReceived(event);
      });
    }
    /**
     * @hidden
     */

  }, {
    key: "unbindCurrentCall",
    value: function unbindCurrentCall() {
      this.currentCall.off(CallEvents_1.CallEvents.Failed);
      this.currentCall.off(CallEvents_1.CallEvents.Connected);
      this.currentCall.off(CallEvents_1.CallEvents.Disconnected);
      this.currentCall.off(CallEvents_1.CallEvents.ProgressToneStart);
      this.currentCall.off(CallEvents_1.CallEvents.Connected);
      this.client.off(Events_1.Events.NetStatsReceived);
    }
    /**
     * @hidden
     * @return {string}
     * @private
     */

  }, {
    key: "_traceName",
    value: function _traceName() {
      return 'ZingayaAPI';
    }
  }]);

  return ZingayaAPI;
}();

exports.ZingayaAPI = ZingayaAPI;

/***/ }),
/* 45 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

Object.defineProperty(exports, "__esModule", { value: true });
var VoxSignaling_1 = __webpack_require__(1);
var RemoteFunction_1 = __webpack_require__(2);
/**
 * @hidden
 */

var PushService = function () {
    function PushService() {
        _classCallCheck(this, PushService);
    }

    _createClass(PushService, [{
        key: "_traceName",

        /**
         * @hidden
         * @return {string}
         * @private
         */
        value: function _traceName() {
            return 'PushService';
        }
    }], [{
        key: "register",
        value: function register(token) {
            return new Promise(function (resolve, reject) {
                var sendResult = VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.registerPushToken, token);
                if (sendResult) resolve();else reject();
            });
        }
    }, {
        key: "unregister",
        value: function unregister(token) {
            return new Promise(function (resolve, reject) {
                var sendResult = VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.unregisterPushToken, token);
                if (sendResult) resolve();else reject();
            });
        }
    }, {
        key: "incomingPush",
        value: function incomingPush(data) {
            return new Promise(function (resolve, reject) {
                var sendResult = VoxSignaling_1.VoxSignaling.get().callRemoteFunction(RemoteFunction_1.RemoteFunction.pushFeedback, data);
                if (sendResult) resolve();else reject();
            });
        }
    }]);

    return PushService;
}();

exports.PushService = PushService;

/***/ }),
/* 46 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(global) {/*
 *  Copyright (c) 2016 The WebRTC project authors. All Rights Reserved.
 *
 *  Use of this source code is governed by a BSD-style license
 *  that can be found in the LICENSE file in the root of the source
 *  tree.
 */
 /* eslint-env node */



var adapterFactory = __webpack_require__(48);
module.exports = adapterFactory({window: global.window});

/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(47)))

/***/ }),
/* 47 */
/***/ (function(module, exports) {

var g;

// This works in non-strict mode
g = (function() {
	return this;
})();

try {
	// This works if eval is allowed (see CSP)
	g = g || Function("return this")() || (1,eval)("this");
} catch(e) {
	// This works if the window reference is available
	if(typeof window === "object")
		g = window;
}

// g can still be undefined, but nothing to do about it...
// We return undefined, instead of nothing here, so it's
// easier to handle this case. if(!global) { ...}

module.exports = g;


/***/ }),
/* 48 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/*
 *  Copyright (c) 2016 The WebRTC project authors. All Rights Reserved.
 *
 *  Use of this source code is governed by a BSD-style license
 *  that can be found in the LICENSE file in the root of the source
 *  tree.
 */
 /* eslint-env node */



var utils = __webpack_require__(7);
// Shimming starts here.
module.exports = function(dependencies, opts) {
  var window = dependencies && dependencies.window;

  var options = {
    shimChrome: true,
    shimFirefox: true,
    shimEdge: true,
    shimSafari: true,
  };

  for (var key in opts) {
    if (hasOwnProperty.call(opts, key)) {
      options[key] = opts[key];
    }
  }

  // Utils.
  var logging = utils.log;
  var browserDetails = utils.detectBrowser(window);

  // Export to the adapter global object visible in the browser.
  var adapter = {
    browserDetails: browserDetails,
    extractVersion: utils.extractVersion,
    disableLog: utils.disableLog,
    disableWarnings: utils.disableWarnings
  };

  // Uncomment the line below if you want logging to occur, including logging
  // for the switch statement below. Can also be turned on in the browser via
  // adapter.disableLog(false), but then logging from the switch statement below
  // will not appear.
  // require('./utils').disableLog(false);

  // Browser shims.
  var chromeShim = __webpack_require__(49) || null;
  var edgeShim = __webpack_require__(51) || null;
  var firefoxShim = __webpack_require__(54) || null;
  var safariShim = __webpack_require__(56) || null;
  var commonShim = __webpack_require__(57) || null;

  // Shim browser if found.
  switch (browserDetails.browser) {
    case 'chrome':
      if (!chromeShim || !chromeShim.shimPeerConnection ||
          !options.shimChrome) {
        logging('Chrome shim is not included in this adapter release.');
        return adapter;
      }
      logging('adapter.js shimming chrome.');
      // Export to the adapter global object visible in the browser.
      adapter.browserShim = chromeShim;
      commonShim.shimCreateObjectURL(window);

      chromeShim.shimGetUserMedia(window);
      chromeShim.shimMediaStream(window);
      chromeShim.shimSourceObject(window);
      chromeShim.shimPeerConnection(window);
      chromeShim.shimOnTrack(window);
      chromeShim.shimAddTrackRemoveTrack(window);
      chromeShim.shimGetSendersWithDtmf(window);

      commonShim.shimRTCIceCandidate(window);
      break;
    case 'firefox':
      if (!firefoxShim || !firefoxShim.shimPeerConnection ||
          !options.shimFirefox) {
        logging('Firefox shim is not included in this adapter release.');
        return adapter;
      }
      logging('adapter.js shimming firefox.');
      // Export to the adapter global object visible in the browser.
      adapter.browserShim = firefoxShim;
      commonShim.shimCreateObjectURL(window);

      firefoxShim.shimGetUserMedia(window);
      firefoxShim.shimSourceObject(window);
      firefoxShim.shimPeerConnection(window);
      firefoxShim.shimOnTrack(window);
      firefoxShim.shimRemoveStream(window);

      commonShim.shimRTCIceCandidate(window);
      break;
    case 'edge':
      if (!edgeShim || !edgeShim.shimPeerConnection || !options.shimEdge) {
        logging('MS edge shim is not included in this adapter release.');
        return adapter;
      }
      logging('adapter.js shimming edge.');
      // Export to the adapter global object visible in the browser.
      adapter.browserShim = edgeShim;
      commonShim.shimCreateObjectURL(window);

      edgeShim.shimGetUserMedia(window);
      edgeShim.shimPeerConnection(window);
      edgeShim.shimReplaceTrack(window);

      // the edge shim implements the full RTCIceCandidate object.
      break;
    case 'safari':
      if (!safariShim || !options.shimSafari) {
        logging('Safari shim is not included in this adapter release.');
        return adapter;
      }
      logging('adapter.js shimming safari.');
      // Export to the adapter global object visible in the browser.
      adapter.browserShim = safariShim;
      commonShim.shimCreateObjectURL(window);

      safariShim.shimRTCIceServerUrls(window);
      safariShim.shimCallbacksAPI(window);
      safariShim.shimLocalStreamsAPI(window);
      safariShim.shimRemoteStreamsAPI(window);
      safariShim.shimTrackEventTransceiver(window);
      safariShim.shimGetUserMedia(window);
      safariShim.shimCreateOfferLegacy(window);

      commonShim.shimRTCIceCandidate(window);
      break;
    default:
      logging('Unsupported browser!');
      break;
  }

  return adapter;
};


/***/ }),
/* 49 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/*
 *  Copyright (c) 2016 The WebRTC project authors. All Rights Reserved.
 *
 *  Use of this source code is governed by a BSD-style license
 *  that can be found in the LICENSE file in the root of the source
 *  tree.
 */
 /* eslint-env node */

var utils = __webpack_require__(7);
var logging = utils.log;

var chromeShim = {
  shimMediaStream: function(window) {
    window.MediaStream = window.MediaStream || window.webkitMediaStream;
  },

  shimOnTrack: function(window) {
    if (typeof window === 'object' && window.RTCPeerConnection && !('ontrack' in
        window.RTCPeerConnection.prototype)) {
      Object.defineProperty(window.RTCPeerConnection.prototype, 'ontrack', {
        get: function() {
          return this._ontrack;
        },
        set: function(f) {
          if (this._ontrack) {
            this.removeEventListener('track', this._ontrack);
          }
          this.addEventListener('track', this._ontrack = f);
        }
      });
      var origSetRemoteDescription =
          window.RTCPeerConnection.prototype.setRemoteDescription;
      window.RTCPeerConnection.prototype.setRemoteDescription = function() {
        var pc = this;
        if (!pc._ontrackpoly) {
          pc._ontrackpoly = function(e) {
            // onaddstream does not fire when a track is added to an existing
            // stream. But stream.onaddtrack is implemented so we use that.
            e.stream.addEventListener('addtrack', function(te) {
              var receiver;
              if (window.RTCPeerConnection.prototype.getReceivers) {
                receiver = pc.getReceivers().find(function(r) {
                  return r.track && r.track.id === te.track.id;
                });
              } else {
                receiver = {track: te.track};
              }

              var event = new Event('track');
              event.track = te.track;
              event.receiver = receiver;
              event.transceiver = {receiver: receiver};
              event.streams = [e.stream];
              pc.dispatchEvent(event);
            });
            e.stream.getTracks().forEach(function(track) {
              var receiver;
              if (window.RTCPeerConnection.prototype.getReceivers) {
                receiver = pc.getReceivers().find(function(r) {
                  return r.track && r.track.id === track.id;
                });
              } else {
                receiver = {track: track};
              }
              var event = new Event('track');
              event.track = track;
              event.receiver = receiver;
              event.transceiver = {receiver: receiver};
              event.streams = [e.stream];
              pc.dispatchEvent(event);
            });
          };
          pc.addEventListener('addstream', pc._ontrackpoly);
        }
        return origSetRemoteDescription.apply(pc, arguments);
      };
    }
  },

  shimGetSendersWithDtmf: function(window) {
    // Overrides addTrack/removeTrack, depends on shimAddTrackRemoveTrack.
    if (typeof window === 'object' && window.RTCPeerConnection &&
        !('getSenders' in window.RTCPeerConnection.prototype) &&
        'createDTMFSender' in window.RTCPeerConnection.prototype) {
      var shimSenderWithDtmf = function(pc, track) {
        return {
          track: track,
          get dtmf() {
            if (this._dtmf === undefined) {
              if (track.kind === 'audio') {
                this._dtmf = pc.createDTMFSender(track);
              } else {
                this._dtmf = null;
              }
            }
            return this._dtmf;
          },
          _pc: pc
        };
      };

      // augment addTrack when getSenders is not available.
      if (!window.RTCPeerConnection.prototype.getSenders) {
        window.RTCPeerConnection.prototype.getSenders = function() {
          this._senders = this._senders || [];
          return this._senders.slice(); // return a copy of the internal state.
        };
        var origAddTrack = window.RTCPeerConnection.prototype.addTrack;
        window.RTCPeerConnection.prototype.addTrack = function(track, stream) {
          var pc = this;
          var sender = origAddTrack.apply(pc, arguments);
          if (!sender) {
            sender = shimSenderWithDtmf(pc, track);
            pc._senders.push(sender);
          }
          return sender;
        };

        var origRemoveTrack = window.RTCPeerConnection.prototype.removeTrack;
        window.RTCPeerConnection.prototype.removeTrack = function(sender) {
          var pc = this;
          origRemoveTrack.apply(pc, arguments);
          var idx = pc._senders.indexOf(sender);
          if (idx !== -1) {
            pc._senders.splice(idx, 1);
          }
        };
      }
      var origAddStream = window.RTCPeerConnection.prototype.addStream;
      window.RTCPeerConnection.prototype.addStream = function(stream) {
        var pc = this;
        pc._senders = pc._senders || [];
        origAddStream.apply(pc, [stream]);
        stream.getTracks().forEach(function(track) {
          pc._senders.push(shimSenderWithDtmf(pc, track));
        });
      };

      var origRemoveStream = window.RTCPeerConnection.prototype.removeStream;
      window.RTCPeerConnection.prototype.removeStream = function(stream) {
        var pc = this;
        pc._senders = pc._senders || [];
        origRemoveStream.apply(pc, [stream]);

        stream.getTracks().forEach(function(track) {
          var sender = pc._senders.find(function(s) {
            return s.track === track;
          });
          if (sender) {
            pc._senders.splice(pc._senders.indexOf(sender), 1); // remove sender
          }
        });
      };
    } else if (typeof window === 'object' && window.RTCPeerConnection &&
               'getSenders' in window.RTCPeerConnection.prototype &&
               'createDTMFSender' in window.RTCPeerConnection.prototype &&
               window.RTCRtpSender &&
               !('dtmf' in window.RTCRtpSender.prototype)) {
      var origGetSenders = window.RTCPeerConnection.prototype.getSenders;
      window.RTCPeerConnection.prototype.getSenders = function() {
        var pc = this;
        var senders = origGetSenders.apply(pc, []);
        senders.forEach(function(sender) {
          sender._pc = pc;
        });
        return senders;
      };

      Object.defineProperty(window.RTCRtpSender.prototype, 'dtmf', {
        get: function() {
          if (this._dtmf === undefined) {
            if (this.track.kind === 'audio') {
              this._dtmf = this._pc.createDTMFSender(this.track);
            } else {
              this._dtmf = null;
            }
          }
          return this._dtmf;
        }
      });
    }
  },

  shimSourceObject: function(window) {
    var URL = window && window.URL;

    if (typeof window === 'object') {
      if (window.HTMLMediaElement &&
        !('srcObject' in window.HTMLMediaElement.prototype)) {
        // Shim the srcObject property, once, when HTMLMediaElement is found.
        Object.defineProperty(window.HTMLMediaElement.prototype, 'srcObject', {
          get: function() {
            return this._srcObject;
          },
          set: function(stream) {
            var self = this;
            // Use _srcObject as a private property for this shim
            this._srcObject = stream;
            if (this.src) {
              URL.revokeObjectURL(this.src);
            }

            if (!stream) {
              this.src = '';
              return undefined;
            }
            this.src = URL.createObjectURL(stream);
            // We need to recreate the blob url when a track is added or
            // removed. Doing it manually since we want to avoid a recursion.
            stream.addEventListener('addtrack', function() {
              if (self.src) {
                URL.revokeObjectURL(self.src);
              }
              self.src = URL.createObjectURL(stream);
            });
            stream.addEventListener('removetrack', function() {
              if (self.src) {
                URL.revokeObjectURL(self.src);
              }
              self.src = URL.createObjectURL(stream);
            });
          }
        });
      }
    }
  },

  shimAddTrackRemoveTrack: function(window) {
    var browserDetails = utils.detectBrowser(window);
    // shim addTrack and removeTrack.
    if (window.RTCPeerConnection.prototype.addTrack &&
        browserDetails.version >= 64) {
      return;
    }

    // also shim pc.getLocalStreams when addTrack is shimmed
    // to return the original streams.
    var origGetLocalStreams = window.RTCPeerConnection.prototype
        .getLocalStreams;
    window.RTCPeerConnection.prototype.getLocalStreams = function() {
      var self = this;
      var nativeStreams = origGetLocalStreams.apply(this);
      self._reverseStreams = self._reverseStreams || {};
      return nativeStreams.map(function(stream) {
        return self._reverseStreams[stream.id];
      });
    };

    var origAddStream = window.RTCPeerConnection.prototype.addStream;
    window.RTCPeerConnection.prototype.addStream = function(stream) {
      var pc = this;
      pc._streams = pc._streams || {};
      pc._reverseStreams = pc._reverseStreams || {};

      stream.getTracks().forEach(function(track) {
        var alreadyExists = pc.getSenders().find(function(s) {
          return s.track === track;
        });
        if (alreadyExists) {
          throw new DOMException('Track already exists.',
              'InvalidAccessError');
        }
      });
      // Add identity mapping for consistency with addTrack.
      // Unless this is being used with a stream from addTrack.
      if (!pc._reverseStreams[stream.id]) {
        var newStream = new window.MediaStream(stream.getTracks());
        pc._streams[stream.id] = newStream;
        pc._reverseStreams[newStream.id] = stream;
        stream = newStream;
      }
      origAddStream.apply(pc, [stream]);
    };

    var origRemoveStream = window.RTCPeerConnection.prototype.removeStream;
    window.RTCPeerConnection.prototype.removeStream = function(stream) {
      var pc = this;
      pc._streams = pc._streams || {};
      pc._reverseStreams = pc._reverseStreams || {};

      origRemoveStream.apply(pc, [(pc._streams[stream.id] || stream)]);
      delete pc._reverseStreams[(pc._streams[stream.id] ?
          pc._streams[stream.id].id : stream.id)];
      delete pc._streams[stream.id];
    };

    window.RTCPeerConnection.prototype.addTrack = function(track, stream) {
      var pc = this;
      if (pc.signalingState === 'closed') {
        throw new DOMException(
          'The RTCPeerConnection\'s signalingState is \'closed\'.',
          'InvalidStateError');
      }
      var streams = [].slice.call(arguments, 1);
      if (streams.length !== 1 ||
          !streams[0].getTracks().find(function(t) {
            return t === track;
          })) {
        // this is not fully correct but all we can manage without
        // [[associated MediaStreams]] internal slot.
        throw new DOMException(
          'The adapter.js addTrack polyfill only supports a single ' +
          ' stream which is associated with the specified track.',
          'NotSupportedError');
      }

      var alreadyExists = pc.getSenders().find(function(s) {
        return s.track === track;
      });
      if (alreadyExists) {
        throw new DOMException('Track already exists.',
            'InvalidAccessError');
      }

      pc._streams = pc._streams || {};
      pc._reverseStreams = pc._reverseStreams || {};
      var oldStream = pc._streams[stream.id];
      if (oldStream) {
        // this is using odd Chrome behaviour, use with caution:
        // https://bugs.chromium.org/p/webrtc/issues/detail?id=7815
        // Note: we rely on the high-level addTrack/dtmf shim to
        // create the sender with a dtmf sender.
        oldStream.addTrack(track);

        // Trigger ONN async.
        Promise.resolve().then(function() {
          pc.dispatchEvent(new Event('negotiationneeded'));
        });
      } else {
        var newStream = new window.MediaStream([track]);
        pc._streams[stream.id] = newStream;
        pc._reverseStreams[newStream.id] = stream;
        pc.addStream(newStream);
      }
      return pc.getSenders().find(function(s) {
        return s.track === track;
      });
    };

    // replace the internal stream id with the external one and
    // vice versa.
    function replaceInternalStreamId(pc, description) {
      var sdp = description.sdp;
      Object.keys(pc._reverseStreams || []).forEach(function(internalId) {
        var externalStream = pc._reverseStreams[internalId];
        var internalStream = pc._streams[externalStream.id];
        sdp = sdp.replace(new RegExp(internalStream.id, 'g'),
            externalStream.id);
      });
      return new RTCSessionDescription({
        type: description.type,
        sdp: sdp
      });
    }
    function replaceExternalStreamId(pc, description) {
      var sdp = description.sdp;
      Object.keys(pc._reverseStreams || []).forEach(function(internalId) {
        var externalStream = pc._reverseStreams[internalId];
        var internalStream = pc._streams[externalStream.id];
        sdp = sdp.replace(new RegExp(externalStream.id, 'g'),
            internalStream.id);
      });
      return new RTCSessionDescription({
        type: description.type,
        sdp: sdp
      });
    }
    ['createOffer', 'createAnswer'].forEach(function(method) {
      var nativeMethod = window.RTCPeerConnection.prototype[method];
      window.RTCPeerConnection.prototype[method] = function() {
        var pc = this;
        var args = arguments;
        var isLegacyCall = arguments.length &&
            typeof arguments[0] === 'function';
        if (isLegacyCall) {
          return nativeMethod.apply(pc, [
            function(description) {
              var desc = replaceInternalStreamId(pc, description);
              args[0].apply(null, [desc]);
            },
            function(err) {
              if (args[1]) {
                args[1].apply(null, err);
              }
            }, arguments[2]
          ]);
        }
        return nativeMethod.apply(pc, arguments)
        .then(function(description) {
          return replaceInternalStreamId(pc, description);
        });
      };
    });

    var origSetLocalDescription =
        window.RTCPeerConnection.prototype.setLocalDescription;
    window.RTCPeerConnection.prototype.setLocalDescription = function() {
      var pc = this;
      if (!arguments.length || !arguments[0].type) {
        return origSetLocalDescription.apply(pc, arguments);
      }
      arguments[0] = replaceExternalStreamId(pc, arguments[0]);
      return origSetLocalDescription.apply(pc, arguments);
    };

    // TODO: mangle getStats: https://w3c.github.io/webrtc-stats/#dom-rtcmediastreamstats-streamidentifier

    var origLocalDescription = Object.getOwnPropertyDescriptor(
        window.RTCPeerConnection.prototype, 'localDescription');
    Object.defineProperty(window.RTCPeerConnection.prototype,
        'localDescription', {
          get: function() {
            var pc = this;
            var description = origLocalDescription.get.apply(this);
            if (description.type === '') {
              return description;
            }
            return replaceInternalStreamId(pc, description);
          }
        });

    window.RTCPeerConnection.prototype.removeTrack = function(sender) {
      var pc = this;
      if (pc.signalingState === 'closed') {
        throw new DOMException(
          'The RTCPeerConnection\'s signalingState is \'closed\'.',
          'InvalidStateError');
      }
      // We can not yet check for sender instanceof RTCRtpSender
      // since we shim RTPSender. So we check if sender._pc is set.
      if (!sender._pc) {
        throw new DOMException('Argument 1 of RTCPeerConnection.removeTrack ' +
            'does not implement interface RTCRtpSender.', 'TypeError');
      }
      var isLocal = sender._pc === pc;
      if (!isLocal) {
        throw new DOMException('Sender was not created by this connection.',
            'InvalidAccessError');
      }

      // Search for the native stream the senders track belongs to.
      pc._streams = pc._streams || {};
      var stream;
      Object.keys(pc._streams).forEach(function(streamid) {
        var hasTrack = pc._streams[streamid].getTracks().find(function(track) {
          return sender.track === track;
        });
        if (hasTrack) {
          stream = pc._streams[streamid];
        }
      });

      if (stream) {
        if (stream.getTracks().length === 1) {
          // if this is the last track of the stream, remove the stream. This
          // takes care of any shimmed _senders.
          pc.removeStream(pc._reverseStreams[stream.id]);
        } else {
          // relying on the same odd chrome behaviour as above.
          stream.removeTrack(sender.track);
        }
        pc.dispatchEvent(new Event('negotiationneeded'));
      }
    };
  },

  shimPeerConnection: function(window) {
    var browserDetails = utils.detectBrowser(window);

    // The RTCPeerConnection object.
    if (!window.RTCPeerConnection) {
      window.RTCPeerConnection = function(pcConfig, pcConstraints) {
        // Translate iceTransportPolicy to iceTransports,
        // see https://code.google.com/p/webrtc/issues/detail?id=4869
        // this was fixed in M56 along with unprefixing RTCPeerConnection.
        logging('PeerConnection');
        if (pcConfig && pcConfig.iceTransportPolicy) {
          pcConfig.iceTransports = pcConfig.iceTransportPolicy;
        }

        return new window.webkitRTCPeerConnection(pcConfig, pcConstraints);
      };
      window.RTCPeerConnection.prototype =
          window.webkitRTCPeerConnection.prototype;
      // wrap static methods. Currently just generateCertificate.
      if (window.webkitRTCPeerConnection.generateCertificate) {
        Object.defineProperty(window.RTCPeerConnection, 'generateCertificate', {
          get: function() {
            return window.webkitRTCPeerConnection.generateCertificate;
          }
        });
      }
    } else {
      // migrate from non-spec RTCIceServer.url to RTCIceServer.urls
      var OrigPeerConnection = window.RTCPeerConnection;
      window.RTCPeerConnection = function(pcConfig, pcConstraints) {
        if (pcConfig && pcConfig.iceServers) {
          var newIceServers = [];
          for (var i = 0; i < pcConfig.iceServers.length; i++) {
            var server = pcConfig.iceServers[i];
            if (!server.hasOwnProperty('urls') &&
                server.hasOwnProperty('url')) {
              utils.deprecated('RTCIceServer.url', 'RTCIceServer.urls');
              server = JSON.parse(JSON.stringify(server));
              server.urls = server.url;
              newIceServers.push(server);
            } else {
              newIceServers.push(pcConfig.iceServers[i]);
            }
          }
          pcConfig.iceServers = newIceServers;
        }
        return new OrigPeerConnection(pcConfig, pcConstraints);
      };
      window.RTCPeerConnection.prototype = OrigPeerConnection.prototype;
      // wrap static methods. Currently just generateCertificate.
      Object.defineProperty(window.RTCPeerConnection, 'generateCertificate', {
        get: function() {
          return OrigPeerConnection.generateCertificate;
        }
      });
    }

    var origGetStats = window.RTCPeerConnection.prototype.getStats;
    window.RTCPeerConnection.prototype.getStats = function(selector,
        successCallback, errorCallback) {
      var self = this;
      var args = arguments;

      // If selector is a function then we are in the old style stats so just
      // pass back the original getStats format to avoid breaking old users.
      if (arguments.length > 0 && typeof selector === 'function') {
        return origGetStats.apply(this, arguments);
      }

      // When spec-style getStats is supported, return those when called with
      // either no arguments or the selector argument is null.
      if (origGetStats.length === 0 && (arguments.length === 0 ||
          typeof arguments[0] !== 'function')) {
        return origGetStats.apply(this, []);
      }

      var fixChromeStats_ = function(response) {
        var standardReport = {};
        var reports = response.result();
        reports.forEach(function(report) {
          var standardStats = {
            id: report.id,
            timestamp: report.timestamp,
            type: {
              localcandidate: 'local-candidate',
              remotecandidate: 'remote-candidate'
            }[report.type] || report.type
          };
          report.names().forEach(function(name) {
            standardStats[name] = report.stat(name);
          });
          standardReport[standardStats.id] = standardStats;
        });

        return standardReport;
      };

      // shim getStats with maplike support
      var makeMapStats = function(stats) {
        return new Map(Object.keys(stats).map(function(key) {
          return [key, stats[key]];
        }));
      };

      if (arguments.length >= 2) {
        var successCallbackWrapper_ = function(response) {
          args[1](makeMapStats(fixChromeStats_(response)));
        };

        return origGetStats.apply(this, [successCallbackWrapper_,
          arguments[0]]);
      }

      // promise-support
      return new Promise(function(resolve, reject) {
        origGetStats.apply(self, [
          function(response) {
            resolve(makeMapStats(fixChromeStats_(response)));
          }, reject]);
      }).then(successCallback, errorCallback);
    };

    // add promise support -- natively available in Chrome 51
    if (browserDetails.version < 51) {
      ['setLocalDescription', 'setRemoteDescription', 'addIceCandidate']
          .forEach(function(method) {
            var nativeMethod = window.RTCPeerConnection.prototype[method];
            window.RTCPeerConnection.prototype[method] = function() {
              var args = arguments;
              var self = this;
              var promise = new Promise(function(resolve, reject) {
                nativeMethod.apply(self, [args[0], resolve, reject]);
              });
              if (args.length < 2) {
                return promise;
              }
              return promise.then(function() {
                args[1].apply(null, []);
              },
              function(err) {
                if (args.length >= 3) {
                  args[2].apply(null, [err]);
                }
              });
            };
          });
    }

    // promise support for createOffer and createAnswer. Available (without
    // bugs) since M52: crbug/619289
    if (browserDetails.version < 52) {
      ['createOffer', 'createAnswer'].forEach(function(method) {
        var nativeMethod = window.RTCPeerConnection.prototype[method];
        window.RTCPeerConnection.prototype[method] = function() {
          var self = this;
          if (arguments.length < 1 || (arguments.length === 1 &&
              typeof arguments[0] === 'object')) {
            var opts = arguments.length === 1 ? arguments[0] : undefined;
            return new Promise(function(resolve, reject) {
              nativeMethod.apply(self, [resolve, reject, opts]);
            });
          }
          return nativeMethod.apply(this, arguments);
        };
      });
    }

    // shim implicit creation of RTCSessionDescription/RTCIceCandidate
    ['setLocalDescription', 'setRemoteDescription', 'addIceCandidate']
        .forEach(function(method) {
          var nativeMethod = window.RTCPeerConnection.prototype[method];
          window.RTCPeerConnection.prototype[method] = function() {
            arguments[0] = new ((method === 'addIceCandidate') ?
                window.RTCIceCandidate :
                window.RTCSessionDescription)(arguments[0]);
            return nativeMethod.apply(this, arguments);
          };
        });

    // support for addIceCandidate(null or undefined)
    var nativeAddIceCandidate =
        window.RTCPeerConnection.prototype.addIceCandidate;
    window.RTCPeerConnection.prototype.addIceCandidate = function() {
      if (!arguments[0]) {
        if (arguments[1]) {
          arguments[1].apply(null);
        }
        return Promise.resolve();
      }
      return nativeAddIceCandidate.apply(this, arguments);
    };
  }
};


// Expose public methods.
module.exports = {
  shimMediaStream: chromeShim.shimMediaStream,
  shimOnTrack: chromeShim.shimOnTrack,
  shimAddTrackRemoveTrack: chromeShim.shimAddTrackRemoveTrack,
  shimGetSendersWithDtmf: chromeShim.shimGetSendersWithDtmf,
  shimSourceObject: chromeShim.shimSourceObject,
  shimPeerConnection: chromeShim.shimPeerConnection,
  shimGetUserMedia: __webpack_require__(50)
};


/***/ }),
/* 50 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/*
 *  Copyright (c) 2016 The WebRTC project authors. All Rights Reserved.
 *
 *  Use of this source code is governed by a BSD-style license
 *  that can be found in the LICENSE file in the root of the source
 *  tree.
 */
 /* eslint-env node */

var utils = __webpack_require__(7);
var logging = utils.log;

// Expose public methods.
module.exports = function(window) {
  var browserDetails = utils.detectBrowser(window);
  var navigator = window && window.navigator;

  var constraintsToChrome_ = function(c) {
    if (typeof c !== 'object' || c.mandatory || c.optional) {
      return c;
    }
    var cc = {};
    Object.keys(c).forEach(function(key) {
      if (key === 'require' || key === 'advanced' || key === 'mediaSource') {
        return;
      }
      var r = (typeof c[key] === 'object') ? c[key] : {ideal: c[key]};
      if (r.exact !== undefined && typeof r.exact === 'number') {
        r.min = r.max = r.exact;
      }
      var oldname_ = function(prefix, name) {
        if (prefix) {
          return prefix + name.charAt(0).toUpperCase() + name.slice(1);
        }
        return (name === 'deviceId') ? 'sourceId' : name;
      };
      if (r.ideal !== undefined) {
        cc.optional = cc.optional || [];
        var oc = {};
        if (typeof r.ideal === 'number') {
          oc[oldname_('min', key)] = r.ideal;
          cc.optional.push(oc);
          oc = {};
          oc[oldname_('max', key)] = r.ideal;
          cc.optional.push(oc);
        } else {
          oc[oldname_('', key)] = r.ideal;
          cc.optional.push(oc);
        }
      }
      if (r.exact !== undefined && typeof r.exact !== 'number') {
        cc.mandatory = cc.mandatory || {};
        cc.mandatory[oldname_('', key)] = r.exact;
      } else {
        ['min', 'max'].forEach(function(mix) {
          if (r[mix] !== undefined) {
            cc.mandatory = cc.mandatory || {};
            cc.mandatory[oldname_(mix, key)] = r[mix];
          }
        });
      }
    });
    if (c.advanced) {
      cc.optional = (cc.optional || []).concat(c.advanced);
    }
    return cc;
  };

  var shimConstraints_ = function(constraints, func) {
    if (browserDetails.version >= 61) {
      return func(constraints);
    }
    constraints = JSON.parse(JSON.stringify(constraints));
    if (constraints && typeof constraints.audio === 'object') {
      var remap = function(obj, a, b) {
        if (a in obj && !(b in obj)) {
          obj[b] = obj[a];
          delete obj[a];
        }
      };
      constraints = JSON.parse(JSON.stringify(constraints));
      remap(constraints.audio, 'autoGainControl', 'googAutoGainControl');
      remap(constraints.audio, 'noiseSuppression', 'googNoiseSuppression');
      constraints.audio = constraintsToChrome_(constraints.audio);
    }
    if (constraints && typeof constraints.video === 'object') {
      // Shim facingMode for mobile & surface pro.
      var face = constraints.video.facingMode;
      face = face && ((typeof face === 'object') ? face : {ideal: face});
      var getSupportedFacingModeLies = browserDetails.version < 66;

      if ((face && (face.exact === 'user' || face.exact === 'environment' ||
                    face.ideal === 'user' || face.ideal === 'environment')) &&
          !(navigator.mediaDevices.getSupportedConstraints &&
            navigator.mediaDevices.getSupportedConstraints().facingMode &&
            !getSupportedFacingModeLies)) {
        delete constraints.video.facingMode;
        var matches;
        if (face.exact === 'environment' || face.ideal === 'environment') {
          matches = ['back', 'rear'];
        } else if (face.exact === 'user' || face.ideal === 'user') {
          matches = ['front'];
        }
        if (matches) {
          // Look for matches in label, or use last cam for back (typical).
          return navigator.mediaDevices.enumerateDevices()
          .then(function(devices) {
            devices = devices.filter(function(d) {
              return d.kind === 'videoinput';
            });
            var dev = devices.find(function(d) {
              return matches.some(function(match) {
                return d.label.toLowerCase().indexOf(match) !== -1;
              });
            });
            if (!dev && devices.length && matches.indexOf('back') !== -1) {
              dev = devices[devices.length - 1]; // more likely the back cam
            }
            if (dev) {
              constraints.video.deviceId = face.exact ? {exact: dev.deviceId} :
                                                        {ideal: dev.deviceId};
            }
            constraints.video = constraintsToChrome_(constraints.video);
            logging('chrome: ' + JSON.stringify(constraints));
            return func(constraints);
          });
        }
      }
      constraints.video = constraintsToChrome_(constraints.video);
    }
    logging('chrome: ' + JSON.stringify(constraints));
    return func(constraints);
  };

  var shimError_ = function(e) {
    return {
      name: {
        PermissionDeniedError: 'NotAllowedError',
        InvalidStateError: 'NotReadableError',
        DevicesNotFoundError: 'NotFoundError',
        ConstraintNotSatisfiedError: 'OverconstrainedError',
        TrackStartError: 'NotReadableError',
        MediaDeviceFailedDueToShutdown: 'NotReadableError',
        MediaDeviceKillSwitchOn: 'NotReadableError'
      }[e.name] || e.name,
      message: e.message,
      constraint: e.constraintName,
      toString: function() {
        return this.name + (this.message && ': ') + this.message;
      }
    };
  };

  var getUserMedia_ = function(constraints, onSuccess, onError) {
    shimConstraints_(constraints, function(c) {
      navigator.webkitGetUserMedia(c, onSuccess, function(e) {
        if (onError) {
          onError(shimError_(e));
        }
      });
    });
  };

  navigator.getUserMedia = getUserMedia_;

  // Returns the result of getUserMedia as a Promise.
  var getUserMediaPromise_ = function(constraints) {
    return new Promise(function(resolve, reject) {
      navigator.getUserMedia(constraints, resolve, reject);
    });
  };

  if (!navigator.mediaDevices) {
    navigator.mediaDevices = {
      getUserMedia: getUserMediaPromise_,
      enumerateDevices: function() {
        return new Promise(function(resolve) {
          var kinds = {audio: 'audioinput', video: 'videoinput'};
          return window.MediaStreamTrack.getSources(function(devices) {
            resolve(devices.map(function(device) {
              return {label: device.label,
                kind: kinds[device.kind],
                deviceId: device.id,
                groupId: ''};
            }));
          });
        });
      },
      getSupportedConstraints: function() {
        return {
          deviceId: true, echoCancellation: true, facingMode: true,
          frameRate: true, height: true, width: true
        };
      }
    };
  }

  // A shim for getUserMedia method on the mediaDevices object.
  // TODO(KaptenJansson) remove once implemented in Chrome stable.
  if (!navigator.mediaDevices.getUserMedia) {
    navigator.mediaDevices.getUserMedia = function(constraints) {
      return getUserMediaPromise_(constraints);
    };
  } else {
    // Even though Chrome 45 has navigator.mediaDevices and a getUserMedia
    // function which returns a Promise, it does not accept spec-style
    // constraints.
    var origGetUserMedia = navigator.mediaDevices.getUserMedia.
        bind(navigator.mediaDevices);
    navigator.mediaDevices.getUserMedia = function(cs) {
      return shimConstraints_(cs, function(c) {
        return origGetUserMedia(c).then(function(stream) {
          if (c.audio && !stream.getAudioTracks().length ||
              c.video && !stream.getVideoTracks().length) {
            stream.getTracks().forEach(function(track) {
              track.stop();
            });
            throw new DOMException('', 'NotFoundError');
          }
          return stream;
        }, function(e) {
          return Promise.reject(shimError_(e));
        });
      });
    };
  }

  // Dummy devicechange event methods.
  // TODO(KaptenJansson) remove once implemented in Chrome stable.
  if (typeof navigator.mediaDevices.addEventListener === 'undefined') {
    navigator.mediaDevices.addEventListener = function() {
      logging('Dummy mediaDevices.addEventListener called.');
    };
  }
  if (typeof navigator.mediaDevices.removeEventListener === 'undefined') {
    navigator.mediaDevices.removeEventListener = function() {
      logging('Dummy mediaDevices.removeEventListener called.');
    };
  }
};


/***/ }),
/* 51 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/*
 *  Copyright (c) 2016 The WebRTC project authors. All Rights Reserved.
 *
 *  Use of this source code is governed by a BSD-style license
 *  that can be found in the LICENSE file in the root of the source
 *  tree.
 */
 /* eslint-env node */


var utils = __webpack_require__(7);
var shimRTCPeerConnection = __webpack_require__(52);

module.exports = {
  shimGetUserMedia: __webpack_require__(53),
  shimPeerConnection: function(window) {
    var browserDetails = utils.detectBrowser(window);

    if (window.RTCIceGatherer) {
      // ORTC defines an RTCIceCandidate object but no constructor.
      // Not implemented in Edge.
      if (!window.RTCIceCandidate) {
        window.RTCIceCandidate = function(args) {
          return args;
        };
      }
      // ORTC does not have a session description object but
      // other browsers (i.e. Chrome) that will support both PC and ORTC
      // in the future might have this defined already.
      if (!window.RTCSessionDescription) {
        window.RTCSessionDescription = function(args) {
          return args;
        };
      }
      // this adds an additional event listener to MediaStrackTrack that signals
      // when a tracks enabled property was changed. Workaround for a bug in
      // addStream, see below. No longer required in 15025+
      if (browserDetails.version < 15025) {
        var origMSTEnabled = Object.getOwnPropertyDescriptor(
            window.MediaStreamTrack.prototype, 'enabled');
        Object.defineProperty(window.MediaStreamTrack.prototype, 'enabled', {
          set: function(value) {
            origMSTEnabled.set.call(this, value);
            var ev = new Event('enabled');
            ev.enabled = value;
            this.dispatchEvent(ev);
          }
        });
      }
    }

    // ORTC defines the DTMF sender a bit different.
    // https://github.com/w3c/ortc/issues/714
    if (window.RTCRtpSender && !('dtmf' in window.RTCRtpSender.prototype)) {
      Object.defineProperty(window.RTCRtpSender.prototype, 'dtmf', {
        get: function() {
          if (this._dtmf === undefined) {
            if (this.track.kind === 'audio') {
              this._dtmf = new window.RTCDtmfSender(this);
            } else if (this.track.kind === 'video') {
              this._dtmf = null;
            }
          }
          return this._dtmf;
        }
      });
    }

    window.RTCPeerConnection =
        shimRTCPeerConnection(window, browserDetails.version);
  },
  shimReplaceTrack: function(window) {
    // ORTC has replaceTrack -- https://github.com/w3c/ortc/issues/614
    if (window.RTCRtpSender &&
        !('replaceTrack' in window.RTCRtpSender.prototype)) {
      window.RTCRtpSender.prototype.replaceTrack =
          window.RTCRtpSender.prototype.setTrack;
    }
  }
};


/***/ }),
/* 52 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/*
 *  Copyright (c) 2017 The WebRTC project authors. All Rights Reserved.
 *
 *  Use of this source code is governed by a BSD-style license
 *  that can be found in the LICENSE file in the root of the source
 *  tree.
 */
 /* eslint-env node */


var SDPUtils = __webpack_require__(28);

function writeMediaSection(transceiver, caps, type, stream, dtlsRole) {
  var sdp = SDPUtils.writeRtpDescription(transceiver.kind, caps);

  // Map ICE parameters (ufrag, pwd) to SDP.
  sdp += SDPUtils.writeIceParameters(
      transceiver.iceGatherer.getLocalParameters());

  // Map DTLS parameters to SDP.
  sdp += SDPUtils.writeDtlsParameters(
      transceiver.dtlsTransport.getLocalParameters(),
      type === 'offer' ? 'actpass' : dtlsRole || 'active');

  sdp += 'a=mid:' + transceiver.mid + '\r\n';

  if (transceiver.rtpSender && transceiver.rtpReceiver) {
    sdp += 'a=sendrecv\r\n';
  } else if (transceiver.rtpSender) {
    sdp += 'a=sendonly\r\n';
  } else if (transceiver.rtpReceiver) {
    sdp += 'a=recvonly\r\n';
  } else {
    sdp += 'a=inactive\r\n';
  }

  if (transceiver.rtpSender) {
    // spec.
    var msid = 'msid:' + stream.id + ' ' +
        transceiver.rtpSender.track.id + '\r\n';
    sdp += 'a=' + msid;

    // for Chrome.
    sdp += 'a=ssrc:' + transceiver.sendEncodingParameters[0].ssrc +
        ' ' + msid;
    if (transceiver.sendEncodingParameters[0].rtx) {
      sdp += 'a=ssrc:' + transceiver.sendEncodingParameters[0].rtx.ssrc +
          ' ' + msid;
      sdp += 'a=ssrc-group:FID ' +
          transceiver.sendEncodingParameters[0].ssrc + ' ' +
          transceiver.sendEncodingParameters[0].rtx.ssrc +
          '\r\n';
    }
  }
  // FIXME: this should be written by writeRtpDescription.
  sdp += 'a=ssrc:' + transceiver.sendEncodingParameters[0].ssrc +
      ' cname:' + SDPUtils.localCName + '\r\n';
  if (transceiver.rtpSender && transceiver.sendEncodingParameters[0].rtx) {
    sdp += 'a=ssrc:' + transceiver.sendEncodingParameters[0].rtx.ssrc +
        ' cname:' + SDPUtils.localCName + '\r\n';
  }
  return sdp;
}

// Edge does not like
// 1) stun: filtered after 14393 unless ?transport=udp is present
// 2) turn: that does not have all of turn:host:port?transport=udp
// 3) turn: with ipv6 addresses
// 4) turn: occurring muliple times
function filterIceServers(iceServers, edgeVersion) {
  var hasTurn = false;
  iceServers = JSON.parse(JSON.stringify(iceServers));
  return iceServers.filter(function(server) {
    if (server && (server.urls || server.url)) {
      var urls = server.urls || server.url;
      if (server.url && !server.urls) {
        console.warn('RTCIceServer.url is deprecated! Use urls instead.');
      }
      var isString = typeof urls === 'string';
      if (isString) {
        urls = [urls];
      }
      urls = urls.filter(function(url) {
        var validTurn = url.indexOf('turn:') === 0 &&
            url.indexOf('transport=udp') !== -1 &&
            url.indexOf('turn:[') === -1 &&
            !hasTurn;

        if (validTurn) {
          hasTurn = true;
          return true;
        }
        return url.indexOf('stun:') === 0 && edgeVersion >= 14393 &&
            url.indexOf('?transport=udp') === -1;
      });

      delete server.url;
      server.urls = isString ? urls[0] : urls;
      return !!urls.length;
    }
  });
}

// Determines the intersection of local and remote capabilities.
function getCommonCapabilities(localCapabilities, remoteCapabilities) {
  var commonCapabilities = {
    codecs: [],
    headerExtensions: [],
    fecMechanisms: []
  };

  var findCodecByPayloadType = function(pt, codecs) {
    pt = parseInt(pt, 10);
    for (var i = 0; i < codecs.length; i++) {
      if (codecs[i].payloadType === pt ||
          codecs[i].preferredPayloadType === pt) {
        return codecs[i];
      }
    }
  };

  var rtxCapabilityMatches = function(lRtx, rRtx, lCodecs, rCodecs) {
    var lCodec = findCodecByPayloadType(lRtx.parameters.apt, lCodecs);
    var rCodec = findCodecByPayloadType(rRtx.parameters.apt, rCodecs);
    return lCodec && rCodec &&
        lCodec.name.toLowerCase() === rCodec.name.toLowerCase();
  };

  localCapabilities.codecs.forEach(function(lCodec) {
    for (var i = 0; i < remoteCapabilities.codecs.length; i++) {
      var rCodec = remoteCapabilities.codecs[i];
      if (lCodec.name.toLowerCase() === rCodec.name.toLowerCase() &&
          lCodec.clockRate === rCodec.clockRate) {
        if (lCodec.name.toLowerCase() === 'rtx' &&
            lCodec.parameters && rCodec.parameters.apt) {
          // for RTX we need to find the local rtx that has a apt
          // which points to the same local codec as the remote one.
          if (!rtxCapabilityMatches(lCodec, rCodec,
              localCapabilities.codecs, remoteCapabilities.codecs)) {
            continue;
          }
        }
        rCodec = JSON.parse(JSON.stringify(rCodec)); // deepcopy
        // number of channels is the highest common number of channels
        rCodec.numChannels = Math.min(lCodec.numChannels,
            rCodec.numChannels);
        // push rCodec so we reply with offerer payload type
        commonCapabilities.codecs.push(rCodec);

        // determine common feedback mechanisms
        rCodec.rtcpFeedback = rCodec.rtcpFeedback.filter(function(fb) {
          for (var j = 0; j < lCodec.rtcpFeedback.length; j++) {
            if (lCodec.rtcpFeedback[j].type === fb.type &&
                lCodec.rtcpFeedback[j].parameter === fb.parameter) {
              return true;
            }
          }
          return false;
        });
        // FIXME: also need to determine .parameters
        //  see https://github.com/openpeer/ortc/issues/569
        break;
      }
    }
  });

  localCapabilities.headerExtensions.forEach(function(lHeaderExtension) {
    for (var i = 0; i < remoteCapabilities.headerExtensions.length;
         i++) {
      var rHeaderExtension = remoteCapabilities.headerExtensions[i];
      if (lHeaderExtension.uri === rHeaderExtension.uri) {
        commonCapabilities.headerExtensions.push(rHeaderExtension);
        break;
      }
    }
  });

  // FIXME: fecMechanisms
  return commonCapabilities;
}

// is action=setLocalDescription with type allowed in signalingState
function isActionAllowedInSignalingState(action, type, signalingState) {
  return {
    offer: {
      setLocalDescription: ['stable', 'have-local-offer'],
      setRemoteDescription: ['stable', 'have-remote-offer']
    },
    answer: {
      setLocalDescription: ['have-remote-offer', 'have-local-pranswer'],
      setRemoteDescription: ['have-local-offer', 'have-remote-pranswer']
    }
  }[type][action].indexOf(signalingState) !== -1;
}

function maybeAddCandidate(iceTransport, candidate) {
  // Edge's internal representation adds some fields therefore
  // not all fieldѕ are taken into account.
  var alreadyAdded = iceTransport.getRemoteCandidates()
      .find(function(remoteCandidate) {
        return candidate.foundation === remoteCandidate.foundation &&
            candidate.ip === remoteCandidate.ip &&
            candidate.port === remoteCandidate.port &&
            candidate.priority === remoteCandidate.priority &&
            candidate.protocol === remoteCandidate.protocol &&
            candidate.type === remoteCandidate.type;
      });
  if (!alreadyAdded) {
    iceTransport.addRemoteCandidate(candidate);
  }
  return !alreadyAdded;
}


// https://w3c.github.io/mediacapture-main/#mediastream
// Helper function to add the track to the stream and
// dispatch the event ourselves.
function addTrackToStreamAndFireEvent(track, stream) {
  stream.addTrack(track);
  var e = new Event('addtrack'); // TODO: MediaStreamTrackEvent
  e.track = track;
  stream.dispatchEvent(e);
}

function removeTrackFromStreamAndFireEvent(track, stream) {
  stream.removeTrack(track);
  var e = new Event('removetrack'); // TODO: MediaStreamTrackEvent
  e.track = track;
  stream.dispatchEvent(e);
}

function fireAddTrack(pc, track, receiver, streams) {
  var trackEvent = new Event('track');
  trackEvent.track = track;
  trackEvent.receiver = receiver;
  trackEvent.transceiver = {receiver: receiver};
  trackEvent.streams = streams;
  window.setTimeout(function() {
    pc.dispatchEvent(trackEvent);
    if (typeof pc.ontrack === 'function') {
      pc.ontrack(trackEvent);
    }
  });
}

module.exports = function(window, edgeVersion) {
  var RTCPeerConnection = function(config) {
    var self = this;

    var _eventTarget = document.createDocumentFragment();
    ['addEventListener', 'removeEventListener', 'dispatchEvent']
        .forEach(function(method) {
          self[method] = _eventTarget[method].bind(_eventTarget);
        });

    this.onicecandidate = null;
    this.onaddstream = null;
    this.ontrack = null;
    this.onremovestream = null;
    this.onsignalingstatechange = null;
    this.oniceconnectionstatechange = null;
    this.onicegatheringstatechange = null;
    this.onnegotiationneeded = null;
    this.ondatachannel = null;
    this.canTrickleIceCandidates = null;

    this.needNegotiation = false;

    this.localStreams = [];
    this.remoteStreams = [];

    this.localDescription = null;
    this.remoteDescription = null;

    this.signalingState = 'stable';
    this.iceConnectionState = 'new';
    this.iceGatheringState = 'new';

    config = JSON.parse(JSON.stringify(config || {}));

    this.usingBundle = config.bundlePolicy === 'max-bundle';
    if (config.rtcpMuxPolicy === 'negotiate') {
      var e = new Error('rtcpMuxPolicy \'negotiate\' is not supported');
      e.name = 'NotSupportedError';
      throw(e);
    } else if (!config.rtcpMuxPolicy) {
      config.rtcpMuxPolicy = 'require';
    }

    switch (config.iceTransportPolicy) {
      case 'all':
      case 'relay':
        break;
      default:
        config.iceTransportPolicy = 'all';
        break;
    }

    switch (config.bundlePolicy) {
      case 'balanced':
      case 'max-compat':
      case 'max-bundle':
        break;
      default:
        config.bundlePolicy = 'balanced';
        break;
    }

    config.iceServers = filterIceServers(config.iceServers || [], edgeVersion);

    this._iceGatherers = [];
    if (config.iceCandidatePoolSize) {
      for (var i = config.iceCandidatePoolSize; i > 0; i--) {
        this._iceGatherers = new window.RTCIceGatherer({
          iceServers: config.iceServers,
          gatherPolicy: config.iceTransportPolicy
        });
      }
    } else {
      config.iceCandidatePoolSize = 0;
    }

    this._config = config;

    // per-track iceGathers, iceTransports, dtlsTransports, rtpSenders, ...
    // everything that is needed to describe a SDP m-line.
    this.transceivers = [];

    this._sdpSessionId = SDPUtils.generateSessionId();
    this._sdpSessionVersion = 0;

    this._dtlsRole = undefined; // role for a=setup to use in answers.
  };

  RTCPeerConnection.prototype._emitGatheringStateChange = function() {
    var event = new Event('icegatheringstatechange');
    this.dispatchEvent(event);
    if (typeof this.onicegatheringstatechange === 'function') {
      this.onicegatheringstatechange(event);
    }
  };

  RTCPeerConnection.prototype.getConfiguration = function() {
    return this._config;
  };

  RTCPeerConnection.prototype.getLocalStreams = function() {
    return this.localStreams;
  };

  RTCPeerConnection.prototype.getRemoteStreams = function() {
    return this.remoteStreams;
  };

  // internal helper to create a transceiver object.
  // (whih is not yet the same as the WebRTC 1.0 transceiver)
  RTCPeerConnection.prototype._createTransceiver = function(kind) {
    var hasBundleTransport = this.transceivers.length > 0;
    var transceiver = {
      track: null,
      iceGatherer: null,
      iceTransport: null,
      dtlsTransport: null,
      localCapabilities: null,
      remoteCapabilities: null,
      rtpSender: null,
      rtpReceiver: null,
      kind: kind,
      mid: null,
      sendEncodingParameters: null,
      recvEncodingParameters: null,
      stream: null,
      associatedRemoteMediaStreams: [],
      wantReceive: true
    };
    if (this.usingBundle && hasBundleTransport) {
      transceiver.iceTransport = this.transceivers[0].iceTransport;
      transceiver.dtlsTransport = this.transceivers[0].dtlsTransport;
    } else {
      var transports = this._createIceAndDtlsTransports();
      transceiver.iceTransport = transports.iceTransport;
      transceiver.dtlsTransport = transports.dtlsTransport;
    }
    this.transceivers.push(transceiver);
    return transceiver;
  };

  RTCPeerConnection.prototype.addTrack = function(track, stream) {
    var transceiver;
    for (var i = 0; i < this.transceivers.length; i++) {
      if (!this.transceivers[i].track &&
          this.transceivers[i].kind === track.kind) {
        transceiver = this.transceivers[i];
      }
    }
    if (!transceiver) {
      transceiver = this._createTransceiver(track.kind);
    }

    this._maybeFireNegotiationNeeded();

    if (this.localStreams.indexOf(stream) === -1) {
      this.localStreams.push(stream);
    }

    transceiver.track = track;
    transceiver.stream = stream;
    transceiver.rtpSender = new window.RTCRtpSender(track,
        transceiver.dtlsTransport);
    return transceiver.rtpSender;
  };

  RTCPeerConnection.prototype.addStream = function(stream) {
    var self = this;
    if (edgeVersion >= 15025) {
      stream.getTracks().forEach(function(track) {
        self.addTrack(track, stream);
      });
    } else {
      // Clone is necessary for local demos mostly, attaching directly
      // to two different senders does not work (build 10547).
      // Fixed in 15025 (or earlier)
      var clonedStream = stream.clone();
      stream.getTracks().forEach(function(track, idx) {
        var clonedTrack = clonedStream.getTracks()[idx];
        track.addEventListener('enabled', function(event) {
          clonedTrack.enabled = event.enabled;
        });
      });
      clonedStream.getTracks().forEach(function(track) {
        self.addTrack(track, clonedStream);
      });
    }
  };

  RTCPeerConnection.prototype.removeTrack = function(sender) {
    if (!(sender instanceof window.RTCRtpSender)) {
      throw new TypeError('Argument 1 of RTCPeerConnection.removeTrack ' +
          'does not implement interface RTCRtpSender.');
    }

    var transceiver = this.transceivers.find(function(t) {
      return t.rtpSender === sender;
    });

    if (!transceiver) {
      var err = new Error('Sender was not created by this connection.');
      err.name = 'InvalidAccessError';
      throw err;
    }
    var stream = transceiver.stream;

    transceiver.rtpSender.stop();
    transceiver.rtpSender = null;
    transceiver.track = null;
    transceiver.stream = null;

    // remove the stream from the set of local streams
    var localStreams = this.transceivers.map(function(t) {
      return t.stream;
    });
    if (localStreams.indexOf(stream) === -1 &&
        this.localStreams.indexOf(stream) > -1) {
      this.localStreams.splice(this.localStreams.indexOf(stream), 1);
    }

    this._maybeFireNegotiationNeeded();
  };

  RTCPeerConnection.prototype.removeStream = function(stream) {
    var self = this;
    stream.getTracks().forEach(function(track) {
      var sender = self.getSenders().find(function(s) {
        return s.track === track;
      });
      if (sender) {
        self.removeTrack(sender);
      }
    });
  };

  RTCPeerConnection.prototype.getSenders = function() {
    return this.transceivers.filter(function(transceiver) {
      return !!transceiver.rtpSender;
    })
    .map(function(transceiver) {
      return transceiver.rtpSender;
    });
  };

  RTCPeerConnection.prototype.getReceivers = function() {
    return this.transceivers.filter(function(transceiver) {
      return !!transceiver.rtpReceiver;
    })
    .map(function(transceiver) {
      return transceiver.rtpReceiver;
    });
  };


  RTCPeerConnection.prototype._createIceGatherer = function(sdpMLineIndex,
      usingBundle) {
    var self = this;
    if (usingBundle && sdpMLineIndex > 0) {
      return this.transceivers[0].iceGatherer;
    } else if (this._iceGatherers.length) {
      return this._iceGatherers.shift();
    }
    var iceGatherer = new window.RTCIceGatherer({
      iceServers: this._config.iceServers,
      gatherPolicy: this._config.iceTransportPolicy
    });
    Object.defineProperty(iceGatherer, 'state',
        {value: 'new', writable: true}
    );

    this.transceivers[sdpMLineIndex].candidates = [];
    this.transceivers[sdpMLineIndex].bufferCandidates = function(event) {
      var end = !event.candidate || Object.keys(event.candidate).length === 0;
      // polyfill since RTCIceGatherer.state is not implemented in
      // Edge 10547 yet.
      iceGatherer.state = end ? 'completed' : 'gathering';
      if (self.transceivers[sdpMLineIndex].candidates !== null) {
        self.transceivers[sdpMLineIndex].candidates.push(event.candidate);
      }
    };
    iceGatherer.addEventListener('localcandidate',
      this.transceivers[sdpMLineIndex].bufferCandidates);
    return iceGatherer;
  };

  // start gathering from an RTCIceGatherer.
  RTCPeerConnection.prototype._gather = function(mid, sdpMLineIndex) {
    var self = this;
    var iceGatherer = this.transceivers[sdpMLineIndex].iceGatherer;
    if (iceGatherer.onlocalcandidate) {
      return;
    }
    var candidates = this.transceivers[sdpMLineIndex].candidates;
    this.transceivers[sdpMLineIndex].candidates = null;
    iceGatherer.removeEventListener('localcandidate',
      this.transceivers[sdpMLineIndex].bufferCandidates);
    iceGatherer.onlocalcandidate = function(evt) {
      if (self.usingBundle && sdpMLineIndex > 0) {
        // if we know that we use bundle we can drop candidates with
        // ѕdpMLineIndex > 0. If we don't do this then our state gets
        // confused since we dispose the extra ice gatherer.
        return;
      }
      var event = new Event('icecandidate');
      event.candidate = {sdpMid: mid, sdpMLineIndex: sdpMLineIndex};

      var cand = evt.candidate;
      // Edge emits an empty object for RTCIceCandidateComplete‥
      var end = !cand || Object.keys(cand).length === 0;
      if (end) {
        // polyfill since RTCIceGatherer.state is not implemented in
        // Edge 10547 yet.
        if (iceGatherer.state === 'new' || iceGatherer.state === 'gathering') {
          iceGatherer.state = 'completed';
        }
      } else {
        if (iceGatherer.state === 'new') {
          iceGatherer.state = 'gathering';
        }
        // RTCIceCandidate doesn't have a component, needs to be added
        cand.component = 1;
        event.candidate.candidate = SDPUtils.writeCandidate(cand);
      }

      // update local description.
      var sections = SDPUtils.splitSections(self.localDescription.sdp);
      if (!end) {
        sections[event.candidate.sdpMLineIndex + 1] +=
            'a=' + event.candidate.candidate + '\r\n';
      } else {
        sections[event.candidate.sdpMLineIndex + 1] +=
            'a=end-of-candidates\r\n';
      }
      self.localDescription.sdp = sections.join('');
      var complete = self.transceivers.every(function(transceiver) {
        return transceiver.iceGatherer &&
            transceiver.iceGatherer.state === 'completed';
      });

      if (self.iceGatheringState !== 'gathering') {
        self.iceGatheringState = 'gathering';
        self._emitGatheringStateChange();
      }

      // Emit candidate. Also emit null candidate when all gatherers are
      // complete.
      if (!end) {
        self.dispatchEvent(event);
        if (typeof self.onicecandidate === 'function') {
          self.onicecandidate(event);
        }
      }
      if (complete) {
        self.dispatchEvent(new Event('icecandidate'));
        if (typeof self.onicecandidate === 'function') {
          self.onicecandidate(new Event('icecandidate'));
        }
        self.iceGatheringState = 'complete';
        self._emitGatheringStateChange();
      }
    };

    // emit already gathered candidates.
    window.setTimeout(function() {
      candidates.forEach(function(candidate) {
        var e = new Event('RTCIceGatherEvent');
        e.candidate = candidate;
        iceGatherer.onlocalcandidate(e);
      });
    }, 0);
  };

  // Create ICE transport and DTLS transport.
  RTCPeerConnection.prototype._createIceAndDtlsTransports = function() {
    var self = this;
    var iceTransport = new window.RTCIceTransport(null);
    iceTransport.onicestatechange = function() {
      self._updateConnectionState();
    };

    var dtlsTransport = new window.RTCDtlsTransport(iceTransport);
    dtlsTransport.ondtlsstatechange = function() {
      self._updateConnectionState();
    };
    dtlsTransport.onerror = function() {
      // onerror does not set state to failed by itself.
      Object.defineProperty(dtlsTransport, 'state',
          {value: 'failed', writable: true});
      self._updateConnectionState();
    };

    return {
      iceTransport: iceTransport,
      dtlsTransport: dtlsTransport
    };
  };

  // Destroy ICE gatherer, ICE transport and DTLS transport.
  // Without triggering the callbacks.
  RTCPeerConnection.prototype._disposeIceAndDtlsTransports = function(
      sdpMLineIndex) {
    var iceGatherer = this.transceivers[sdpMLineIndex].iceGatherer;
    if (iceGatherer) {
      delete iceGatherer.onlocalcandidate;
      delete this.transceivers[sdpMLineIndex].iceGatherer;
    }
    var iceTransport = this.transceivers[sdpMLineIndex].iceTransport;
    if (iceTransport) {
      delete iceTransport.onicestatechange;
      delete this.transceivers[sdpMLineIndex].iceTransport;
    }
    var dtlsTransport = this.transceivers[sdpMLineIndex].dtlsTransport;
    if (dtlsTransport) {
      delete dtlsTransport.ondtlsstatechange;
      delete dtlsTransport.onerror;
      delete this.transceivers[sdpMLineIndex].dtlsTransport;
    }
  };

  // Start the RTP Sender and Receiver for a transceiver.
  RTCPeerConnection.prototype._transceive = function(transceiver,
      send, recv) {
    var params = getCommonCapabilities(transceiver.localCapabilities,
        transceiver.remoteCapabilities);
    if (send && transceiver.rtpSender) {
      params.encodings = transceiver.sendEncodingParameters;
      params.rtcp = {
        cname: SDPUtils.localCName,
        compound: transceiver.rtcpParameters.compound
      };
      if (transceiver.recvEncodingParameters.length) {
        params.rtcp.ssrc = transceiver.recvEncodingParameters[0].ssrc;
      }
      transceiver.rtpSender.send(params);
    }
    if (recv && transceiver.rtpReceiver && params.codecs.length > 0) {
      // remove RTX field in Edge 14942
      if (transceiver.kind === 'video'
          && transceiver.recvEncodingParameters
          && edgeVersion < 15019) {
        transceiver.recvEncodingParameters.forEach(function(p) {
          delete p.rtx;
        });
      }
      if (transceiver.recvEncodingParameters.length) {
        params.encodings = transceiver.recvEncodingParameters;
      }
      params.rtcp = {
        compound: transceiver.rtcpParameters.compound
      };
      if (transceiver.rtcpParameters.cname) {
        params.rtcp.cname = transceiver.rtcpParameters.cname;
      }
      if (transceiver.sendEncodingParameters.length) {
        params.rtcp.ssrc = transceiver.sendEncodingParameters[0].ssrc;
      }
      transceiver.rtpReceiver.receive(params);
    }
  };

  RTCPeerConnection.prototype.setLocalDescription = function(description) {
    var self = this;
    var args = arguments;

    if (!isActionAllowedInSignalingState('setLocalDescription',
        description.type, this.signalingState)) {
      return new Promise(function(resolve, reject) {
        var e = new Error('Can not set local ' + description.type +
            ' in state ' + self.signalingState);
        e.name = 'InvalidStateError';
        if (args.length > 2 && typeof args[2] === 'function') {
          args[2].apply(null, [e]);
        }
        reject(e);
      });
    }

    var sections;
    var sessionpart;
    if (description.type === 'offer') {
      // VERY limited support for SDP munging. Limited to:
      // * changing the order of codecs
      sections = SDPUtils.splitSections(description.sdp);
      sessionpart = sections.shift();
      sections.forEach(function(mediaSection, sdpMLineIndex) {
        var caps = SDPUtils.parseRtpParameters(mediaSection);
        self.transceivers[sdpMLineIndex].localCapabilities = caps;
      });

      this.transceivers.forEach(function(transceiver, sdpMLineIndex) {
        self._gather(transceiver.mid, sdpMLineIndex);
      });
    } else if (description.type === 'answer') {
      sections = SDPUtils.splitSections(self.remoteDescription.sdp);
      sessionpart = sections.shift();
      var isIceLite = SDPUtils.matchPrefix(sessionpart,
          'a=ice-lite').length > 0;
      sections.forEach(function(mediaSection, sdpMLineIndex) {
        var transceiver = self.transceivers[sdpMLineIndex];
        var iceGatherer = transceiver.iceGatherer;
        var iceTransport = transceiver.iceTransport;
        var dtlsTransport = transceiver.dtlsTransport;
        var localCapabilities = transceiver.localCapabilities;
        var remoteCapabilities = transceiver.remoteCapabilities;

        // treat bundle-only as not-rejected.
        var rejected = SDPUtils.isRejected(mediaSection) &&
            !SDPUtils.matchPrefix(mediaSection, 'a=bundle-only').length === 1;

        if (!rejected && !transceiver.isDatachannel) {
          var remoteIceParameters = SDPUtils.getIceParameters(
              mediaSection, sessionpart);
          var remoteDtlsParameters = SDPUtils.getDtlsParameters(
              mediaSection, sessionpart);
          if (isIceLite) {
            remoteDtlsParameters.role = 'server';
          }

          if (!self.usingBundle || sdpMLineIndex === 0) {
            self._gather(transceiver.mid, sdpMLineIndex);
            if (iceTransport.state === 'new') {
              iceTransport.start(iceGatherer, remoteIceParameters,
                  isIceLite ? 'controlling' : 'controlled');
            }
            if (dtlsTransport.state === 'new') {
              dtlsTransport.start(remoteDtlsParameters);
            }
          }

          // Calculate intersection of capabilities.
          var params = getCommonCapabilities(localCapabilities,
              remoteCapabilities);

          // Start the RTCRtpSender. The RTCRtpReceiver for this
          // transceiver has already been started in setRemoteDescription.
          self._transceive(transceiver,
              params.codecs.length > 0,
              false);
        }
      });
    }

    this.localDescription = {
      type: description.type,
      sdp: description.sdp
    };
    switch (description.type) {
      case 'offer':
        this._updateSignalingState('have-local-offer');
        break;
      case 'answer':
        this._updateSignalingState('stable');
        break;
      default:
        throw new TypeError('unsupported type "' + description.type +
            '"');
    }

    // If a success callback was provided, emit ICE candidates after it
    // has been executed. Otherwise, emit callback after the Promise is
    // resolved.
    var cb = arguments.length > 1 && typeof arguments[1] === 'function' &&
        arguments[1];
    return new Promise(function(resolve) {
      if (cb) {
        cb.apply(null);
      }
      resolve();
    });
  };

  RTCPeerConnection.prototype.setRemoteDescription = function(description) {
    var self = this;
    var args = arguments;

    if (!isActionAllowedInSignalingState('setRemoteDescription',
        description.type, this.signalingState)) {
      return new Promise(function(resolve, reject) {
        var e = new Error('Can not set remote ' + description.type +
            ' in state ' + self.signalingState);
        e.name = 'InvalidStateError';
        if (args.length > 2 && typeof args[2] === 'function') {
          args[2].apply(null, [e]);
        }
        reject(e);
      });
    }

    var streams = {};
    this.remoteStreams.forEach(function(stream) {
      streams[stream.id] = stream;
    });
    var receiverList = [];
    var sections = SDPUtils.splitSections(description.sdp);
    var sessionpart = sections.shift();
    var isIceLite = SDPUtils.matchPrefix(sessionpart,
        'a=ice-lite').length > 0;
    var usingBundle = SDPUtils.matchPrefix(sessionpart,
        'a=group:BUNDLE ').length > 0;
    this.usingBundle = usingBundle;
    var iceOptions = SDPUtils.matchPrefix(sessionpart,
        'a=ice-options:')[0];
    if (iceOptions) {
      this.canTrickleIceCandidates = iceOptions.substr(14).split(' ')
          .indexOf('trickle') >= 0;
    } else {
      this.canTrickleIceCandidates = false;
    }

    sections.forEach(function(mediaSection, sdpMLineIndex) {
      var lines = SDPUtils.splitLines(mediaSection);
      var kind = SDPUtils.getKind(mediaSection);
      // treat bundle-only as not-rejected.
      var rejected = SDPUtils.isRejected(mediaSection) &&
          !SDPUtils.matchPrefix(mediaSection, 'a=bundle-only').length === 1;
      var protocol = lines[0].substr(2).split(' ')[2];

      var direction = SDPUtils.getDirection(mediaSection, sessionpart);
      var remoteMsid = SDPUtils.parseMsid(mediaSection);

      var mid = SDPUtils.getMid(mediaSection) || SDPUtils.generateIdentifier();

      // Reject datachannels which are not implemented yet.
      if (kind === 'application' && protocol === 'DTLS/SCTP') {
        self.transceivers[sdpMLineIndex] = {
          mid: mid,
          isDatachannel: true
        };
        return;
      }

      var transceiver;
      var iceGatherer;
      var iceTransport;
      var dtlsTransport;
      var rtpReceiver;
      var sendEncodingParameters;
      var recvEncodingParameters;
      var localCapabilities;

      var track;
      // FIXME: ensure the mediaSection has rtcp-mux set.
      var remoteCapabilities = SDPUtils.parseRtpParameters(mediaSection);
      var remoteIceParameters;
      var remoteDtlsParameters;
      if (!rejected) {
        remoteIceParameters = SDPUtils.getIceParameters(mediaSection,
            sessionpart);
        remoteDtlsParameters = SDPUtils.getDtlsParameters(mediaSection,
            sessionpart);
        remoteDtlsParameters.role = 'client';
      }
      recvEncodingParameters =
          SDPUtils.parseRtpEncodingParameters(mediaSection);

      var rtcpParameters = SDPUtils.parseRtcpParameters(mediaSection);

      var isComplete = SDPUtils.matchPrefix(mediaSection,
          'a=end-of-candidates', sessionpart).length > 0;
      var cands = SDPUtils.matchPrefix(mediaSection, 'a=candidate:')
          .map(function(cand) {
            return SDPUtils.parseCandidate(cand);
          })
          .filter(function(cand) {
            return cand.component === 1;
          });

      // Check if we can use BUNDLE and dispose transports.
      if ((description.type === 'offer' || description.type === 'answer') &&
          !rejected && usingBundle && sdpMLineIndex > 0 &&
          self.transceivers[sdpMLineIndex]) {
        self._disposeIceAndDtlsTransports(sdpMLineIndex);
        self.transceivers[sdpMLineIndex].iceGatherer =
            self.transceivers[0].iceGatherer;
        self.transceivers[sdpMLineIndex].iceTransport =
            self.transceivers[0].iceTransport;
        self.transceivers[sdpMLineIndex].dtlsTransport =
            self.transceivers[0].dtlsTransport;
        if (self.transceivers[sdpMLineIndex].rtpSender) {
          self.transceivers[sdpMLineIndex].rtpSender.setTransport(
              self.transceivers[0].dtlsTransport);
        }
        if (self.transceivers[sdpMLineIndex].rtpReceiver) {
          self.transceivers[sdpMLineIndex].rtpReceiver.setTransport(
              self.transceivers[0].dtlsTransport);
        }
      }
      if (description.type === 'offer' && !rejected) {
        transceiver = self.transceivers[sdpMLineIndex] ||
            self._createTransceiver(kind);
        transceiver.mid = mid;

        if (!transceiver.iceGatherer) {
          transceiver.iceGatherer = self._createIceGatherer(sdpMLineIndex,
              usingBundle);
        }

        if (cands.length && transceiver.iceTransport.state === 'new') {
          if (isComplete && (!usingBundle || sdpMLineIndex === 0)) {
            transceiver.iceTransport.setRemoteCandidates(cands);
          } else {
            cands.forEach(function(candidate) {
              maybeAddCandidate(transceiver.iceTransport, candidate);
            });
          }
        }

        localCapabilities = window.RTCRtpReceiver.getCapabilities(kind);

        // filter RTX until additional stuff needed for RTX is implemented
        // in adapter.js
        if (edgeVersion < 15019) {
          localCapabilities.codecs = localCapabilities.codecs.filter(
              function(codec) {
                return codec.name !== 'rtx';
              });
        }

        sendEncodingParameters = transceiver.sendEncodingParameters || [{
          ssrc: (2 * sdpMLineIndex + 2) * 1001
        }];

        // TODO: rewrite to use http://w3c.github.io/webrtc-pc/#set-associated-remote-streams
        var isNewTrack = false;
        if (direction === 'sendrecv' || direction === 'sendonly') {
          isNewTrack = !transceiver.rtpReceiver;
          rtpReceiver = transceiver.rtpReceiver ||
              new window.RTCRtpReceiver(transceiver.dtlsTransport, kind);

          if (isNewTrack) {
            var stream;
            track = rtpReceiver.track;
            // FIXME: does not work with Plan B.
            if (remoteMsid && remoteMsid.stream === '-') {
              // no-op. a stream id of '-' means: no associated stream.
            } else if (remoteMsid) {
              if (!streams[remoteMsid.stream]) {
                streams[remoteMsid.stream] = new window.MediaStream();
                Object.defineProperty(streams[remoteMsid.stream], 'id', {
                  get: function() {
                    return remoteMsid.stream;
                  }
                });
              }
              Object.defineProperty(track, 'id', {
                get: function() {
                  return remoteMsid.track;
                }
              });
              stream = streams[remoteMsid.stream];
            } else {
              if (!streams.default) {
                streams.default = new window.MediaStream();
              }
              stream = streams.default;
            }
            if (stream) {
              addTrackToStreamAndFireEvent(track, stream);
              transceiver.associatedRemoteMediaStreams.push(stream);
            }
            receiverList.push([track, rtpReceiver, stream]);
          }
        } else if (transceiver.rtpReceiver && transceiver.rtpReceiver.track) {
          transceiver.associatedRemoteMediaStreams.forEach(function(s) {
            var nativeTrack = s.getTracks().find(function(t) {
              return t.id === transceiver.rtpReceiver.track.id;
            });
            if (nativeTrack) {
              removeTrackFromStreamAndFireEvent(nativeTrack, s);
            }
          });
          transceiver.associatedRemoteMediaStreams = [];
        }

        transceiver.localCapabilities = localCapabilities;
        transceiver.remoteCapabilities = remoteCapabilities;
        transceiver.rtpReceiver = rtpReceiver;
        transceiver.rtcpParameters = rtcpParameters;
        transceiver.sendEncodingParameters = sendEncodingParameters;
        transceiver.recvEncodingParameters = recvEncodingParameters;

        // Start the RTCRtpReceiver now. The RTPSender is started in
        // setLocalDescription.
        self._transceive(self.transceivers[sdpMLineIndex],
            false,
            isNewTrack);
      } else if (description.type === 'answer' && !rejected) {
        transceiver = self.transceivers[sdpMLineIndex];
        iceGatherer = transceiver.iceGatherer;
        iceTransport = transceiver.iceTransport;
        dtlsTransport = transceiver.dtlsTransport;
        rtpReceiver = transceiver.rtpReceiver;
        sendEncodingParameters = transceiver.sendEncodingParameters;
        localCapabilities = transceiver.localCapabilities;

        self.transceivers[sdpMLineIndex].recvEncodingParameters =
            recvEncodingParameters;
        self.transceivers[sdpMLineIndex].remoteCapabilities =
            remoteCapabilities;
        self.transceivers[sdpMLineIndex].rtcpParameters = rtcpParameters;

        if (cands.length && iceTransport.state === 'new') {
          if ((isIceLite || isComplete) &&
              (!usingBundle || sdpMLineIndex === 0)) {
            iceTransport.setRemoteCandidates(cands);
          } else {
            cands.forEach(function(candidate) {
              maybeAddCandidate(transceiver.iceTransport, candidate);
            });
          }
        }

        if (!usingBundle || sdpMLineIndex === 0) {
          if (iceTransport.state === 'new') {
            iceTransport.start(iceGatherer, remoteIceParameters,
                'controlling');
          }
          if (dtlsTransport.state === 'new') {
            dtlsTransport.start(remoteDtlsParameters);
          }
        }

        self._transceive(transceiver,
            direction === 'sendrecv' || direction === 'recvonly',
            direction === 'sendrecv' || direction === 'sendonly');

        // TODO: rewrite to use http://w3c.github.io/webrtc-pc/#set-associated-remote-streams
        if (rtpReceiver &&
            (direction === 'sendrecv' || direction === 'sendonly')) {
          track = rtpReceiver.track;
          if (remoteMsid) {
            if (!streams[remoteMsid.stream]) {
              streams[remoteMsid.stream] = new window.MediaStream();
            }
            addTrackToStreamAndFireEvent(track, streams[remoteMsid.stream]);
            receiverList.push([track, rtpReceiver, streams[remoteMsid.stream]]);
          } else {
            if (!streams.default) {
              streams.default = new window.MediaStream();
            }
            addTrackToStreamAndFireEvent(track, streams.default);
            receiverList.push([track, rtpReceiver, streams.default]);
          }
        } else {
          // FIXME: actually the receiver should be created later.
          delete transceiver.rtpReceiver;
        }
      }
    });

    if (this._dtlsRole === undefined) {
      this._dtlsRole = description.type === 'offer' ? 'active' : 'passive';
    }

    this.remoteDescription = {
      type: description.type,
      sdp: description.sdp
    };
    switch (description.type) {
      case 'offer':
        this._updateSignalingState('have-remote-offer');
        break;
      case 'answer':
        this._updateSignalingState('stable');
        break;
      default:
        throw new TypeError('unsupported type "' + description.type +
            '"');
    }
    Object.keys(streams).forEach(function(sid) {
      var stream = streams[sid];
      if (stream.getTracks().length) {
        if (self.remoteStreams.indexOf(stream) === -1) {
          self.remoteStreams.push(stream);
          var event = new Event('addstream');
          event.stream = stream;
          window.setTimeout(function() {
            self.dispatchEvent(event);
            if (typeof self.onaddstream === 'function') {
              self.onaddstream(event);
            }
          });
        }

        receiverList.forEach(function(item) {
          var track = item[0];
          var receiver = item[1];
          if (stream.id !== item[2].id) {
            return;
          }
          fireAddTrack(self, track, receiver, [stream]);
        });
      }
    });
    receiverList.forEach(function(item) {
      if (item[2]) {
        return;
      }
      fireAddTrack(self, item[0], item[1], []);
    });

    // check whether addIceCandidate({}) was called within four seconds after
    // setRemoteDescription.
    window.setTimeout(function() {
      if (!(self && self.transceivers)) {
        return;
      }
      self.transceivers.forEach(function(transceiver) {
        if (transceiver.iceTransport &&
            transceiver.iceTransport.state === 'new' &&
            transceiver.iceTransport.getRemoteCandidates().length > 0) {
          console.warn('Timeout for addRemoteCandidate. Consider sending ' +
              'an end-of-candidates notification');
          transceiver.iceTransport.addRemoteCandidate({});
        }
      });
    }, 4000);

    return new Promise(function(resolve) {
      if (args.length > 1 && typeof args[1] === 'function') {
        args[1].apply(null);
      }
      resolve();
    });
  };

  RTCPeerConnection.prototype.close = function() {
    this.transceivers.forEach(function(transceiver) {
      /* not yet
      if (transceiver.iceGatherer) {
        transceiver.iceGatherer.close();
      }
      */
      if (transceiver.iceTransport) {
        transceiver.iceTransport.stop();
      }
      if (transceiver.dtlsTransport) {
        transceiver.dtlsTransport.stop();
      }
      if (transceiver.rtpSender) {
        transceiver.rtpSender.stop();
      }
      if (transceiver.rtpReceiver) {
        transceiver.rtpReceiver.stop();
      }
    });
    // FIXME: clean up tracks, local streams, remote streams, etc
    this._updateSignalingState('closed');
  };

  // Update the signaling state.
  RTCPeerConnection.prototype._updateSignalingState = function(newState) {
    this.signalingState = newState;
    var event = new Event('signalingstatechange');
    this.dispatchEvent(event);
    if (typeof this.onsignalingstatechange === 'function') {
      this.onsignalingstatechange(event);
    }
  };

  // Determine whether to fire the negotiationneeded event.
  RTCPeerConnection.prototype._maybeFireNegotiationNeeded = function() {
    var self = this;
    if (this.signalingState !== 'stable' || this.needNegotiation === true) {
      return;
    }
    this.needNegotiation = true;
    window.setTimeout(function() {
      if (self.needNegotiation === false) {
        return;
      }
      self.needNegotiation = false;
      var event = new Event('negotiationneeded');
      self.dispatchEvent(event);
      if (typeof self.onnegotiationneeded === 'function') {
        self.onnegotiationneeded(event);
      }
    }, 0);
  };

  // Update the connection state.
  RTCPeerConnection.prototype._updateConnectionState = function() {
    var newState;
    var states = {
      'new': 0,
      closed: 0,
      connecting: 0,
      checking: 0,
      connected: 0,
      completed: 0,
      disconnected: 0,
      failed: 0
    };
    this.transceivers.forEach(function(transceiver) {
      states[transceiver.iceTransport.state]++;
      states[transceiver.dtlsTransport.state]++;
    });
    // ICETransport.completed and connected are the same for this purpose.
    states.connected += states.completed;

    newState = 'new';
    if (states.failed > 0) {
      newState = 'failed';
    } else if (states.connecting > 0 || states.checking > 0) {
      newState = 'connecting';
    } else if (states.disconnected > 0) {
      newState = 'disconnected';
    } else if (states.new > 0) {
      newState = 'new';
    } else if (states.connected > 0 || states.completed > 0) {
      newState = 'connected';
    }

    if (newState !== this.iceConnectionState) {
      this.iceConnectionState = newState;
      var event = new Event('iceconnectionstatechange');
      this.dispatchEvent(event);
      if (typeof this.oniceconnectionstatechange === 'function') {
        this.oniceconnectionstatechange(event);
      }
    }
  };

  RTCPeerConnection.prototype.createOffer = function() {
    var self = this;
    var args = arguments;

    var offerOptions;
    if (arguments.length === 1 && typeof arguments[0] !== 'function') {
      offerOptions = arguments[0];
    } else if (arguments.length === 3) {
      offerOptions = arguments[2];
    }

    var numAudioTracks = this.transceivers.filter(function(t) {
      return t.kind === 'audio';
    }).length;
    var numVideoTracks = this.transceivers.filter(function(t) {
      return t.kind === 'video';
    }).length;

    // Determine number of audio and video tracks we need to send/recv.
    if (offerOptions) {
      // Reject Chrome legacy constraints.
      if (offerOptions.mandatory || offerOptions.optional) {
        throw new TypeError(
            'Legacy mandatory/optional constraints not supported.');
      }
      if (offerOptions.offerToReceiveAudio !== undefined) {
        if (offerOptions.offerToReceiveAudio === true) {
          numAudioTracks = 1;
        } else if (offerOptions.offerToReceiveAudio === false) {
          numAudioTracks = 0;
        } else {
          numAudioTracks = offerOptions.offerToReceiveAudio;
        }
      }
      if (offerOptions.offerToReceiveVideo !== undefined) {
        if (offerOptions.offerToReceiveVideo === true) {
          numVideoTracks = 1;
        } else if (offerOptions.offerToReceiveVideo === false) {
          numVideoTracks = 0;
        } else {
          numVideoTracks = offerOptions.offerToReceiveVideo;
        }
      }
    }

    this.transceivers.forEach(function(transceiver) {
      if (transceiver.kind === 'audio') {
        numAudioTracks--;
        if (numAudioTracks < 0) {
          transceiver.wantReceive = false;
        }
      } else if (transceiver.kind === 'video') {
        numVideoTracks--;
        if (numVideoTracks < 0) {
          transceiver.wantReceive = false;
        }
      }
    });

    // Create M-lines for recvonly streams.
    while (numAudioTracks > 0 || numVideoTracks > 0) {
      if (numAudioTracks > 0) {
        this._createTransceiver('audio');
        numAudioTracks--;
      }
      if (numVideoTracks > 0) {
        this._createTransceiver('video');
        numVideoTracks--;
      }
    }

    var sdp = SDPUtils.writeSessionBoilerplate(this._sdpSessionId,
        this._sdpSessionVersion++);
    this.transceivers.forEach(function(transceiver, sdpMLineIndex) {
      // For each track, create an ice gatherer, ice transport,
      // dtls transport, potentially rtpsender and rtpreceiver.
      var track = transceiver.track;
      var kind = transceiver.kind;
      var mid = SDPUtils.generateIdentifier();
      transceiver.mid = mid;

      if (!transceiver.iceGatherer) {
        transceiver.iceGatherer = self._createIceGatherer(sdpMLineIndex,
            self.usingBundle);
      }

      var localCapabilities = window.RTCRtpSender.getCapabilities(kind);
      // filter RTX until additional stuff needed for RTX is implemented
      // in adapter.js
      if (edgeVersion < 15019) {
        localCapabilities.codecs = localCapabilities.codecs.filter(
            function(codec) {
              return codec.name !== 'rtx';
            });
      }
      localCapabilities.codecs.forEach(function(codec) {
        // work around https://bugs.chromium.org/p/webrtc/issues/detail?id=6552
        // by adding level-asymmetry-allowed=1
        if (codec.name === 'H264' &&
            codec.parameters['level-asymmetry-allowed'] === undefined) {
          codec.parameters['level-asymmetry-allowed'] = '1';
        }
      });

      // generate an ssrc now, to be used later in rtpSender.send
      var sendEncodingParameters = transceiver.sendEncodingParameters || [{
        ssrc: (2 * sdpMLineIndex + 1) * 1001
      }];
      if (track) {
        // add RTX
        if (edgeVersion >= 15019 && kind === 'video' &&
            !sendEncodingParameters[0].rtx) {
          sendEncodingParameters[0].rtx = {
            ssrc: sendEncodingParameters[0].ssrc + 1
          };
        }
      }

      if (transceiver.wantReceive) {
        transceiver.rtpReceiver = new window.RTCRtpReceiver(
            transceiver.dtlsTransport, kind);
      }

      transceiver.localCapabilities = localCapabilities;
      transceiver.sendEncodingParameters = sendEncodingParameters;
    });

    // always offer BUNDLE and dispose on return if not supported.
    if (this._config.bundlePolicy !== 'max-compat') {
      sdp += 'a=group:BUNDLE ' + this.transceivers.map(function(t) {
        return t.mid;
      }).join(' ') + '\r\n';
    }
    sdp += 'a=ice-options:trickle\r\n';

    this.transceivers.forEach(function(transceiver, sdpMLineIndex) {
      sdp += writeMediaSection(transceiver, transceiver.localCapabilities,
          'offer', transceiver.stream, self._dtlsRole);
      sdp += 'a=rtcp-rsize\r\n';

      if (transceiver.iceGatherer && self.iceGatheringState !== 'new' &&
          (sdpMLineIndex === 0 || !self.usingBundle)) {
        transceiver.iceGatherer.getLocalCandidates().forEach(function(cand) {
          cand.component = 1;
          sdp += 'a=' + SDPUtils.writeCandidate(cand) + '\r\n';
        });

        if (transceiver.iceGatherer.state === 'completed') {
          sdp += 'a=end-of-candidates\r\n';
        }
      }
    });

    var desc = new window.RTCSessionDescription({
      type: 'offer',
      sdp: sdp
    });
    return new Promise(function(resolve) {
      if (args.length > 0 && typeof args[0] === 'function') {
        args[0].apply(null, [desc]);
        resolve();
        return;
      }
      resolve(desc);
    });
  };

  RTCPeerConnection.prototype.createAnswer = function() {
    var self = this;
    var args = arguments;

    var sdp = SDPUtils.writeSessionBoilerplate(this._sdpSessionId,
        this._sdpSessionVersion++);
    if (this.usingBundle) {
      sdp += 'a=group:BUNDLE ' + this.transceivers.map(function(t) {
        return t.mid;
      }).join(' ') + '\r\n';
    }
    var mediaSectionsInOffer = SDPUtils.splitSections(
        this.remoteDescription.sdp).length - 1;
    this.transceivers.forEach(function(transceiver, sdpMLineIndex) {
      if (sdpMLineIndex + 1 > mediaSectionsInOffer) {
        return;
      }
      if (transceiver.isDatachannel) {
        sdp += 'm=application 0 DTLS/SCTP 5000\r\n' +
            'c=IN IP4 0.0.0.0\r\n' +
            'a=mid:' + transceiver.mid + '\r\n';
        return;
      }

      // FIXME: look at direction.
      if (transceiver.stream) {
        var localTrack;
        if (transceiver.kind === 'audio') {
          localTrack = transceiver.stream.getAudioTracks()[0];
        } else if (transceiver.kind === 'video') {
          localTrack = transceiver.stream.getVideoTracks()[0];
        }
        if (localTrack) {
          // add RTX
          if (edgeVersion >= 15019 && transceiver.kind === 'video' &&
              !transceiver.sendEncodingParameters[0].rtx) {
            transceiver.sendEncodingParameters[0].rtx = {
              ssrc: transceiver.sendEncodingParameters[0].ssrc + 1
            };
          }
        }
      }

      // Calculate intersection of capabilities.
      var commonCapabilities = getCommonCapabilities(
          transceiver.localCapabilities,
          transceiver.remoteCapabilities);

      var hasRtx = commonCapabilities.codecs.filter(function(c) {
        return c.name.toLowerCase() === 'rtx';
      }).length;
      if (!hasRtx && transceiver.sendEncodingParameters[0].rtx) {
        delete transceiver.sendEncodingParameters[0].rtx;
      }

      sdp += writeMediaSection(transceiver, commonCapabilities,
          'answer', transceiver.stream, self._dtlsRole);
      if (transceiver.rtcpParameters &&
          transceiver.rtcpParameters.reducedSize) {
        sdp += 'a=rtcp-rsize\r\n';
      }
    });

    var desc = new window.RTCSessionDescription({
      type: 'answer',
      sdp: sdp
    });
    return new Promise(function(resolve) {
      if (args.length > 0 && typeof args[0] === 'function') {
        args[0].apply(null, [desc]);
        resolve();
        return;
      }
      resolve(desc);
    });
  };

  RTCPeerConnection.prototype.addIceCandidate = function(candidate) {
    var err;
    var sections;
    if (!candidate || candidate.candidate === '') {
      for (var j = 0; j < this.transceivers.length; j++) {
        if (this.transceivers[j].isDatachannel) {
          continue;
        }
        this.transceivers[j].iceTransport.addRemoteCandidate({});
        sections = SDPUtils.splitSections(this.remoteDescription.sdp);
        sections[j + 1] += 'a=end-of-candidates\r\n';
        this.remoteDescription.sdp = sections.join('');
        if (this.usingBundle) {
          break;
        }
      }
    } else if (!(candidate.sdpMLineIndex !== undefined || candidate.sdpMid)) {
      throw new TypeError('sdpMLineIndex or sdpMid required');
    } else if (!this.remoteDescription) {
      err = new Error('Can not add ICE candidate without ' +
          'a remote description');
      err.name = 'InvalidStateError';
    } else {
      var sdpMLineIndex = candidate.sdpMLineIndex;
      if (candidate.sdpMid) {
        for (var i = 0; i < this.transceivers.length; i++) {
          if (this.transceivers[i].mid === candidate.sdpMid) {
            sdpMLineIndex = i;
            break;
          }
        }
      }
      var transceiver = this.transceivers[sdpMLineIndex];
      if (transceiver) {
        if (transceiver.isDatachannel) {
          return Promise.resolve();
        }
        var cand = Object.keys(candidate.candidate).length > 0 ?
            SDPUtils.parseCandidate(candidate.candidate) : {};
        // Ignore Chrome's invalid candidates since Edge does not like them.
        if (cand.protocol === 'tcp' && (cand.port === 0 || cand.port === 9)) {
          return Promise.resolve();
        }
        // Ignore RTCP candidates, we assume RTCP-MUX.
        if (cand.component && cand.component !== 1) {
          return Promise.resolve();
        }
        // when using bundle, avoid adding candidates to the wrong
        // ice transport. And avoid adding candidates added in the SDP.
        if (sdpMLineIndex === 0 || (sdpMLineIndex > 0 &&
            transceiver.iceTransport !== this.transceivers[0].iceTransport)) {
          if (!maybeAddCandidate(transceiver.iceTransport, cand)) {
            err = new Error('Can not add ICE candidate');
            err.name = 'OperationError';
          }
        }

        if (!err) {
          // update the remoteDescription.
          var candidateString = candidate.candidate.trim();
          if (candidateString.indexOf('a=') === 0) {
            candidateString = candidateString.substr(2);
          }
          sections = SDPUtils.splitSections(this.remoteDescription.sdp);
          sections[sdpMLineIndex + 1] += 'a=' +
              (cand.type ? candidateString : 'end-of-candidates')
              + '\r\n';
          this.remoteDescription.sdp = sections.join('');
        }
      } else {
        err = new Error('Can not add ICE candidate');
        err.name = 'OperationError';
      }
    }
    var args = arguments;
    return new Promise(function(resolve, reject) {
      if (err) {
        if (args.length > 2 && typeof args[2] === 'function') {
          args[2].apply(null, [err]);
        }
        reject(err);
      } else {
        if (args.length > 1 && typeof args[1] === 'function') {
          args[1].apply(null);
        }
        resolve();
      }
    });
  };

  RTCPeerConnection.prototype.getStats = function() {
    var promises = [];
    this.transceivers.forEach(function(transceiver) {
      ['rtpSender', 'rtpReceiver', 'iceGatherer', 'iceTransport',
          'dtlsTransport'].forEach(function(method) {
            if (transceiver[method]) {
              promises.push(transceiver[method].getStats());
            }
          });
    });
    var cb = arguments.length > 1 && typeof arguments[1] === 'function' &&
        arguments[1];
    var fixStatsType = function(stat) {
      return {
        inboundrtp: 'inbound-rtp',
        outboundrtp: 'outbound-rtp',
        candidatepair: 'candidate-pair',
        localcandidate: 'local-candidate',
        remotecandidate: 'remote-candidate'
      }[stat.type] || stat.type;
    };
    return new Promise(function(resolve) {
      // shim getStats with maplike support
      var results = new Map();
      Promise.all(promises).then(function(res) {
        res.forEach(function(result) {
          Object.keys(result).forEach(function(id) {
            result[id].type = fixStatsType(result[id]);
            results.set(id, result[id]);
          });
        });
        if (cb) {
          cb.apply(null, results);
        }
        resolve(results);
      });
    });
  };
  return RTCPeerConnection;
};


/***/ }),
/* 53 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/*
 *  Copyright (c) 2016 The WebRTC project authors. All Rights Reserved.
 *
 *  Use of this source code is governed by a BSD-style license
 *  that can be found in the LICENSE file in the root of the source
 *  tree.
 */
 /* eslint-env node */


// Expose public methods.
module.exports = function(window) {
  var navigator = window && window.navigator;

  var shimError_ = function(e) {
    return {
      name: {PermissionDeniedError: 'NotAllowedError'}[e.name] || e.name,
      message: e.message,
      constraint: e.constraint,
      toString: function() {
        return this.name;
      }
    };
  };

  // getUserMedia error shim.
  var origGetUserMedia = navigator.mediaDevices.getUserMedia.
      bind(navigator.mediaDevices);
  navigator.mediaDevices.getUserMedia = function(c) {
    return origGetUserMedia(c).catch(function(e) {
      return Promise.reject(shimError_(e));
    });
  };
};


/***/ }),
/* 54 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/*
 *  Copyright (c) 2016 The WebRTC project authors. All Rights Reserved.
 *
 *  Use of this source code is governed by a BSD-style license
 *  that can be found in the LICENSE file in the root of the source
 *  tree.
 */
 /* eslint-env node */


var utils = __webpack_require__(7);

var firefoxShim = {
  shimOnTrack: function(window) {
    if (typeof window === 'object' && window.RTCPeerConnection && !('ontrack' in
        window.RTCPeerConnection.prototype)) {
      Object.defineProperty(window.RTCPeerConnection.prototype, 'ontrack', {
        get: function() {
          return this._ontrack;
        },
        set: function(f) {
          if (this._ontrack) {
            this.removeEventListener('track', this._ontrack);
            this.removeEventListener('addstream', this._ontrackpoly);
          }
          this.addEventListener('track', this._ontrack = f);
          this.addEventListener('addstream', this._ontrackpoly = function(e) {
            e.stream.getTracks().forEach(function(track) {
              var event = new Event('track');
              event.track = track;
              event.receiver = {track: track};
              event.transceiver = {receiver: event.receiver};
              event.streams = [e.stream];
              this.dispatchEvent(event);
            }.bind(this));
          }.bind(this));
        }
      });
    }
    if (typeof window === 'object' && window.RTCTrackEvent &&
        ('receiver' in window.RTCTrackEvent.prototype) &&
        !('transceiver' in window.RTCTrackEvent.prototype)) {
      Object.defineProperty(window.RTCTrackEvent.prototype, 'transceiver', {
        get: function() {
          return {receiver: this.receiver};
        }
      });
    }
  },

  shimSourceObject: function(window) {
    // Firefox has supported mozSrcObject since FF22, unprefixed in 42.
    if (typeof window === 'object') {
      if (window.HTMLMediaElement &&
        !('srcObject' in window.HTMLMediaElement.prototype)) {
        // Shim the srcObject property, once, when HTMLMediaElement is found.
        Object.defineProperty(window.HTMLMediaElement.prototype, 'srcObject', {
          get: function() {
            return this.mozSrcObject;
          },
          set: function(stream) {
            this.mozSrcObject = stream;
          }
        });
      }
    }
  },

  shimPeerConnection: function(window) {
    var browserDetails = utils.detectBrowser(window);

    if (typeof window !== 'object' || !(window.RTCPeerConnection ||
        window.mozRTCPeerConnection)) {
      return; // probably media.peerconnection.enabled=false in about:config
    }
    // The RTCPeerConnection object.
    if (!window.RTCPeerConnection) {
      window.RTCPeerConnection = function(pcConfig, pcConstraints) {
        if (browserDetails.version < 38) {
          // .urls is not supported in FF < 38.
          // create RTCIceServers with a single url.
          if (pcConfig && pcConfig.iceServers) {
            var newIceServers = [];
            for (var i = 0; i < pcConfig.iceServers.length; i++) {
              var server = pcConfig.iceServers[i];
              if (server.hasOwnProperty('urls')) {
                for (var j = 0; j < server.urls.length; j++) {
                  var newServer = {
                    url: server.urls[j]
                  };
                  if (server.urls[j].indexOf('turn') === 0) {
                    newServer.username = server.username;
                    newServer.credential = server.credential;
                  }
                  newIceServers.push(newServer);
                }
              } else {
                newIceServers.push(pcConfig.iceServers[i]);
              }
            }
            pcConfig.iceServers = newIceServers;
          }
        }
        return new window.mozRTCPeerConnection(pcConfig, pcConstraints);
      };
      window.RTCPeerConnection.prototype =
          window.mozRTCPeerConnection.prototype;

      // wrap static methods. Currently just generateCertificate.
      if (window.mozRTCPeerConnection.generateCertificate) {
        Object.defineProperty(window.RTCPeerConnection, 'generateCertificate', {
          get: function() {
            return window.mozRTCPeerConnection.generateCertificate;
          }
        });
      }

      window.RTCSessionDescription = window.mozRTCSessionDescription;
      window.RTCIceCandidate = window.mozRTCIceCandidate;
    }

    // shim away need for obsolete RTCIceCandidate/RTCSessionDescription.
    ['setLocalDescription', 'setRemoteDescription', 'addIceCandidate']
        .forEach(function(method) {
          var nativeMethod = window.RTCPeerConnection.prototype[method];
          window.RTCPeerConnection.prototype[method] = function() {
            arguments[0] = new ((method === 'addIceCandidate') ?
                window.RTCIceCandidate :
                window.RTCSessionDescription)(arguments[0]);
            return nativeMethod.apply(this, arguments);
          };
        });

    // support for addIceCandidate(null or undefined)
    var nativeAddIceCandidate =
        window.RTCPeerConnection.prototype.addIceCandidate;
    window.RTCPeerConnection.prototype.addIceCandidate = function() {
      if (!arguments[0]) {
        if (arguments[1]) {
          arguments[1].apply(null);
        }
        return Promise.resolve();
      }
      return nativeAddIceCandidate.apply(this, arguments);
    };

    // shim getStats with maplike support
    var makeMapStats = function(stats) {
      var map = new Map();
      Object.keys(stats).forEach(function(key) {
        map.set(key, stats[key]);
        map[key] = stats[key];
      });
      return map;
    };

    var modernStatsTypes = {
      inboundrtp: 'inbound-rtp',
      outboundrtp: 'outbound-rtp',
      candidatepair: 'candidate-pair',
      localcandidate: 'local-candidate',
      remotecandidate: 'remote-candidate'
    };

    var nativeGetStats = window.RTCPeerConnection.prototype.getStats;
    window.RTCPeerConnection.prototype.getStats = function(
      selector,
      onSucc,
      onErr
    ) {
      return nativeGetStats.apply(this, [selector || null])
        .then(function(stats) {
          if (browserDetails.version < 48) {
            stats = makeMapStats(stats);
          }
          if (browserDetails.version < 53 && !onSucc) {
            // Shim only promise getStats with spec-hyphens in type names
            // Leave callback version alone; misc old uses of forEach before Map
            try {
              stats.forEach(function(stat) {
                stat.type = modernStatsTypes[stat.type] || stat.type;
              });
            } catch (e) {
              if (e.name !== 'TypeError') {
                throw e;
              }
              // Avoid TypeError: "type" is read-only, in old versions. 34-43ish
              stats.forEach(function(stat, i) {
                stats.set(i, Object.assign({}, stat, {
                  type: modernStatsTypes[stat.type] || stat.type
                }));
              });
            }
          }
          return stats;
        })
        .then(onSucc, onErr);
    };
  },

  shimRemoveStream: function(window) {
    if ('removeStream' in window.RTCPeerConnection.prototype) {
      return;
    }
    window.RTCPeerConnection.prototype.removeStream = function(stream) {
      var pc = this;
      utils.deprecated('removeStream', 'removeTrack');
      this.getSenders().forEach(function(sender) {
        if (sender.track && stream.getTracks().indexOf(sender.track) !== -1) {
          pc.removeTrack(sender);
        }
      });
    };
  }
};

// Expose public methods.
module.exports = {
  shimOnTrack: firefoxShim.shimOnTrack,
  shimSourceObject: firefoxShim.shimSourceObject,
  shimPeerConnection: firefoxShim.shimPeerConnection,
  shimRemoveStream: firefoxShim.shimRemoveStream,
  shimGetUserMedia: __webpack_require__(55)
};


/***/ }),
/* 55 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/*
 *  Copyright (c) 2016 The WebRTC project authors. All Rights Reserved.
 *
 *  Use of this source code is governed by a BSD-style license
 *  that can be found in the LICENSE file in the root of the source
 *  tree.
 */
 /* eslint-env node */


var utils = __webpack_require__(7);
var logging = utils.log;

// Expose public methods.
module.exports = function(window) {
  var browserDetails = utils.detectBrowser(window);
  var navigator = window && window.navigator;
  var MediaStreamTrack = window && window.MediaStreamTrack;

  var shimError_ = function(e) {
    return {
      name: {
        InternalError: 'NotReadableError',
        NotSupportedError: 'TypeError',
        PermissionDeniedError: 'NotAllowedError',
        SecurityError: 'NotAllowedError'
      }[e.name] || e.name,
      message: {
        'The operation is insecure.': 'The request is not allowed by the ' +
        'user agent or the platform in the current context.'
      }[e.message] || e.message,
      constraint: e.constraint,
      toString: function() {
        return this.name + (this.message && ': ') + this.message;
      }
    };
  };

  // getUserMedia constraints shim.
  var getUserMedia_ = function(constraints, onSuccess, onError) {
    var constraintsToFF37_ = function(c) {
      if (typeof c !== 'object' || c.require) {
        return c;
      }
      var require = [];
      Object.keys(c).forEach(function(key) {
        if (key === 'require' || key === 'advanced' || key === 'mediaSource') {
          return;
        }
        var r = c[key] = (typeof c[key] === 'object') ?
            c[key] : {ideal: c[key]};
        if (r.min !== undefined ||
            r.max !== undefined || r.exact !== undefined) {
          require.push(key);
        }
        if (r.exact !== undefined) {
          if (typeof r.exact === 'number') {
            r. min = r.max = r.exact;
          } else {
            c[key] = r.exact;
          }
          delete r.exact;
        }
        if (r.ideal !== undefined) {
          c.advanced = c.advanced || [];
          var oc = {};
          if (typeof r.ideal === 'number') {
            oc[key] = {min: r.ideal, max: r.ideal};
          } else {
            oc[key] = r.ideal;
          }
          c.advanced.push(oc);
          delete r.ideal;
          if (!Object.keys(r).length) {
            delete c[key];
          }
        }
      });
      if (require.length) {
        c.require = require;
      }
      return c;
    };
    constraints = JSON.parse(JSON.stringify(constraints));
    if (browserDetails.version < 38) {
      logging('spec: ' + JSON.stringify(constraints));
      if (constraints.audio) {
        constraints.audio = constraintsToFF37_(constraints.audio);
      }
      if (constraints.video) {
        constraints.video = constraintsToFF37_(constraints.video);
      }
      logging('ff37: ' + JSON.stringify(constraints));
    }
    return navigator.mozGetUserMedia(constraints, onSuccess, function(e) {
      onError(shimError_(e));
    });
  };

  // Returns the result of getUserMedia as a Promise.
  var getUserMediaPromise_ = function(constraints) {
    return new Promise(function(resolve, reject) {
      getUserMedia_(constraints, resolve, reject);
    });
  };

  // Shim for mediaDevices on older versions.
  if (!navigator.mediaDevices) {
    navigator.mediaDevices = {getUserMedia: getUserMediaPromise_,
      addEventListener: function() { },
      removeEventListener: function() { }
    };
  }
  navigator.mediaDevices.enumerateDevices =
      navigator.mediaDevices.enumerateDevices || function() {
        return new Promise(function(resolve) {
          var infos = [
            {kind: 'audioinput', deviceId: 'default', label: '', groupId: ''},
            {kind: 'videoinput', deviceId: 'default', label: '', groupId: ''}
          ];
          resolve(infos);
        });
      };

  if (browserDetails.version < 41) {
    // Work around http://bugzil.la/1169665
    var orgEnumerateDevices =
        navigator.mediaDevices.enumerateDevices.bind(navigator.mediaDevices);
    navigator.mediaDevices.enumerateDevices = function() {
      return orgEnumerateDevices().then(undefined, function(e) {
        if (e.name === 'NotFoundError') {
          return [];
        }
        throw e;
      });
    };
  }
  if (browserDetails.version < 49) {
    var origGetUserMedia = navigator.mediaDevices.getUserMedia.
        bind(navigator.mediaDevices);
    navigator.mediaDevices.getUserMedia = function(c) {
      return origGetUserMedia(c).then(function(stream) {
        // Work around https://bugzil.la/802326
        if (c.audio && !stream.getAudioTracks().length ||
            c.video && !stream.getVideoTracks().length) {
          stream.getTracks().forEach(function(track) {
            track.stop();
          });
          throw new DOMException('The object can not be found here.',
                                 'NotFoundError');
        }
        return stream;
      }, function(e) {
        return Promise.reject(shimError_(e));
      });
    };
  }
  if (!(browserDetails.version > 55 &&
      'autoGainControl' in navigator.mediaDevices.getSupportedConstraints())) {
    var remap = function(obj, a, b) {
      if (a in obj && !(b in obj)) {
        obj[b] = obj[a];
        delete obj[a];
      }
    };

    var nativeGetUserMedia = navigator.mediaDevices.getUserMedia.
        bind(navigator.mediaDevices);
    navigator.mediaDevices.getUserMedia = function(c) {
      if (typeof c === 'object' && typeof c.audio === 'object') {
        c = JSON.parse(JSON.stringify(c));
        remap(c.audio, 'autoGainControl', 'mozAutoGainControl');
        remap(c.audio, 'noiseSuppression', 'mozNoiseSuppression');
      }
      return nativeGetUserMedia(c);
    };

    if (MediaStreamTrack && MediaStreamTrack.prototype.getSettings) {
      var nativeGetSettings = MediaStreamTrack.prototype.getSettings;
      MediaStreamTrack.prototype.getSettings = function() {
        var obj = nativeGetSettings.apply(this, arguments);
        remap(obj, 'mozAutoGainControl', 'autoGainControl');
        remap(obj, 'mozNoiseSuppression', 'noiseSuppression');
        return obj;
      };
    }

    if (MediaStreamTrack && MediaStreamTrack.prototype.applyConstraints) {
      var nativeApplyConstraints = MediaStreamTrack.prototype.applyConstraints;
      MediaStreamTrack.prototype.applyConstraints = function(c) {
        if (this.kind === 'audio' && typeof c === 'object') {
          c = JSON.parse(JSON.stringify(c));
          remap(c, 'autoGainControl', 'mozAutoGainControl');
          remap(c, 'noiseSuppression', 'mozNoiseSuppression');
        }
        return nativeApplyConstraints.apply(this, [c]);
      };
    }
  }
  navigator.getUserMedia = function(constraints, onSuccess, onError) {
    if (browserDetails.version < 44) {
      return getUserMedia_(constraints, onSuccess, onError);
    }
    // Replace Firefox 44+'s deprecation warning with unprefixed version.
    utils.deprecated('navigator.getUserMedia',
        'navigator.mediaDevices.getUserMedia');
    navigator.mediaDevices.getUserMedia(constraints).then(onSuccess, onError);
  };
};


/***/ }),
/* 56 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/*
 *  Copyright (c) 2016 The WebRTC project authors. All Rights Reserved.
 *
 *  Use of this source code is governed by a BSD-style license
 *  that can be found in the LICENSE file in the root of the source
 *  tree.
 */

var utils = __webpack_require__(7);

var safariShim = {
  // TODO: DrAlex, should be here, double check against LayoutTests

  // TODO: once the back-end for the mac port is done, add.
  // TODO: check for webkitGTK+
  // shimPeerConnection: function() { },

  shimLocalStreamsAPI: function(window) {
    if (typeof window !== 'object' || !window.RTCPeerConnection) {
      return;
    }
    if (!('getLocalStreams' in window.RTCPeerConnection.prototype)) {
      window.RTCPeerConnection.prototype.getLocalStreams = function() {
        if (!this._localStreams) {
          this._localStreams = [];
        }
        return this._localStreams;
      };
    }
    if (!('getStreamById' in window.RTCPeerConnection.prototype)) {
      window.RTCPeerConnection.prototype.getStreamById = function(id) {
        var result = null;
        if (this._localStreams) {
          this._localStreams.forEach(function(stream) {
            if (stream.id === id) {
              result = stream;
            }
          });
        }
        if (this._remoteStreams) {
          this._remoteStreams.forEach(function(stream) {
            if (stream.id === id) {
              result = stream;
            }
          });
        }
        return result;
      };
    }
    if (!('addStream' in window.RTCPeerConnection.prototype)) {
      var _addTrack = window.RTCPeerConnection.prototype.addTrack;
      window.RTCPeerConnection.prototype.addStream = function(stream) {
        if (!this._localStreams) {
          this._localStreams = [];
        }
        if (this._localStreams.indexOf(stream) === -1) {
          this._localStreams.push(stream);
        }
        var self = this;
        stream.getTracks().forEach(function(track) {
          _addTrack.call(self, track, stream);
        });
      };

      window.RTCPeerConnection.prototype.addTrack = function(track, stream) {
        if (stream) {
          if (!this._localStreams) {
            this._localStreams = [stream];
          } else if (this._localStreams.indexOf(stream) === -1) {
            this._localStreams.push(stream);
          }
        }
        _addTrack.call(this, track, stream);
      };
    }
    if (!('removeStream' in window.RTCPeerConnection.prototype)) {
      window.RTCPeerConnection.prototype.removeStream = function(stream) {
        if (!this._localStreams) {
          this._localStreams = [];
        }
        var index = this._localStreams.indexOf(stream);
        if (index === -1) {
          return;
        }
        this._localStreams.splice(index, 1);
        var self = this;
        var tracks = stream.getTracks();
        this.getSenders().forEach(function(sender) {
          if (tracks.indexOf(sender.track) !== -1) {
            self.removeTrack(sender);
          }
        });
      };
    }
  },
  shimRemoteStreamsAPI: function(window) {
    if (typeof window !== 'object' || !window.RTCPeerConnection) {
      return;
    }
    if (!('getRemoteStreams' in window.RTCPeerConnection.prototype)) {
      window.RTCPeerConnection.prototype.getRemoteStreams = function() {
        return this._remoteStreams ? this._remoteStreams : [];
      };
    }
    if (!('onaddstream' in window.RTCPeerConnection.prototype)) {
      Object.defineProperty(window.RTCPeerConnection.prototype, 'onaddstream', {
        get: function() {
          return this._onaddstream;
        },
        set: function(f) {
          if (this._onaddstream) {
            this.removeEventListener('addstream', this._onaddstream);
            this.removeEventListener('track', this._onaddstreampoly);
          }
          this.addEventListener('addstream', this._onaddstream = f);
          this.addEventListener('track', this._onaddstreampoly = function(e) {
            var stream = e.streams[0];
            if (!this._remoteStreams) {
              this._remoteStreams = [];
            }
            if (this._remoteStreams.indexOf(stream) >= 0) {
              return;
            }
            this._remoteStreams.push(stream);
            var event = new Event('addstream');
            event.stream = e.streams[0];
            this.dispatchEvent(event);
          }.bind(this));
        }
      });
    }
  },
  shimCallbacksAPI: function(window) {
    if (typeof window !== 'object' || !window.RTCPeerConnection) {
      return;
    }
    var prototype = window.RTCPeerConnection.prototype;
    var createOffer = prototype.createOffer;
    var createAnswer = prototype.createAnswer;
    var setLocalDescription = prototype.setLocalDescription;
    var setRemoteDescription = prototype.setRemoteDescription;
    var addIceCandidate = prototype.addIceCandidate;

    prototype.createOffer = function(successCallback, failureCallback) {
      var options = (arguments.length >= 2) ? arguments[2] : arguments[0];
      var promise = createOffer.apply(this, [options]);
      if (!failureCallback) {
        return promise;
      }
      promise.then(successCallback, failureCallback);
      return Promise.resolve();
    };

    prototype.createAnswer = function(successCallback, failureCallback) {
      var options = (arguments.length >= 2) ? arguments[2] : arguments[0];
      var promise = createAnswer.apply(this, [options]);
      if (!failureCallback) {
        return promise;
      }
      promise.then(successCallback, failureCallback);
      return Promise.resolve();
    };

    var withCallback = function(description, successCallback, failureCallback) {
      var promise = setLocalDescription.apply(this, [description]);
      if (!failureCallback) {
        return promise;
      }
      promise.then(successCallback, failureCallback);
      return Promise.resolve();
    };
    prototype.setLocalDescription = withCallback;

    withCallback = function(description, successCallback, failureCallback) {
      var promise = setRemoteDescription.apply(this, [description]);
      if (!failureCallback) {
        return promise;
      }
      promise.then(successCallback, failureCallback);
      return Promise.resolve();
    };
    prototype.setRemoteDescription = withCallback;

    withCallback = function(candidate, successCallback, failureCallback) {
      var promise = addIceCandidate.apply(this, [candidate]);
      if (!failureCallback) {
        return promise;
      }
      promise.then(successCallback, failureCallback);
      return Promise.resolve();
    };
    prototype.addIceCandidate = withCallback;
  },
  shimGetUserMedia: function(window) {
    var navigator = window && window.navigator;

    if (!navigator.getUserMedia) {
      if (navigator.webkitGetUserMedia) {
        navigator.getUserMedia = navigator.webkitGetUserMedia.bind(navigator);
      } else if (navigator.mediaDevices &&
          navigator.mediaDevices.getUserMedia) {
        navigator.getUserMedia = function(constraints, cb, errcb) {
          navigator.mediaDevices.getUserMedia(constraints)
          .then(cb, errcb);
        }.bind(navigator);
      }
    }
  },
  shimRTCIceServerUrls: function(window) {
    // migrate from non-spec RTCIceServer.url to RTCIceServer.urls
    var OrigPeerConnection = window.RTCPeerConnection;
    window.RTCPeerConnection = function(pcConfig, pcConstraints) {
      if (pcConfig && pcConfig.iceServers) {
        var newIceServers = [];
        for (var i = 0; i < pcConfig.iceServers.length; i++) {
          var server = pcConfig.iceServers[i];
          if (!server.hasOwnProperty('urls') &&
              server.hasOwnProperty('url')) {
            utils.deprecated('RTCIceServer.url', 'RTCIceServer.urls');
            server = JSON.parse(JSON.stringify(server));
            server.urls = server.url;
            delete server.url;
            newIceServers.push(server);
          } else {
            newIceServers.push(pcConfig.iceServers[i]);
          }
        }
        pcConfig.iceServers = newIceServers;
      }
      return new OrigPeerConnection(pcConfig, pcConstraints);
    };
    window.RTCPeerConnection.prototype = OrigPeerConnection.prototype;
    // wrap static methods. Currently just generateCertificate.
    if ('generateCertificate' in window.RTCPeerConnection) {
      Object.defineProperty(window.RTCPeerConnection, 'generateCertificate', {
        get: function() {
          return OrigPeerConnection.generateCertificate;
        }
      });
    }
  },
  shimTrackEventTransceiver: function(window) {
    // Add event.transceiver member over deprecated event.receiver
    if (typeof window === 'object' && window.RTCPeerConnection &&
        ('receiver' in window.RTCTrackEvent.prototype) &&
        // can't check 'transceiver' in window.RTCTrackEvent.prototype, as it is
        // defined for some reason even when window.RTCTransceiver is not.
        !window.RTCTransceiver) {
      Object.defineProperty(window.RTCTrackEvent.prototype, 'transceiver', {
        get: function() {
          return {receiver: this.receiver};
        }
      });
    }
  },

  shimCreateOfferLegacy: function(window) {
    var origCreateOffer = window.RTCPeerConnection.prototype.createOffer;
    window.RTCPeerConnection.prototype.createOffer = function(offerOptions) {
      var pc = this;
      if (offerOptions) {
        var audioTransceiver = pc.getTransceivers().find(function(transceiver) {
          return transceiver.sender.track &&
              transceiver.sender.track.kind === 'audio';
        });
        if (offerOptions.offerToReceiveAudio === false && audioTransceiver) {
          if (audioTransceiver.direction === 'sendrecv') {
            audioTransceiver.setDirection('sendonly');
          } else if (audioTransceiver.direction === 'recvonly') {
            audioTransceiver.setDirection('inactive');
          }
        } else if (offerOptions.offerToReceiveAudio === true &&
            !audioTransceiver) {
          pc.addTransceiver('audio');
        }

        var videoTransceiver = pc.getTransceivers().find(function(transceiver) {
          return transceiver.sender.track &&
              transceiver.sender.track.kind === 'video';
        });
        if (offerOptions.offerToReceiveVideo === false && videoTransceiver) {
          if (videoTransceiver.direction === 'sendrecv') {
            videoTransceiver.setDirection('sendonly');
          } else if (videoTransceiver.direction === 'recvonly') {
            videoTransceiver.setDirection('inactive');
          }
        } else if (offerOptions.offerToReceiveVideo === true &&
            !videoTransceiver) {
          pc.addTransceiver('video');
        }
      }
      return origCreateOffer.apply(pc, arguments);
    };
  }
};

// Expose public methods.
module.exports = {
  shimCallbacksAPI: safariShim.shimCallbacksAPI,
  shimLocalStreamsAPI: safariShim.shimLocalStreamsAPI,
  shimRemoteStreamsAPI: safariShim.shimRemoteStreamsAPI,
  shimGetUserMedia: safariShim.shimGetUserMedia,
  shimRTCIceServerUrls: safariShim.shimRTCIceServerUrls,
  shimTrackEventTransceiver: safariShim.shimTrackEventTransceiver,
  shimCreateOfferLegacy: safariShim.shimCreateOfferLegacy
  // TODO
  // shimPeerConnection: safariShim.shimPeerConnection
};


/***/ }),
/* 57 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/*
 *  Copyright (c) 2017 The WebRTC project authors. All Rights Reserved.
 *
 *  Use of this source code is governed by a BSD-style license
 *  that can be found in the LICENSE file in the root of the source
 *  tree.
 */
 /* eslint-env node */


var SDPUtils = __webpack_require__(28);
var utils = __webpack_require__(7);

// Wraps the peerconnection event eventNameToWrap in a function
// which returns the modified event object.
function wrapPeerConnectionEvent(window, eventNameToWrap, wrapper) {
  if (!window.RTCPeerConnection) {
    return;
  }
  var proto = window.RTCPeerConnection.prototype;
  var nativeAddEventListener = proto.addEventListener;
  proto.addEventListener = function(nativeEventName, cb) {
    if (nativeEventName !== eventNameToWrap) {
      return nativeAddEventListener.apply(this, arguments);
    }
    var wrappedCallback = function(e) {
      cb(wrapper(e));
    };
    this._eventMap = this._eventMap || {};
    this._eventMap[cb] = wrappedCallback;
    return nativeAddEventListener.apply(this, [nativeEventName,
      wrappedCallback]);
  };

  var nativeRemoveEventListener = proto.removeEventListener;
  proto.removeEventListener = function(nativeEventName, cb) {
    if (nativeEventName !== eventNameToWrap || !this._eventMap
        || !this._eventMap[cb]) {
      return nativeRemoveEventListener.apply(this, arguments);
    }
    var unwrappedCb = this._eventMap[cb];
    delete this._eventMap[cb];
    return nativeRemoveEventListener.apply(this, [nativeEventName,
      unwrappedCb]);
  };

  Object.defineProperty(proto, 'on' + eventNameToWrap, {
    get: function() {
      return this['_on' + eventNameToWrap];
    },
    set: function(cb) {
      if (this['_on' + eventNameToWrap]) {
        this.removeEventListener(eventNameToWrap,
            this['_on' + eventNameToWrap]);
        delete this['_on' + eventNameToWrap];
      }
      if (cb) {
        this.addEventListener(eventNameToWrap,
            this['_on' + eventNameToWrap] = cb);
      }
    }
  });
}

module.exports = {
  shimRTCIceCandidate: function(window) {
    // foundation is arbitrarily chosen as an indicator for full support for
    // https://w3c.github.io/webrtc-pc/#rtcicecandidate-interface
    if (window.RTCIceCandidate && 'foundation' in
        window.RTCIceCandidate.prototype) {
      return;
    }

    var NativeRTCIceCandidate = window.RTCIceCandidate;
    window.RTCIceCandidate = function(args) {
      // Remove the a= which shouldn't be part of the candidate string.
      if (typeof args === 'object' && args.candidate &&
          args.candidate.indexOf('a=') === 0) {
        args = JSON.parse(JSON.stringify(args));
        args.candidate = args.candidate.substr(2);
      }

      // Augment the native candidate with the parsed fields.
      var nativeCandidate = new NativeRTCIceCandidate(args);
      var parsedCandidate = SDPUtils.parseCandidate(args.candidate);
      var augmentedCandidate = Object.assign(nativeCandidate,
          parsedCandidate);

      // Add a serializer that does not serialize the extra attributes.
      augmentedCandidate.toJSON = function() {
        return {
          candidate: augmentedCandidate.candidate,
          sdpMid: augmentedCandidate.sdpMid,
          sdpMLineIndex: augmentedCandidate.sdpMLineIndex,
          usernameFragment: augmentedCandidate.usernameFragment,
        };
      };
      return augmentedCandidate;
    };

    // Hook up the augmented candidate in onicecandidate and
    // addEventListener('icecandidate', ...)
    wrapPeerConnectionEvent(window, 'icecandidate', function(e) {
      if (e.candidate) {
        Object.defineProperty(e, 'candidate', {
          value: new window.RTCIceCandidate(e.candidate),
          writable: 'false'
        });
      }
      return e;
    });
  },

  // shimCreateObjectURL must be called before shimSourceObject to avoid loop.

  shimCreateObjectURL: function(window) {
    var URL = window && window.URL;

    if (!(typeof window === 'object' && window.HTMLMediaElement &&
          'srcObject' in window.HTMLMediaElement.prototype &&
        URL.createObjectURL && URL.revokeObjectURL)) {
      // Only shim CreateObjectURL using srcObject if srcObject exists.
      return undefined;
    }

    var nativeCreateObjectURL = URL.createObjectURL.bind(URL);
    var nativeRevokeObjectURL = URL.revokeObjectURL.bind(URL);
    var streams = new Map(), newId = 0;

    URL.createObjectURL = function(stream) {
      if ('getTracks' in stream) {
        var url = 'polyblob:' + (++newId);
        streams.set(url, stream);
        utils.deprecated('URL.createObjectURL(stream)',
            'elem.srcObject = stream');
        return url;
      }
      return nativeCreateObjectURL(stream);
    };
    URL.revokeObjectURL = function(url) {
      nativeRevokeObjectURL(url);
      streams.delete(url);
    };

    var dsc = Object.getOwnPropertyDescriptor(window.HTMLMediaElement.prototype,
                                              'src');
    Object.defineProperty(window.HTMLMediaElement.prototype, 'src', {
      get: function() {
        return dsc.get.apply(this);
      },
      set: function(url) {
        this.srcObject = streams.get(url) || null;
        return dsc.set.apply(this, [url]);
      }
    });

    var nativeSetAttribute = window.HTMLMediaElement.prototype.setAttribute;
    window.HTMLMediaElement.prototype.setAttribute = function() {
      if (arguments.length === 2 &&
          ('' + arguments[0]).toLowerCase() === 'src') {
        this.srcObject = streams.get(arguments[1]) || null;
      }
      return nativeSetAttribute.apply(this, arguments);
    };
  }
};


/***/ }),
/* 58 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _get = function get(object, property, receiver) { if (object === null) object = Function.prototype; var desc = Object.getOwnPropertyDescriptor(object, property); if (desc === undefined) { var parent = Object.getPrototypeOf(object); if (parent === null) { return undefined; } else { return get(parent, property, receiver); } } else if ("value" in desc) { return desc.value; } else { var getter = desc.get; if (getter === undefined) { return undefined; } return getter.call(receiver); } };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

Object.defineProperty(exports, "__esModule", { value: true });
var EventDispatcher_1 = __webpack_require__(15);
/**
 * @hidden
 */

var EndPoint = function (_EventDispatcher_1$Ev) {
  _inherits(EndPoint, _EventDispatcher_1$Ev);

  function EndPoint(proto) {
    _classCallCheck(this, EndPoint);

    return _possibleConstructorReturn(this, (EndPoint.__proto__ || Object.getPrototypeOf(EndPoint)).call(this));
  }
  /**
   *
   * @preferred
   * @param {EndPointEvents} event
   * @param {Function} handler
   */


  _createClass(EndPoint, [{
    key: "on",
    value: function on(event, handler) {
      _get(EndPoint.prototype.__proto__ || Object.getPrototypeOf(EndPoint.prototype), "on", this).call(this, event, handler);
    }
    /**
     *
     * @preferred
     * @param {EndPointEvents} event
     * @param {Function} handler
     */

  }, {
    key: "off",
    value: function off(event, handler) {
      _get(EndPoint.prototype.__proto__ || Object.getPrototypeOf(EndPoint.prototype), "off", this).call(this, event, handler);
    }
    /**
     * @hidden
     * @return {string}
     * @private
     */

  }, {
    key: "_traceName",
    value: function _traceName() {
      return 'EndPoint';
    }
  }]);

  return EndPoint;
}(EventDispatcher_1.EventDispatcher);

exports.EndPoint = EndPoint;

/***/ }),
/* 59 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", { value: true });
/**
 * Enumeration of ACD statuses, use
 * <a href="../classes/client.html#setoperatoracdstatus">VoxImplant.Client#setOperatorACDStatus</a> to set the status.
 * <br/>
 * <img src="../assets/images/acdflow.svg" style="width: 500px;display: block;margin: 10px auto 0 auto;"/>
 */
var OperatorACDStatuses;
(function (OperatorACDStatuses) {
  /**
   * Operator is offline
   * <br/>
   * <br/>
   * <strong>Recommended logic flow</strong>
   * <table>
   * <thead>
   * <tr><th>From status      </th><th>This status                 </th><th>To status</th></tr>
   * </thead>
   * <tbody>
   * <tr><td>NONE             </td><td rowspan="6">OFFLINE           </td><td rowspan="6">ONLINE           </td></tr>
   * <tr><td>ONLINE           </td></tr>
   * <tr><td>READY            </td></tr>
   * <tr><td>AFTER_SERVICE    </td></tr>
   * <tr><td>DND              </td></tr>
   * <tr><td>TIMEOUT          </td></tr>
   * </tbody>
   * </table>
   */
  OperatorACDStatuses[OperatorACDStatuses["Offline"] = "OFFLINE"] = "Offline";
  /**
   * The operator is logged in, but not ready to handle incoming calls yet
   * <br/>
   * <br/>
   * <strong>Recommended logic flow</strong>
   * <table>
   * <thead>
   * <tr><th>From status      </th><th>This status                 </th><th>To status</th></tr>
   * </thead>
   * <tbody>
   * <tr><td>OFFLINE          </td><td rowspan="2">ONLINE            </td><td rowspan="2">READY            </td></tr>
   * <tr><td>READY            </td></tr>
   * </tbody>
   * </table>
   *
   * <strong>!!! Set status to ONLINE and then to READY, if you want flush operator ban (after missed call)</strong>
   */
  OperatorACDStatuses[OperatorACDStatuses["Online"] = "ONLINE"] = "Online";
  /**
   * Ready to handle incoming calls
   * <br/>
   * <br/>
   * <strong>Recommended logic flow</strong>
   * <table>
   * <thead>
   * <tr><th>From status      </th><th>This status                 </th><th>To status</th></tr>
   * </thead>
   * <tbody>
   * <tr><td>ONLINE           </td><td rowspan="4">READY            </td><td rowspan="1">IN_SERVICE       </td></tr>
   * <tr><td>DND              </td>                                      <td rowspan="1">ONLINE           </td></tr>
   * <tr><td>AFTER_SERVICE    </td>                                      <td rowspan="1">DND              </td></tr>
   * <tr><td>TIMEOUT          </td>                                      <td rowspan="1">TIMEOUT          </td></tr>
   * </tbody>
   * </table>
   */
  OperatorACDStatuses[OperatorACDStatuses["Ready"] = "READY"] = "Ready";
  /**
   * Incoming call is in service
   * <br/>
   * <br/>
   * <strong>Recommended logic flow</strong>
   * <table>
   * <thead>
   * <tr><th>From status      </th><th>This status                 </th><th>To status</th></tr>
   * </thead>
   * <tbody>
   * <tr><td>READY            </td><td rowspan="1">IN_SERVICE    </td><td rowspan="1">AFTER_SERVICE    </td></tr>
   * </tbody>
   * </table>
   */
  OperatorACDStatuses[OperatorACDStatuses["InService"] = "IN_SERVICE"] = "InService";
  /**
   * Incoming call ended and now processing after service work.
   * <br/>
   * <br/>
   * <strong>Recommended logic flow</strong>
   * <table>
   * <thead>
   * <tr><th>From status      </th><th>This status                 </th><th>To status</th></tr>
   * </thead>
   * <tbody>
   * <tr><td rowspan="4">IN_SERVICE       </td><td rowspan="4">AFTER_SERVICE    </td><td>READY            </td></tr>
   * <tr>                                                                            <td>TIMEOUT          </td></tr>
   * <tr>                                                                            <td>DND              </td></tr>
   * <tr>                                                                            <td>OFFLINE          </td></tr>
   * </tbody>
   * </table>
   */
  OperatorACDStatuses[OperatorACDStatuses["AfterService"] = "AFTER_SERVICE"] = "AfterService";
  /**
   * The operator is on a break, maybe lunch.
   * <br/>
   * <br/>
   * <strong>Recommended logic flow</strong>
   * <table>
   * <thead>
   * <tr><th>From status      </th><th>This status                 </th><th>To status</th></tr>
   * </thead>
   * <tbody>
   * <tr><td>READY            </td><td rowspan="2">TIMEOUT          </td><td rowspan="2">READY            </td></tr>
   * <tr><td>AFTER_SERVICE    </td></tr>
   * </tbody>
   * </table>
   */
  OperatorACDStatuses[OperatorACDStatuses["Timeout"] = "TIMEOUT"] = "Timeout";
  /**
   * The operator is busy now and not ready to handle incoming calls (e.g. working on another call)
   * <br/>
   * <br/>
   * <strong>Recommended logic flow</strong>
   * <table>
   * <thead>
   * <tr><th>From status      </th><th>This status                 </th><th>To status</th></tr>
   * </thead>
   * <tbody>
   * <tr><td>READY            </td><td rowspan="2">DND              </td><td rowspan="2">READY            </td></tr>
   * <tr><td>AFTER_SERVICE    </td></tr>
   * </tbody>
   * </table>
   */
  OperatorACDStatuses[OperatorACDStatuses["DND"] = "DND"] = "DND";
})(OperatorACDStatuses = exports.OperatorACDStatuses || (exports.OperatorACDStatuses = {}));

/***/ })
/******/ ]);
//# sourceMappingURL=voximplant.js.map