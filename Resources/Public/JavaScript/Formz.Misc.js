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
        },

        buildQueryForm: function (form, wrapper) {
            var query = '';
            var elementsDone = {};
            for (var i = 0; i < form.elements.length; i++) {
                var key = form.elements[i].name;
                if ('' !== key
                    && 'undefined' !== typeof key
                    && false == key in elementsDone
                ) {
                    if (null === key.match(/\[__referrer|__trustedProperties\]/)) {
                        var value = this.getElementValue(form.elements[i]);
                        if (value) {
                            if ('' !== query) {
                                query += '&';
                            }
                            if (wrapper) {
                                key = key.replace(/\w+/, function () {
                                    return '[' + arguments[0] + ']';
                                });
                                query += wrapper.toString() + encodeURIComponent(key) + '=' + encodeURIComponent(value);
                            } else {
                                query += encodeURIComponent(key) + '=' + encodeURIComponent(value);
                            }

                            elementsDone[key] = true;
                        }
                    }
                }
            }

            return query;
        },

        getElementValue: function (formElement) {
            var type = null;
            if (formElement.length != null) {
                type = formElement[0].type;
            }
            if (typeof(type) == 'undefined' || type == 0 || null == type) {
                type = formElement.type;
            }

            var x = 0;

            switch (type) {
                case 'undefined':
                    return;

                case 'radio':
                    var checkedOne = document.querySelector('[name="' + formElement.name + '"]:checked');
                    if (null !== checkedOne) {
                        return checkedOne.value;
                    }

                    return;

                case 'select-multiple':
                    var myArray = [];
                    for (x = 0; x < formElement.length; x++) {
                        if (formElement[x].selected == true) {
                            myArray[myArray.length] = formElement[x].value;
                        }
                    }

                    return myArray;

                case 'checkbox':
                    if (formElement.checked) {
                        return formElement.value;
                    } else {
                        return formElement.checked;
                    }

                default:
                    return formElement.value;
            }
        }
    }
);
