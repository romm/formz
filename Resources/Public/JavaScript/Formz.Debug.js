Fz.Debug = (function () {
    /**
     * The debug utilities will run only if this variable is set to true.
     *
     * @type {boolean}
     */
    var activated = false;

    /** @namespace Formz.Debug */
    return {
        /**
         * Activates debug utilities.
         */
        activate: function () {
            activated = true;
        },

        /**
         * Deactivates debug utilities.
         */
        deactivate: function () {
            activated = false;
        },

        /**
         * Will show a value in the console.
         *
         * @param {*}      value
         * @param {string} type
         */
        debug: function (value, type) {
            if (activated) {
                var color = '';

                switch (type) {
                    case Fz.TYPE_WARNING:
                        color = 'color: orange;';
                        console.warn('%c[FormZ] ' + value + '%c', color, 'color: orange;');
                        break;
                    case Fz.TYPE_ERROR:
                        color = 'color: red;';
                        console.error('%c[FormZ] ' + value + '%c', color, 'color: red;');
                        break;
                    case Fz.TYPE_NOTICE:
                        color = 'color: blue;';
                        console.info('%c[FormZ] ' + value + '%c', color, 'color: blue;');
                        break;
                    default:
                        color = 'color: black;';
                        console.log('%c[FormZ] ' + value + '%c', color, 'color: black;');
                        break;
                }

                var consoleTypo3Debugbar = document.querySelector('.phpdebugbar-widgets-console');
                if (consoleTypo3Debugbar) {
                    consoleTypo3Debugbar.innerHTML = value;
                    consoleTypo3Debugbar.style = color;
                }

            }
        }
    };
})();

if (typeof(PhpDebugBar) === 'undefined') {
    // namespace
    var PhpDebugBar = {};
    PhpDebugBar.$ = jQuery;
}

(function($) {

    var csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-');

    /**
     * Widget for the displaying FormZ informations
     *
     * Options:
     *  - data
     */
    var FormzWidget = PhpDebugBar.Widgets.FormzWidget = PhpDebugBar.Widget.extend({

        className: csscls('formz'),

        render: function() {

            this.$status = $('<div />').addClass(csscls('status')).appendTo(this.$el);
            this.$debug = $('<div />').addClass(csscls('debug')).appendTo(this.$el);
            this.$list = new PhpDebugBar.Widgets.ListWidget(
                {
                    itemRenderer: function(li, form) {
                        if (form.name) {
                            $('<h3><span title="Name" /></h3>').addClass(csscls('name')).text(form.name).appendTo(li);
                        }

                        if (form.className) {
                            $('<span title="ClassName" />').addClass(csscls('className')).text(form.className).appendTo(li);
                        }
                        if (form.params && !$.isEmptyObject(form.params)) {
                            var table = $('<table style="display: none;"><tr><th colspan="2">Configuration</th></tr></table>').addClass(csscls('params')).appendTo(li);
                            for (var key in form.params) {
                                if (typeof form.params[key] !== 'function') {
                                    table.append('<tr><td class="' + csscls('name') + '">' + key + '</td><td class="' + csscls('value') +
                                        '">' + form.params[key] + '</td></tr>');
                                }
                            }
                            li.css('cursor', 'pointer').click(function() {
                                if (table.is(':visible')) {
                                    table.hide();
                                } else {
                                    table.show();
                                }
                            });
                        }
                    }
                }
            );
            this.$list.$el.appendTo(this.$el);
            this.$console = $('<div />').addClass(csscls('console')).appendTo(this.$el);

            this.bindAttr('data', function(data) {

                if (data.length <= 0) {
                    return false;
                }

                this.$list.set('data', data.forms);
                this.$status.empty();
                this.$console.empty();

                this.$status.append(data.status);
                this.$debug.append(data.debug);
                this.$console.append(data.console);
                this.$el.append(data);
            });
        }
    });
})(PhpDebugBar.$);

