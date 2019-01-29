(function () {
    if (!Array.prototype.reduce) {
        Object.defineProperty(Array.prototype, 'reduce', {
            value: function (callback /*, initialValue*/) {
                if (this === null) {
                    throw new TypeError(
                        'Array.prototype.reduce called on null or undefined',
                    );
                }
                if (typeof callback !== 'function') {
                    throw new TypeError(callback + ' is not a function');
                }

                // 1. Let O be ? ToObject(this value).
                var o = Object(this);

                // 2. Let len be ? ToLength(? Get(O, "length")).
                var len = o.length >>> 0;

                // Steps 3, 4, 5, 6, 7
                var k = 0;
                var value;

                if (arguments.length == 2) {
                    value = arguments[1];
                } else {
                    while (k < len && !(k in o)) {
                        k++;
                    }

                    // 3. If len is 0 and initialValue is not present, throw
                    // a TypeError exception.
                    if (k >= len) {
                        throw new TypeError(
                            'Reduce of empty array with no initial value',
                        );
                    }
                    value = o[k++];
                }

                // 8. Repeat, while k < len
                while (k < len) {
                    // a. Let Pk be ! ToString(k).
                    // b. Let kPresent be ? HasProperty(O, Pk).
                    // c. If kPresent is true, then
                    //    i. Let kValue be ? Get(O, Pk).
                    //    ii. Let accumulator be ? Call(callbackfn, undefined,
                    //        « accumulator, kValue, k, O »).
                    if (k in o) {
                        value = callback(value, o[k], k, o);
                    }

                    // d. Increase k by 1.
                    k++;
                }

                // 9. Return accumulator.
                return value;
            },
        });
    }

    if (!Object.keys) {
        Object.keys = (function () {
            'use strict';
            var hasOwnProperty = Object.prototype.hasOwnProperty,
                hasDontEnumBug = !{ toString: null }.propertyIsEnumerable(
                    'toString',
                ),
                dontEnums = [
                    'toString',
                    'toLocaleString',
                    'valueOf',
                    'hasOwnProperty',
                    'isPrototypeOf',
                    'propertyIsEnumerable',
                    'constructor',
                ],
                dontEnumsLength = dontEnums.length;

            return function (obj) {
                if (
                    typeof obj !== 'function' &&
                    (typeof obj !== 'object' || obj === null)
                ) {
                    throw new TypeError('Object.keys called on non-object');
                }

                var result = [],
                    prop,
                    i;

                for (prop in obj) {
                    if (hasOwnProperty.call(obj, prop)) {
                        result.push(prop);
                    }
                }

                if (hasDontEnumBug) {
                    for (i = 0; i < dontEnumsLength; i++) {
                        if (hasOwnProperty.call(obj, dontEnums[i])) {
                            result.push(dontEnums[i]);
                        }
                    }
                }
                return result;
            };
        })();
    }

    if (!Array.prototype.find) {
        Object.defineProperty(Array.prototype, 'find', {
            value: function (predicate) {
                // 1. Let O be ? ToObject(this value).
                if (this == null) {
                    throw new TypeError('"this" is null or not defined');
                }

                var o = Object(this);

                // 2. Let len be ? ToLength(? Get(O, "length")).
                var len = o.length >>> 0;

                // 3. If IsCallable(predicate) is false, throw a TypeError exception.
                if (typeof predicate !== 'function') {
                    throw new TypeError('predicate must be a function');
                }

                // 4. If thisArg was supplied, let T be thisArg; else let T be undefined.
                var thisArg = arguments[1];

                // 5. Let k be 0.
                var k = 0;

                // 6. Repeat, while k < len
                while (k < len) {
                    // a. Let Pk be ! ToString(k).
                    // b. Let kValue be ? Get(O, Pk).
                    // c. Let testResult be ToBoolean(? Call(predicate, T, « kValue, k, O »)).
                    // d. If testResult is true, return kValue.
                    var kValue = o[k];
                    if (predicate.call(thisArg, kValue, k, o)) {
                        return kValue;
                    }
                    // e. Increase k by 1.
                    k++;
                }

                // 7. Return undefined.
                return undefined;
            },
            configurable: true,
            writable: true,
        });
    }

    if (!Array.prototype.some) {
        Array.prototype.some = function (fun, thisArg) {
            'use strict';

            if (this == null) {
                throw new TypeError(
                    'Array.prototype.some called on null or undefined');
            }

            if (typeof fun !== 'function') {
                throw new TypeError();
            }

            var t = Object(this);
            var len = t.length >>> 0;

            for (var i = 0; i < len; i++) {
                if (i in t && fun.call(thisArg, t[i], i, t)) {
                    return true;
                }
            }

            return false;
        };
    }
})();
