/*global chrome*/
/*jslint devel: true, browser: true */
var launchBtn, disabledBtn, stopBtn;
var session, currentMedia;

function receiverListener() {
    'use strict';
    console.log('receiverListener');
}

function sessionListener() {
    'use strict';
    console.log('sessionListener');
}

function onMediaDiscovered(how, media) {
    'use strict';
    currentMedia = media;
}

function onMediaError() {
    'use strict';
    console.log('onMediaError');
    stopCast();
}

function onRequestSessionSuccess(e) {
    'use strict';
    session = e;
    var videoLink = document.getElementById('video_link'), videoURL = videoLink.dataset.video, mediaInfo = new chrome.cast.media.MediaInfo(videoURL, 'video/' + videoLink.dataset.ext), request = new chrome.cast.media.LoadRequest(mediaInfo);
    stopBtn.classList.remove('cast_hidden');
    launchBtn.classList.add('cast_hidden');
    session.loadMedia(request, onMediaDiscovered.bind(this, 'loadMedia'), onMediaError);
}

function onLaunchError() {
    'use strict';
    console.log('onLaunchError');
}

function onInitSuccess() {
    'use strict';
    chrome.cast.requestSession(onRequestSessionSuccess, onLaunchError);
}

function onError() {
    'use strict';
    console.log('onError');
}

function onStopCast() {
    'use strict';
    stopBtn.classList.add('cast_hidden');
    launchBtn.classList.remove('cast_hidden');
}

function launchCast() {
    'use strict';
    var sessionRequest = new chrome.cast.SessionRequest(chrome.cast.media.DEFAULT_MEDIA_RECEIVER_APP_ID), apiConfig = new chrome.cast.ApiConfig(sessionRequest, sessionListener, receiverListener, chrome.cast.AutoJoinPolicy.PAGE_SCOPED);
    chrome.cast.initialize(apiConfig, onInitSuccess, onError);
}

function stopCast() {
    'use strict';
    session.stop(onStopCast);
}

function initializeCastApi() {
    'use strict';
    launchBtn = document.getElementById('cast_btn_launch');
    disabledBtn = document.getElementById('cast_disabled');
    stopBtn = document.getElementById('cast_btn_stop');
    if (launchBtn) {
        disabledBtn.classList.add('cast_hidden');
        launchBtn.classList.remove('cast_hidden');
        launchBtn.addEventListener('click', launchCast, false);
        stopBtn.addEventListener('click', stopCast, false);
    }
}

function loadCastApi(loaded, errorInfo) {
    'use strict';
    if (loaded) {
        initializeCastApi();
    } else {
        console.log(errorInfo);
    }
}

window['__onGCastApiAvailable'] = loadCastApi;
