YUI.add('moodle-availability_password-popup', function (Y, NAME) {

/**
 * Availability password - YUI code for password popup
 *
 * @package    availability_password
 * @copyright  2016 Davo Smith, Synergy Learning UK on behalf of Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* global M */
var SELECTORS = {
    MAINREGION: '#region-main',
    PASSWORDLINK: '.availability_password-popup',
    PASSWORDFIELD: '#availability_password',
    ERRORMESSAGE: '#availability_password_error',
    CMCONTAINER: '.activity',
    CMNAME: '.instancename'
};

M.availability_password = M.availability_password || {};
M.availability_password.popup = {
    api: M.cfg.wwwroot + '/availability/condition/password/ajax.php',

    init: function() {
        var main;

        main = Y.one(SELECTORS.MAINREGION);
        if (!main) {
            return;
        }
        main.delegate('click', this.showPopup, SELECTORS.PASSWORDLINK, this);
        main.delegate('click', this.checkShowPopup, SELECTORS.CMNAME, this);
        this.initActivityLinks();
    },

    showPopup: function(e) {
        var content, cmname, panel, url, cmid, cmcontainer, cmnameholder, submit;

        e.preventDefault();
        e.stopPropagation();

        url = e.currentTarget.get('href');
        cmid = url.match(/id=(\d+)/);
        if (!cmid) {
            return;
        }
        cmid = parseInt(cmid[1], 10);
        if (!cmid) {
            return;
        }

        cmname = '';
        cmcontainer = e.currentTarget.ancestor(SELECTORS.CMCONTAINER);
        if (cmcontainer) {
            cmnameholder = cmcontainer.one(SELECTORS.CMNAME);
            if (cmnameholder) {
                cmname = cmnameholder.getHTML();
            }
        }

        content = '';
        content += '<span class="availability_password_intro">' +
            M.util.get_string('passwordintro', 'availability_password', cmname) + '</span>';
        content += '<label for="availability_password">' + M.util.get_string('enterpassword', 'availability_password') +
            '</label>';
        content += '<span id="availability_password_error"></span>';
        content += '<input type="password" id="availability_password">';

        panel = new M.core.dialogue({
            bodyContent: content,
            width: '350px',
            modal: true
        }).show();
        panel.after('visibleChange', function() {
            if (!panel.get('visible')) {
                panel.destroy(true);
            }
        });

        submit = function(e) {
            var data, password;
            e.preventDefault();

            password = Y.one(SELECTORS.PASSWORDFIELD).get('value').trim();
            if (password.length === 0) {
                return; // Do nothing if the password is blank.
            }

            // Send the request back to the server.
            data = {
                sesskey: M.cfg.sesskey,
                id: cmid,
                password: password
            };
            Y.io(this.api, {
                data: data,
                on: {
                    // Handle the response from the server.
                    success: function(ignore, resp) {
                        var details;
                        try {
                            details = JSON.parse(resp.responseText);
                        } catch (ex) {
                            window.alert('Communication error');
                            return;
                        }
                        if (details.error) {
                            window.alert(details.error);
                            return;
                        }
                        if (details.success) {
                            if (details.redirect !== undefined) {
                                document.location = details.redirect;
                            } else {
                                document.location.reload();
                            }
                        } else {
                            Y.one(SELECTORS.ERRORMESSAGE).setHTML(M.util.get_string('wrongpassword', 'availability_password'));
                            Y.one(SELECTORS.PASSWORDFIELD).focus();
                        }
                    }
                }
            });
        };

        panel.addButton({
            label: M.util.get_string('submit', 'core'),
            section: Y.WidgetStdMod.FOOTER,
            action: submit,
            context: this
        });
        panel.addButton({
            label: M.util.get_string('cancel', 'core'),
            section: Y.WidgetStdMod.FOOTER,
            action: function(e) {
                e.preventDefault();
                panel.hide();
            }
        });

        Y.one(SELECTORS.PASSWORDFIELD).focus().on('key', submit, 'enter', this);
    },

    /**
     * Check to see if the activity is unavailable, but has an associated password popup.
     * If so, popup the relevant password request, when the activity name is clicked on.
     * @param e
     */
    checkShowPopup: function(e) {
        var activityName, pwLink;

        activityName = e.currentTarget;
        if (activityName.ancestor('a')) {
            return; // The activity name is already linked - go with the default action.
        }

        pwLink = activityName.ancestor(SELECTORS.CMCONTAINER).one(SELECTORS.PASSWORDLINK);
        if (pwLink) {
            // Trigger the relevant password popup.
            e.preventDefault();
            e.stopPropagation();
            this.showPopup({
                currentTarget: pwLink,
                preventDefault: function() { /* Do nothing */
                },
                stopPropagation: function() { /* Do nothing */
                }
            });
        }
    },

    initActivityLinks: function() {
        Y.one(SELECTORS.MAINREGION).all(SELECTORS.CMNAME).each(function(activityName) {
            var pwLink;
            if (activityName.ancestor('a')) {
                return; // Already linked, nothing to do.
            }
            pwLink = activityName.ancestor(SELECTORS.CMCONTAINER).one(SELECTORS.PASSWORDLINK);
            if (pwLink) {
                activityName.setStyle('cursor', 'pointer');
            }
        });
    }
};


}, '@VERSION@', {"requires": ["base", "node", "event", "moodle-core-notification-dialogue", "io-base"]});
