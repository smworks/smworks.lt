// Set starting history point
console.log(`Loaded page. History size: ${window.history.length}`)
window.history.replaceState({}, window.document.title, window.location.href);
console.log(`Loaded page. History size after replace: ${window.history.length}`)
// DOM elements
var $pageWindow = $('#page-window');
var $pageContent = $('#page-content');
var $pageTitle = $('#page-title');
var $contacts = $('#contacts');
var $contactForm = $('#contact-form');
var $socialControls = $('#social');
// Global variables
var onCancelGoToIndex = true;
var isFBReady = false;

$contactForm.on('submit', function (e) {
    e.preventDefault();
    var params = {
        email: $('#email').val(),
        message: $('#message').val()
    };
    $.post('/contacts.php', params)
        .done(function () {
            $('input, textarea, button', $contactForm).prop('disabled', true);
            $('.panel-body', $contacts).html('<div class="alert alert-success">' +
                '<strong>Success! </strong>Message sent.</div>');
        })
        .fail(function () {
            $('.panel-body', $contacts).html('<div class="alert alert-danger">' +
                '<strong>Failure! </strong>Message was not sent.</div>');
        })
});

function addSocialControls(id) {
    if (isFBReady) {
        var addr = 'http://smworks.lt/pages/' + id;
        $socialControls.html(''
            + '<div class="fb-like pull-left" data-href="' + addr + '" data-layout="standard" data-action="like" data-size="small" data-show-faces="true" data-share="true"></div>'
            + '<div class="fb-comments" data-href="' + addr + '" data-width="100%" data-numposts="5"></div>');
        FB.XFBML.parse($socialControls[0]);
        var params = {};
        params[FB.AppEvents.ParameterNames.CONTENT_ID] = id;
        params[FB.AppEvents.ParameterNames.CONTENT_TYPE] = 'article';
        FB.AppEvents.logEvent(FB.AppEvents.EventNames.VIEWED_CONTENT, null, params);
    } else {
        setTimeout(function () {
            addSocialControls(id);
        }, 1000);
    }
}

$('#category').change(function () {
    var value = parseInt(this.value);
    $('.list-item').each(function () {
        var item = $(this);
        var category = item.data('category');
        if (category === value || value === -1) {
            item.show();
        } else {
            item.hide();
        }
    });
});

$('.menu').on('click', function (e) {
    e.preventDefault();
    var id = this.dataset['id'];
    document.getElementById(id).scrollIntoView();
});

// Switcheroo!
window.addEventListener('popstate', function (event) {
    let data = event.state;
    let isVisible = $pageWindow.is(':visible');
    console.log(`Data: ${JSON.stringify(data)}. Is modal visible: ${isVisible} History lenght: ${window.history.length}`)
    if (data != null) {
        if (data.id != null) {
            goToPage(data);
        } else {
            if (isVisible) {
                onCancelGoToIndex = false;
                $pageWindow.modal('hide');
            }
        }
    }
});

$('.page-link').on('click', function (e) {
    console.log(`Article click triggered`);
    e.preventDefault();
    goToPage(this.dataset, true);
});

function goToPage(dataset, pushToHistory = false) {
    var id = dataset['id'];
    var title = dataset['title'];
    var d = new Date(dataset['date'] * 1000);
    var edit = dataset['edit'];
    var editLink = $('#edit-link');
    onCancelGoToIndex = true
    if (pushToHistory) {
        console.log("Pushing history state");
        window.history.pushState({
            id: id,
            title: title,
            date: dataset['date'],
            edit: edit
        }, title, "/pages/" + id);
        console.log(`Data: ${JSON.stringify(dataset)}. History lenght: ${window.history.length}`)
    }
    if (edit === '1') {
        editLink.show();
        editLink.prop('href', '/index.php?editor=true&pageId=' + id);
    }
    else {
        editLink.hide();
    }
    $pageTitle.html(title
        + ' (' + d.getFullYear() + ' ' + ('0' + (d.getMonth() + 1)).slice(-2) + ' ' + ('0' + d.getDate()).slice(-2) + ')');
    document.body.style.overflow = 'hidden';
    $pageWindow.modal('show');
    $.get('/rest/pages/' + id).done(function (data) {
        $pageContent.html(data);
        $pageContent.find('img').each(function () {
            $(this).addClass('img-responsive');
        });
        $pageContent.find('pre').each(function (i, e) {
            $(e).removeClass('language-markup');
        });
        $pageContent.find('pre code').each(function (i, e) {
            hljs.highlightBlock(e);
        });
    }).fail(function () {
        $pageContent.html('Unable to get data from server...');
    }).done(function () {
        addSocialControls(id);
    });
}

$pageWindow.on('hidden.bs.modal', function () {
    document.body.style.overflow = 'visible';
    $pageContent.html('<div class="pre-loader"></div>');
    $socialControls.html('');
    if (onCancelGoToIndex) {
        console.log("Popping history state");
        history.back();
    }
});

$(document).ready(function () {
    var address = window.location.href;
    if (address.indexOf('pages/') !== -1) {
        var parts = address.split('/');
        var id = parts[parts.length - 1];
        if (!isNaN(parseFloat(id)) && isFinite(id)) {
            $('[data-id="' + id + '"]').trigger('click');
        }
    }
});

window.fbAsyncInit = function () {
    FB.init({
        appId: '227461151000174',
        xfbml: false,
        version: 'v2.8'
    });
    FB.XFBML.parse(document.getElementById('fb-page'));
    isFBReady = true;
    FB.AppEvents.logEvent("openedPage");
};

(function (d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) {
        return;
    }
    js = d.createElement(s);
    js.id = id;
    js.src = "//connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));