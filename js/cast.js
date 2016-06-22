/*global chrome*/
/*jslint browser: true */
var castModule = (function () {
    'use strict';
    var launchBtn, disabledBtn, stopBtn, session;

    function receiverListener(e) {
        return (e === chrome.cast.ReceiverAvailability.AVAILABLE);
    }

    function onMediaDiscovered() {
        if (launchBtn) {
            stopBtn.classList.remove('cast_hidden');
            launchBtn.classList.add('cast_hidden');
        }
    }

    function onStopCast() {
        stopBtn.classList.add('cast_hidden');
        launchBtn.classList.remove('cast_hidden');
    }

    function updateListener() {
        if (session.status !== chrome.cast.SessionStatus.CONNECTED) {
            onStopCast();
        }
    }

    function sessionListener(e) {
        session = e;
        session.addMediaListener(onMediaDiscovered.bind(this, 'addMediaListener'));
        session.addUpdateListener(updateListener.bind(this));
        if (session.media.length !== 0) {
            onMediaDiscovered('onRequestSessionSuccess', session.media[0]);
        }
    }

    function stopCast() {
        session.stop(onStopCast);
    }

    function onMediaError() {
        stopCast();
    }

    function onRequestSessionSuccess(e) {
        session = e;
        var videoLink = document.getElementById('video_link'), videoURL = videoLink.dataset.video, mediaInfo = new chrome.cast.media.MediaInfo(videoURL, 'video/' + videoLink.dataset.ext), request = new chrome.cast.media.LoadRequest(mediaInfo);
        session.loadMedia(request, onMediaDiscovered.bind(this, 'loadMedia'), onMediaError);
    }

    function onLaunchError(e) {
        throw e.description;
    }

    function launchCast() {
        chrome.cast.requestSession(onRequestSessionSuccess, onLaunchError);
    }

    function onInitSuccess() {
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

    function onError(e) {
        throw e.code;
    }

    function initializeCastApi() {
        var sessionRequest = new chrome.cast.SessionRequest(chrome.cast.media.DEFAULT_MEDIA_RECEIVER_APP_ID), apiConfig = new chrome.cast.ApiConfig(sessionRequest, sessionListener, receiverListener, chrome.cast.AutoJoinPolicy.ORIGIN_SCOPED);
        chrome.cast.initialize(apiConfig, onInitSuccess, onError);
    }

    function loadCastApi(loaded, errorInfo) {
        if (loaded) {
            initializeCastApi();
        } else {
            throw errorInfo;
        }
    }

    return {
        init: function () {
            var intro = document.getElementById('download_intro');
            if (intro) {
                intro.insertAdjacentHTML('beforeend', '<img class="cast_icon" id="cast_disabled" src="img/ic_media_route_disabled_holo_light.png" alt="" title="Google Cast is not supported on this browser." /> <img class="cast_btn cast_hidden cast_icon" id="cast_btn_launch" src="img/ic_media_route_off_holo_light.png" title="Cast to ChromeCast" alt="Google Cast™" /> <img src="img/ic_media_route_on_holo_light.png" alt="Casting to ChromeCast…" title="Stop casting" id="cast_btn_stop" class="cast_btn cast_hidden cast_icon" />');
                window.__onGCastApiAvailable = loadCastApi;
            }
        }
    };
}());

window.addEventListener('load', castModule.init, false);
