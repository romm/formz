// http://youmightnotneedjquery.com/#deep_extend
Fz.extend = function(out) {
    out = out || {};

    for (var i = 1; i < arguments.length; i++) {
        var obj = arguments[i];

        if (!obj) {
            continue;
        }

        for (var key in obj) {
            if (obj.hasOwnProperty(key)) {
                //if (typeof obj[key] === 'object')
                //    out[key] = this.extend(out[key], obj[key]);
                //else
                    out[key] = obj[key];
            }
        }
    }

    return out;
};

Fz.extend(
    Fz,
    /** @namespace Formz */
    {
        camelCaseToDashed: function (string) {
            return string.replace(/(?:^|\.?)([A-Z])/g, function (x, y) {
                return "-" + y.toLowerCase()
            }).replace(/^-/, "");
        },

        commaSeparatedValues: function (string) {
            return (typeof string === 'object')
                ? string.join()
                : string;
        },

        objectSize: function (obj) {
            var size = 0;

            for (var key in obj) {
                if (obj.hasOwnProperty(key)) {
                    size++;
                }
            }

            return size;
        },

        objectKeys: function (obj) {
            var keys = [];

            for (var key in obj) {
                if (obj.hasOwnProperty(key)) {
                    keys.push(key);
                }
            }

            return keys;
        },

        addClass: function(el, className) {
            // http://youmightnotneedjquery.com/#add_class
            if (el.classList) {
                el.classList.add(className);
            } else {
                el.className += ' ' + className;
            }
        },

        removeClass: function(el, className) {
            // http://youmightnotneedjquery.com/#remove_class
            if (el.classList) {
                el.classList.remove(className);
            } else {
                el.className = el.className.replace(new RegExp('(^|\\b)' + className.split(' ').join('|') + '(\\b|$)', 'gi'), ' ');
            }
        },

        hasClass: function(el, className) {
            // http://youmightnotneedjquery.com/#has_class
            return (el.classList)
                ? el.classList.contains(className)
                : new RegExp('(^| )' + className + '( |$)', 'gi').test(el.className);
        }
    }
);
