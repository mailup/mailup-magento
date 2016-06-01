/**
 * MailUp admin javascript
 *
 * Javascript to be run on admin pages
 */

/**
 * Setup Ajax in system config for loading groups
 */
function initListObserver(url) {
    $('mailup_newsletter_mailup_list').observe('change', function (event) {
        var currentGroupSelected = $('mailup_newsletter_mailup_default_group').value;
        var updater = new Ajax.Updater('mailup_newsletter_mailup_default_group', url, {
            method: 'get',
            onSuccess: function () {
                $('mailup_newsletter_mailup_default_group').value = currentGroupSelected;
            },
            parameters: {list: $('mailup_newsletter_mailup_list').value}
        });
    }); // End of mailup list change
}

function initSelfTestObserver(url) {
    $('mailup_selftest_button').observe('click', function (event) {
        var request = new Ajax.Request(url, {
            method: 'get',
            onFailure: function(transport) {$('messages').update('<ul class="messages"><li class="error-msg"><ul><li>Error checking connection details</li></ul></li></ul>')},
            onComplete: function(transport) {
                $('messages').update(transport.responseText);
                Element.hide('loading-mask');
            },
            parameters: {
                url_console: $('mailup_newsletter_mailup_url_console').value,
                username_ws: $('mailup_newsletter_mailup_username_ws').value,
                password_ws: $('mailup_newsletter_mailup_password_ws').value
            }
        });
    }); // End of mailup selftest button click change
}
